<?php
/**
 * Copyright 2018 AlexaCRM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
 * OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace AlexaCRM\WebAPI\OData;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Represents the Dynamics 365 (online) authentication middleware.
 */
class OnlineAuthMiddleware implements AuthMiddlewareInterface {

    /**
     * OData service settings.
     */
    protected OnlineSettings $settings;

    /**
     * Bearer token.
     */
    protected ?Token $token = null;

    protected ?HttpClient $httpClient = null;

    /**
     * OnlineAuthMiddleware constructor.
     *
     * @param OnlineSettings $settings
     */
    public function __construct( OnlineSettings $settings ) {
        $this->settings = $settings;
    }

    /**
     * Constructs an HTTP client for the middleware.
     */
    protected function getHttpClient(): HttpClient {
        if ( $this->httpClient instanceof HttpClient ) {
            return $this->httpClient;
        }

        $verify = $this->settings->caBundle;
        if ( $verify === null ) {
            $verify = $this->settings->tlsVerifyPeers;
            if ( $verify && $this->settings->caBundlePath !== null ) {
                $verify = $this->settings->caBundlePath;
            }
        }

        $this->httpClient = new HttpClient( [
            'verify' => $verify,
        ] );

        return $this->httpClient;
    }

    /**
     * Provides access to the cache pool to store transient data, e.g. access token, tenant id.
     */
    protected function getPool(): CacheItemPoolInterface {
        return $this->settings->cachePool;
    }

    /**
     * Detects the instance tenant ID by probing the API without authorization.
     *
     * @param string $endpointUri
     *
     * @return string Tenant ID of the queried instance.
     */
    protected function detectTenantID( string $endpointUri ): string {
        if ( isset( $this->settings->tenantID ) ) {
            return $this->settings->tenantID;
        }

        $pool = $this->getPool();
        $cacheKey = 'msdynwebapi.tenant.' . sha1( $endpointUri );
        $cache = $pool->getItem( $cacheKey );
        if ( $cache->isHit() ) {
            return $cache->get();
        }

        $httpClient = $this->getHttpClient();

        try {
            $probeResponse = $httpClient->get( $endpointUri );
        } catch ( HttpClientException $e ) {
            $probeResponse = $e->getResponse();
        }

        preg_match( '~/([a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12})/~', $probeResponse->getHeader( 'WWW-Authenticate' )[0], $tenantMatch );
        $tenantID = $tenantMatch[1];
        $this->settings->logger->debug( "Probed {$endpointUri} for tenant ID {{$tenantID}}" );

        $expirationDuration = new \DateInterval( 'P1Y' ); // Cache the tenant ID for 1 year.
        $pool->save( $cache->set( $tenantID )->expiresAfter( $expirationDuration ) );

        return $tenantID;
    }

    /**
     * Acquires the Bearer token via client credentials OAuth2 flow.
     *
     * @throws AuthenticationException
     */
    protected function acquireToken(): Token {
        if ( $this->token instanceof Token && $this->token->isValid() ) {
            return $this->token; // Token already acquired and is not expired.
        }

        $settings = $this->settings;

        $pool = $this->getPool();
        $cacheKey = 'msdynwebapi.token.' . sha1( $settings->instanceURI . $settings->applicationID . $settings->applicationSecret );
        $cache = $pool->getItem( $cacheKey );
        if ( $cache->isHit() ) {
            $token = $cache->get();
            if ( $token instanceof Token && $token->isValid() ) {
                $this->token = $token;
                $settings->logger->debug( 'Loaded a non-expired access token from cache' );

                return $token;
            }

            $pool->deleteItem( $cacheKey );
        }

        $tenantId = $this->detectTenantID( $settings->getEndpointURI() );
        $tokenEndpoint = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/token';

        $httpClient = $this->getHttpClient();
        try {
            $tokenResponse = $httpClient->post( $tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $settings->applicationID,
                    'client_secret' => $settings->applicationSecret,
                    'resource' => $settings->instanceURI,
                ],
            ] );
            $settings->logger->debug( 'Retrieved a new access token via ' . $tokenEndpoint );
        } catch ( RequestException $e ) {
            $errorDescription = $e->getMessage();
            if ( $e->getResponse() instanceof ResponseInterface ) {
                $response = json_decode( $e->getResponse()->getBody()->getContents() );
                $errorDescription = $response->error_description;
            }

            throw new AuthenticationException( 'Authentication at Azure AD failed. ' . $errorDescription, $e );
        }

        $this->token = Token::createFromJson( $tokenResponse->getBody()->getContents() );
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp( $this->token->expiresOn );
        $pool->save( $cache->set( $this->token )->expiresAt( $expirationDate ) );

        return $this->token;
    }

    /**
     * Discards the access token from memory and cache.
     */
    public function discardToken(): void {
        $this->token = null;

        $settings = $this->settings;

        $cacheKey = 'msdynwebapi.token.' . sha1( $settings->instanceURI . $settings->applicationID . $settings->applicationSecret );
        $this->getPool()->deleteItem( $cacheKey );
    }

    /**
     * Returns a Guzzle-compliant middleware.
     *
     * @return callable
     *
     * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
     */
    public function getMiddleware(): callable {
        $self = $this;

        return static function ( callable $handler ) use ( $self ) {
            return static function ( RequestInterface $request, array $options ) use ( $self, $handler ) {
                $token = $self->acquireToken();
                $headerValue = $token->type . ' ' . $token->token;
                $newReq = $request->withHeader( 'Authorization', $headerValue );

                return $handler( $newReq, $options );
            };
        };
    }
}


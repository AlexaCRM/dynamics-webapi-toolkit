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
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Represents the Dynamics 365 (On Premise) authentication middleware.
 */
class OnPremiseAuthMiddleware implements AuthMiddlewareInterface {

    /**
     * OData service settings.
     */
    protected OnPremiseSettings $settings;

    /**
     * Bearer token.
     */
    protected ?Token $token = null;

    protected ?HttpClient $httpClient = null;

    public function __construct( OnPremiseSettings $settings ) {
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

    private function refreshToken( $cache, $token ): void {
        $settings = $this->settings;
        $refreshToken = $settings->getRefreshTokenURI();

        $requestPayload = [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'client_id' => $settings->applicationID,
                'client_secret' => $settings->applicationSecret,
                'resource' => $settings->resource,
                'refresh_token' => $token->refreshToken,
            ],
        ];

        $this->requestToken( $refreshToken, $cache, $requestPayload );
    }

    private function getAuthToken( $cache ): void {
        $settings = $this->settings;
        $requestPayload = [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $settings->applicationID,
                'client_secret' => $settings->applicationSecret,
                'resource' => $settings->resource,
                'username' => $settings->username,
                'password' => $settings->password,

            ],
        ];
        $tokenEndpoint = $settings->getOnPremiseAuthURI();
        $this->requestToken( $tokenEndpoint, $cache, $requestPayload );
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
        $cacheKey = 'onpremise.token.' . sha1( $settings->getOnPremiseAuthURI() . $settings->applicationID . $settings->applicationSecret );
        $cache = $pool->getItem( $cacheKey );
        if ( $cache->isHit() ) {
            $token = $cache->get();
            if ( $token instanceof Token ) {
                if ( !$token->isValid() ) {
                    $this->refreshToken( $cache, $token );
                }
                $settings->logger->debug( 'Loaded a non-expired access token from cache' );
            } else {
                $pool->deleteItem( $cacheKey );
            }
        } else {
            $this->getAuthToken( $cache );
        }

        return $this->token;
    }

    private function requestToken( $endPoint, $cache, $requestPayload ): void {
        $settings = $this->settings;
        $pool = $this->getPool();

        $httpClient = $this->getHttpClient();

        try {
            $tokenResponse = $httpClient->post( $endPoint, $requestPayload );
            $settings->logger->debug( 'Retrieved a new access token via ' . $endPoint );
        } catch ( RequestException $e ) {
            $errorDescription = $e->getMessage();
            if ( $e->getResponse() instanceof ResponseInterface ) {
                $response = json_decode( $e->getResponse()->getBody()->getContents() );
                $errorDescription = $response->error_description;
            }

            throw new AuthenticationException( 'Authentication at Azure AD failed. ' . $errorDescription, $e );
        }

        $this->token = TokenOnPremise::createFromJson( $tokenResponse->getBody()->getContents() );
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp( $this->token->expiresIn );
        $pool->save( $cache->set( $this->token )->expiresAt( $expirationDate ) );
    }

    /**
     * Discards the access token from memory and cache.
     */
    public function discardToken(): void {
        $this->token = null;

        $settings = $this->settings;

        $cacheKey = 'onpremise.token.' . sha1( $settings->getOnPremiseAuthURI() . $settings->applicationID . $settings->applicationSecret );
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

        return static function( callable $handler ) use ( $self ) {
            return static function( RequestInterface $request, array $options ) use ( $self, $handler ) {
                $token = $self->acquireToken();
                $headerValue = ucfirst( $token->type ) . ' ' . $token->token;
                $newReq = $request->withHeader( 'Authorization', $headerValue );

                return $handler( $newReq, $options );
            };
        };
    }
}


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
        $cacheKey = 'msdynwebapi.token.' . sha1( $settings->instanceURI . $settings->applicationID . $settings->applicationSecret ?? $settings->certificatePath );
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
            $requestPayload = [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $settings->applicationID,
                    'client_secret' => $settings->applicationSecret,
                    'resource' => $settings->instanceURI,
                ],
            ];

	        // Add and remove unnecessary params for certificate-based auth
	        if ( $this->settings->isCertificateBasedAuth() ) {
		        $client_assertion              = $this->computeAssertion( $tokenEndpoint );
		        $requestPayload['form_params'] = array_merge( $requestPayload['form_params'], [
			        'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
			        'client_assertion'      => $client_assertion,
		        ] );
		        unset( $requestPayload['form_params']['client_secret'] );
	        }

	        $tokenResponse = $httpClient->post( $tokenEndpoint, $requestPayload );

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
	 * @param string $tokenEndpoint
	 * Compute an assertion for auth certificate in JWT format
	 * The assertion is generated based on the guide: https://docs.microsoft.com/en-us/azure/active-directory/develop/active-directory-certificate-credentials
	 * @return string
	 * @throws \Exception
	 */
	private function computeAssertion( string $tokenEndpoint ): string {
		if ( $certificate = file_get_contents( $this->settings->certificatePath ) ) {
			openssl_pkcs12_read( $certificate, $certs, $this->settings->passphrase );
			if ( empty( $certs ) ) {
				throw new \Exception( "Can't read certificate. Please check your passphrase" );
			}
			$cert = openssl_x509_read( $certs["cert"] );
			$pkey = openssl_pkey_get_private( $certs["pkey"] );

			$header = json_encode( [
				'alg' => 'RS256',
				'typ' => 'JWT',
				'x5t' => $this->base64UrlEncode( openssl_x509_fingerprint( $cert, 'sha1', true ) ),
			] );

			$payload = json_encode( [
				'iss' => $this->settings->applicationID,
				'sub' => $this->settings->applicationID,
				'exp' => time() + ( 60 * 10 ),
				'jti' => vsprintf( '%s%s-%s-4000-8%.3s-%s%s%s0', str_split( dechex( microtime( true ) * 1000 ) . bin2hex( random_bytes( 8 ) ), 4 ) ),
				'aud' => $tokenEndpoint,
				'nbf' => time() - ( 60 * 10 ),
			] );

			$base64UrlHeader  = $this->base64UrlEncode( $header );
			$base64UrlPayload = $this->base64UrlEncode( $payload );
			openssl_sign( "$base64UrlHeader.$base64UrlPayload", $signature, $pkey, 'SHA256' );
			$base64UrlSignature = $this->base64UrlEncode( $signature );

			return "$base64UrlHeader.$base64UrlPayload.$base64UrlSignature";
		} else {
			throw new \Exception( "No certificate found in '{$this->settings->certificatePath}'" );
		}
	}

	/**
	 * @param $text
	 * Returns base64 url-encode string
	 * @return string
	 */
	function base64UrlEncode( $text ) {
		return rtrim( strtr( base64_encode( $text ), '+/', '-_' ), '=' );
	}

    /**
     * Discards the access token from memory and cache.
     */
    public function discardToken(): void {
        $this->token = null;

        $settings = $this->settings;

        $cacheKey = 'msdynwebapi.token.' . sha1( $settings->instanceURI . $settings->applicationID . $settings->applicationSecret ?? $settings->certificatePath );
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

    /**
     * Always returns true, because there is no token
     *
     * @return bool
     */
    public function refreshToken(): bool {
        return true;
    }
}


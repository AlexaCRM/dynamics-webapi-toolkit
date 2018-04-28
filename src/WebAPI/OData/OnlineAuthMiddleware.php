<?php

namespace AlexaCRM\WebAPI\OData;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;

/**
 * Represents the Dynamics 365 (online) authentication middleware.
 */
class OnlineAuthMiddleware implements AuthMiddlewareInterface {

    /**
     * OData service settings.
     *
     * @var OnlineSettings
     */
    protected $settings;

    /**
     * Bearer token.
     *
     * @var Token
     */
    protected $token;

    /**
     * OnlineAuthMiddleware constructor.
     *
     * @param OnlineSettings $settings
     */
    public function __construct( OnlineSettings $settings ) {
        $this->settings = $settings;
    }

    /**
     * Detects the instance tenant ID by probing the API without authorization.
     *
     * @param string $endpointUri
     *
     * @return string Tenant ID of the queried instance.
     */
    protected function detectTenantID( $endpointUri ) {
        $cacheKey = 'msdynwebapi.tenant.' . sha1( $endpointUri );
        $cache = $this->settings->cachePool->getItem( $cacheKey );
        if ( $cache->isHit() ) {
            return $cache->get();
        }

        $httpClient = new HttpClient( [ 'verify' => false ] ); // TODO: consume custom CA from settings

        try {
            $probeResponse = $httpClient->get( $endpointUri );
        } catch ( HttpClientException $e ) {
            $probeResponse = $e->getResponse();
        }

        preg_match( '~/([a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12})/~', $probeResponse->getHeader( 'WWW-Authenticate' )[0], $tenantMatch );

        $tenantID = $tenantMatch[1];
        $this->settings->cachePool->save( $cache->set( $tenantID ) );

        return $tenantID;
    }

    /**
     * Acquires the Bearer token via client credentials OAuth2 flow.
     *
     * @throws AuthenticationException
     */
    protected function acquireToken() {
        if ( $this->token instanceof Token && $this->token->isValid() ) {
            return $this->token; // Token already acquired and is not expired.
        }

        $settings = $this->settings;

        $cacheKey = 'msdynwebapi.token.' . sha1( $settings->instanceURI . $settings->applicationID . $settings->applicationSecret );
        $cache = $settings->cachePool->getItem( $cacheKey );
        if ( $cache->isHit() ) {
            $token = $cache->get();
            if ( $token instanceof Token && $token->isValid() ) {
                $this->token = $token;

                return $token;
            } else {
                $settings->cachePool->deleteItem( $cacheKey );
            }
        }

        $tenantId = $this->detectTenantID( $settings->endpointURI );
        $tokenEndpoint = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/token';

        $httpClient = new HttpClient( [ 'verify' => false ] ); // TODO: consume custom CA from settings
        try {
            $tokenResponse = $httpClient->post( $tokenEndpoint, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $settings->applicationID,
                    'client_secret' => $settings->applicationSecret,
                    'resource' => $settings->instanceURI,
                ],
            ] );
        } catch ( RequestException $e ) {
            $response = json_decode( $e->getResponse()->getBody()->getContents() );
            $errorDescription = $response->error_description;
            throw new AuthenticationException( 'Authentication at Azure AD failed. ' . $errorDescription, $e );
        }

        $this->token = Token::createFromJson( $tokenResponse->getBody()->getContents() );
        $expirationDate = new \DateTime();
        $expirationDate->setTimestamp( $this->token->expires_on );
        $settings->cachePool->save( $cache->set( $this->token )->expiresAt( $expirationDate ) );

        return $this->token;
    }

    /**
     * Returns a Guzzle-compliant middleware.
     *
     * @return callable
     *
     * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
     */
    public function getMiddleware() {
        $self = $this;

        return function ( callable $handler ) use ( $self ) {
            $settings = $self->settings;

            return function ( RequestInterface $request, array $options ) use ( $self, $handler, $settings ) {
                $token = $self->acquireToken();
                $headerValue = $token->token_type . ' ' . $token->access_token;
                $request = $request->withHeader( 'Authorization', $headerValue );

                return $handler( $request, $options );
            };
        };
    }
}


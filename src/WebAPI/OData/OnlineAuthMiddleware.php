<?php

namespace AlexaCRM\WebAPI\OData;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;
use Psr\Http\Message\RequestInterface;

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

    public function __construct( OnlineSettings $settings ) {
        $this->settings = $settings;
    }

    /**
     * Detects instance tenant ID by probing the API without authorization.
     *
     * @param string $endpointUri
     *
     * @return string
     */
    protected function detectTenantId( $endpointUri ) {
        $httpClient = new HttpClient( [ 'verify' => false ] );

        try {
            $probeResponse = $httpClient->get( $endpointUri );
        } catch ( HttpClientException $e ) {
            $probeResponse = $e->getResponse();
        }

        preg_match( '~/([a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12})/~', $probeResponse->getHeader( 'WWW-Authenticate' )[0], $tenantMatch );

        return $tenantMatch[1];
    }

    protected function acquireToken() {
        $settings = $this->settings;

        $tenantId = $this->detectTenantId( $settings->endpointURI );
        $tokenEndpoint = 'https://login.microsoftonline.com/' . $tenantId . '/oauth2/token';

        $httpClient = new HttpClient( [ 'verify' => false ] );
        $tokenResponse = $httpClient->post( $tokenEndpoint, [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $settings->applicationID,
                'client_secret' => $settings->applicationSecret,
                'resource' => $settings->instanceURI,
            ],
        ] );

        $token = Token::createFromJson( $tokenResponse->getBody()->getContents() );

        $this->token = $token;
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

            $self->acquireToken();

            return function ( RequestInterface $request, array $options ) use ( $self, $handler, $settings ) {
                $headerValue = $self->token->token_type . ' ' . $self->token->access_token;
                $request = $request->withHeader( 'Authorization', $headerValue );

                return $handler( $request, $options );
            };
        };
    }
}


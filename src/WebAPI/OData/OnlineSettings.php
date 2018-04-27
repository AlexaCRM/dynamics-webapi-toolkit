<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * Contains Dynamics 365 (online) credentials.
 */
class OnlineSettings extends Settings {

    /**
     * URI of the Web API endpoint.
     *
     * @var string
     */
    public $endpointURI;

    /**
     * Azure AD application ID.
     *
     * @var string
     */
    public $applicationID;

    /**
     * Azure AD application secret.
     *
     * @var string
     */
    public $applicationSecret;

    /**
     * Returns Web API endpoint URI.
     *
     * @return string
     */
    public function getEndpointURI() {
        if ( $this->endpointURI !== null ) {
            return $this->endpointURI;
        }

        $this->endpointURI = trim( $this->instanceURI, '/' ) . '/api/data/v' . Client::API_VERSION . '/';

        return $this->endpointURI;
    }
}

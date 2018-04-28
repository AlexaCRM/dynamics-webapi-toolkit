<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * Contains Dynamics 365 (online) credentials.
 */
class OnlineSettings extends Settings {

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
        return trim( $this->instanceURI, '/' ) . '/api/data/v' . $this->apiVersion . '/';
    }
}

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
     * Azure AD tenant ID.
     *
     * Optional, allows skipping tenant detection.
     *
     * @var string
     */
    public $tenantID;

    /**
     * Returns Web API endpoint URI.
     *
     * @return string
     */
    public function getEndpointURI() {
        return trim( $this->instanceURI, '/' ) . '/api/data/v' . $this->apiVersion . '/';
    }
}

<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\OData\OnlineAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnlineSettings;

/**
 * Provides several methods to create Client instances for different deployment scenarios.
 */
class ClientFactory {

    /**
     * Creates a client proxy implementing the IOrganizationService interface given the Organization URI
     * and credentials for the Azure AD application configured for access to Dynamics 365.
     *
     * @param string $instanceURI Organization URI, e.g. https://contoso.crm.dynamics.com/.
     * @param string $applicationID GUID of the Azure AD application.
     * @param string $applicationSecret Secret key of the Azure AD application.
     *
     * @return Client
     */
    public static function createOnlineClient( $instanceURI, $applicationID, $applicationSecret ) {
        $settings = new OnlineSettings();
        $settings->instanceURI = $instanceURI;
        $settings->applicationID = $applicationID;
        $settings->applicationSecret = $applicationSecret;

        $middleware = new OnlineAuthMiddleware( $settings );
        $odataClient = new OData\Client( $settings, $middleware );
        $client = new Client( $odataClient );

        return $client;
    }

}

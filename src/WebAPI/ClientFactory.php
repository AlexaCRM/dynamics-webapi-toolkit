<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\OData\OnlineAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnlineSettings;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

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
    public static function createOnlineClient( $instanceURI, $applicationID, $applicationSecret, array $services = [] ) {
        $settings = new OnlineSettings();
        $settings->instanceURI = $instanceURI;
        $settings->applicationID = $applicationID;
        $settings->applicationSecret = $applicationSecret;

        if ( isset ( $services['logger'] ) && $services['logger'] instanceof LoggerInterface ) {
            $settings->setLogger( $services['logger'] );
        }
        if ( isset ( $services['cachePool'] ) && $services['cachePool'] instanceof CacheItemPoolInterface ) {
            $settings->cachePool = $services['cachePool'];
        }

        $middleware = new OnlineAuthMiddleware( $settings );
        $odataClient = new OData\Client( $settings, $middleware );
        $client = new Client( $odataClient );

        return $client;
    }

}

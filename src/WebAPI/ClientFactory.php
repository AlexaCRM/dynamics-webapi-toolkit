<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\OData\OnlineAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnlineSettings;

class ClientFactory {

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

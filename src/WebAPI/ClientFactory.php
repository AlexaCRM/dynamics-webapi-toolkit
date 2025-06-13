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

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\OData\OnlineAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnlineSettings;
use AlexaCRM\WebAPI\OData\OnPremiseAuthMiddleware;
use AlexaCRM\WebAPI\OData\OnPremiseSettings;
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
     * @param array $services Optional services like `logger` or `cachePool`.
     *
     * @return Client
     */
    public static function createOnlineClient(
        string $instanceURI,
        string $applicationID,
        string $applicationSecret,
        array $services = [],
    ): Client {
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

        return new Client( $odataClient );
    }

    /**
     * Creates a client proxy implementing the IOrganizationService interface given the Organization URI
     * and credentials for the Azure AD application configured for access to Dynamics 365.
     *
     * @param OnPremiseSettings $settings
     * @param array $services Optional services like `logger` or `cachePool`.
     * @param bool $ignoreAuthCache
     *
     * @return Client
     */
    public static function createOnPremiseClient(
        OnPremiseSettings $settings,
        array $services = [],
        ?bool $ignoreAuthCache = false,
    ): Client {

        if ( isset ( $services['logger'] ) && $services['logger'] instanceof LoggerInterface ) {
            $settings->setLogger( $services['logger'] );
        }
        if ( isset ( $services['cachePool'] ) && $services['cachePool'] instanceof CacheItemPoolInterface ) {
            $settings->cachePool = $services['cachePool'];
        }

        $middleware = new OnPremiseAuthMiddleware( $settings, $ignoreAuthCache );
        $odataClient = new OData\Client( $settings, $middleware );

        return new Client( $odataClient );
    }

}

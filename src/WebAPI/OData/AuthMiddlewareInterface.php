<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * Describes an interface to provide authentication middleware.
 */
interface AuthMiddlewareInterface {

    /**
     * Returns a Guzzle-compliant middleware.
     *
     * The middleware must augment the request by adding required authorization credentials.
     *
     * @return callable
     *
     * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
     */
    public function getMiddleware();

    /**
     * Discards the currently used access token.
     *
     * @return void
     */
    public function discardToken();

}

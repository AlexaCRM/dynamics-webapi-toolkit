<?php

namespace AlexaCRM\WebAPI\OData;

interface AuthMiddlewareInterface {

    /**
     * Returns a Guzzle-compliant middleware.
     *
     * @return callable
     *
     * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html
     */
    public function getMiddleware();

}

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

namespace AlexaCRM\WebAPI\OData;

use AlexaCRM\Cache\NullAdapter;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Contains Dynamics 365 credentials.
 */
abstract class Settings implements LoggerAwareInterface {

    /**
     * Web API version.
     */
    public $apiVersion = '8.2';

    /**
     * Dynamics 365 organization address.
     *
     * @var string
     */
    public $instanceURI;

    /**
     * Path to a custom CA bundle.
     *
     * @var string
     */
    public $caBundle;

    /**
     * @var CacheItemPoolInterface
     */
    public $cachePool;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * ID of the user to impersonate during calls.
     *
     * Null value means no user is impersonated.
     *
     * @var string
     */
    public $callerID;

    /**
     * Settings constructor.
     */
    public function __construct() {
        $this->cachePool = new NullAdapter();
        $this->logger = new NullLogger();
    }

    /**
     * Sets a logger instance on the object.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger( LoggerInterface $logger ) {
        $this->logger = $logger;
    }

    /**
     * Returns Web API endpoint URI.
     *
     * @return string
     */
    public abstract function getEndpointURI();

}

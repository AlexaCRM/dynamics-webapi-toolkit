<?php

namespace AlexaCRM\WebAPI\OData;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WildWolf\Psr6MemoryCache;

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
     * Settings constructor.
     */
    public function __construct() {
        $this->cachePool = Psr6MemoryCache::instance();
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

<?php

namespace AlexaCRM\WebAPI\OData;

use Psr\Cache\CacheItemPoolInterface;
use WildWolf\Psr6MemoryCache;

/**
 * Contains Dynamics 365 credentials.
 */
abstract class Settings {

    /**
     * Dynamics 365 organization address.
     *
     * @var string
     */
    public $instanceURI;

    /**
     * @var CacheItemPoolInterface
     */
    public $cachePool;

    /**
     * Settings constructor.
     */
    public function __construct() {
        $this->cachePool = Psr6MemoryCache::instance();
    }

    /**
     * Returns Web API endpoint URI.
     *
     * @return string
     */
    public abstract function getEndpointURI();

}

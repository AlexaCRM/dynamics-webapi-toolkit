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
 */

namespace AlexaCRM\WebAPI;

use AlexaCRM\Cache\NullAdapter;
use AlexaCRM\WebAPI\Client as WebAPIClient;
use AlexaCRM\WebAPI\OData\Annotation;
use AlexaCRM\WebAPI\OData\Client;
use AlexaCRM\WebAPI\OData\ODataException;
use AlexaCRM\Xrm\Metadata\EntityMetadata;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides access to Dynamics 365 organization metadata.
 */
class MetadataRegistry {

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CacheItemPoolInterface
     */
    protected $storage;

    /**
     * How long to store metadata until it is discarded.
     *
     * @var \DateInterval
     */
    public $ttl;

    /**
     * MetadataRegistry constructor.
     *
     * @param \AlexaCRM\WebAPI\Client $client
     */
    public function __construct( WebAPIClient $client ) {
        $this->client = $client->getClient();

        $this->storage = new NullAdapter();
        $this->storage->clear();

        $this->ttl = new \DateInterval( 'P7D' );
    }

    /**
     * Returns a new instance of the registry with the given storage.
     *
     * @param CacheItemPoolInterface $storage A PSR-6 compliant storage.
     *
     * @return MetadataRegistry
     */
    public function withStorage( CacheItemPoolInterface $storage ) {
        $new = clone $this;
        $new->storage = $storage;

        return $new;
    }

    /**
     * Returns an entity metadata definition.
     *
     * @param string $logicalName
     *
     * @return EntityMetadata
     * @throws ODataException
     * @throws OData\AuthenticationException
     */
    public function getDefinition( $logicalName ) {
        $cached = $this->storage->getItem( $logicalName );
        if ( $cached->isHit() ) {
            return $cached->get();
        }

        try {

            $object = $this->client->getRecord( 'EntityDefinitions', "LogicalName='{$logicalName}'", [
                'Expand' => 'Attributes,Keys,OneToManyRelationships,ManyToOneRelationships,ManyToManyRelationships'
            ] );
            unset( $object->{Annotation::ODATA_CONTEXT} );
        } catch ( ODataException $e ) {
            if ( $e->getCode() === 404 ) {
                return null;
            }

            throw $e;
        }

        $serializer = new MetadataSerializer();
        $md = $serializer->createEntityMetadata( $object );

        $cached->set( $md )->expiresAfter( $this->ttl );
        $this->storage->save( $cached );

        return $md;
    }

}

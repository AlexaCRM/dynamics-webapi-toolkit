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
use AlexaCRM\StrongSerializer\Reference;
use AlexaCRM\WebAPI\Client as WebAPIClient;
use AlexaCRM\WebAPI\OData\Annotation;
use AlexaCRM\WebAPI\OData\AuthenticationException;
use AlexaCRM\WebAPI\OData\Client;
use AlexaCRM\WebAPI\OData\ODataException;
use AlexaCRM\WebAPI\OData\TransportException;
use AlexaCRM\Xrm\Metadata\EntityMetadata;
use DateInterval;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Provides access to Dynamics 365 organization metadata.
 */
class MetadataRegistry {

    /**
     * Contains type names of attribute metadata which need OData expansion to retrieve option sets.
     */
    const OPTIONSET_ATTRIBUTES = [
        'BooleanAttributeMetadata',
        'EntityNameAttributeMetadata',
        'MultiSelectPicklistAttributeMetadata',
        'PicklistAttributeMetadata',
        'StateAttributeMetadata',
        'StatusAttributeMetadata',
    ];

    /**
     * How long to store metadata until it is discarded.
     *
     * @var DateInterval
     */
    public DateInterval $ttl;

    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var CacheItemPoolInterface
     */
    protected CacheItemPoolInterface $storage;

    /**
     * Deserializer class conversion map.
     *
     * @var array|null
     */
    protected static ?array $map = null;

    /**
     * MetadataRegistry constructor.
     *
     * @param WebAPIClient $client
     */
    public function __construct( WebAPIClient $client ) {
        $this->client = $client->getClient();

        $this->storage = new NullAdapter();
        $this->storage->clear();

        $this->ttl = new DateInterval( 'P7D' );
    }

    /**
     * Returns a new instance of the registry with the given storage.
     *
     * @param CacheItemPoolInterface $storage A PSR-6 compliant storage.
     *
     * @return MetadataRegistry
     */
    public function withStorage( CacheItemPoolInterface $storage ): MetadataRegistry {
        $new = clone $this;
        $new->storage = $storage;

        return $new;
    }

    /**
     * Returns an entity metadata definition.
     *
     * @param string $logicalName
     *
     * @return EntityMetadata|null
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    public function getDefinition( string $logicalName ): ?EntityMetadata {
        $cached = $this->storage->getItem( $logicalName );
        if ( $cached->isHit() ) {
            return $cached->get();
        }

        try {
            $object = $this->client->getRecord( 'EntityDefinitions', "LogicalName='{$logicalName}'", [
                'Expand' => 'Attributes,Keys,OneToManyRelationships,ManyToOneRelationships,ManyToManyRelationships',
                'ApiVersion' => $this->client->getSettings()->apiVersion,
            ] );
            unset( $object->{Annotation::ODATA_CONTEXT} );

            /*
             * Attributes with option sets arrived without them because OptionSet property is an OData navigation property
             * which needs expansion and explicit type casting.
             *
             * Although we duplicate the attributes, Deserializer will eliminate the duplicates
             * and overwrite them with the newly retrieved attributes.
             */
            $object->Attributes = array_merge( $object->Attributes, $this->retrieveOptionSetAttributes( $logicalName ) );
        } catch ( ODataException $e ) {
            if ( $e->getCode() === 404 ) {
                return null;
            }

            throw new OrganizationException( "Failed to retrieve `{$logicalName}` metadata", $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        }

        $deserializer = $this->newDeserializer();
        /** @var EntityMetadata $md */
        $md = $deserializer->deserialize( $object, new Reference( EntityMetadata::class ) );

        $cached->set( $md )->expiresAfter( $this->ttl );
        $this->storage->save( $cached );

        return $md;
    }

    /**
     * Provides a new instance of metadata deserializer.
     *
     * @return MetadataDeserializer
     */
    public function newDeserializer(): MetadataDeserializer {
        if ( static::$map === null ) {
            static::$map = require 'metadataClassMap.php';
        }

        return new MetadataDeserializer( static::$map );
    }

    /**
     * Retrieves attributes with option sets for the given entity.
     *
     * OData needs type casting AND navigation property expansion to return relevant data. We cycle through each
     * type that needs navigation property expansion and later merge these attributes together.
     *
     * @param string $logicalName
     *
     * @return array
     * @throws AuthenticationException
     * @throws TransportException
     */
    protected function retrieveOptionSetAttributes( string $logicalName ): array {
        $typedAttributes = [];

        foreach ( static::OPTIONSET_ATTRIBUTES as $type ) {
            try {
                $attributesResponse = $this->client->getList( "EntityDefinitions(LogicalName='{$logicalName}')/Attributes/Microsoft.Dynamics.CRM.{$type}", [
                    'Expand' => 'OptionSet,GlobalOptionSet',
                ] );

                foreach ( $attributesResponse->List as $attribute ) {
                    unset( $attribute->{'OptionSet@odata.context'} );

                    if ( !isset( $attribute->OptionSet ) && isset( $attribute->GlobalOptionSet ) ) {
                        $attribute->OptionSet = $attribute->GlobalOptionSet;
                    }

                    unset( $attribute->GlobalOptionSet );

                    if ( !isset($attribute->{'@odata.type'})){
                        $attribute->{'@odata.type'} = "#Microsoft.Dynamics.CRM.{$type}";
                    }

                    $typedAttributes[] = $attribute;
                }
            } catch ( ODataException $e ) {
                $this->client->getLogger()->error( 'Web API responded with an error to the attribute expansion request', [
                    'entity' => $logicalName,
                    'type' => 'Microsoft.Dynamics.CRM.' . $type,
                    'exception' => $e,
                ] );

                continue;
            }
        }

        return $typedAttributes;
    }

}

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

use AlexaCRM\WebAPI\OData\AuthenticationException;
use AlexaCRM\WebAPI\OData\InaccessibleMetadataException;
use AlexaCRM\WebAPI\OData\ODataException;
use AlexaCRM\WebAPI\OData\EntityNotSupportedException;
use AlexaCRM\Xrm\ColumnSet;
use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityCollection;
use AlexaCRM\Xrm\EntityReference;
use AlexaCRM\Xrm\IOrganizationService;
use AlexaCRM\Xrm\Query\FetchExpression;
use AlexaCRM\Xrm\Query\PagingInfo;
use AlexaCRM\Xrm\Query\QueryBase;
use AlexaCRM\Xrm\Query\QueryByAttribute;
use AlexaCRM\Xrm\Relationship;
use AlexaCRM\WebAPI\OData\Client as ODataClient;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Represents the Organization-compatible Dynamics 365 Web API client.
 */
class Client implements IOrganizationService {

    /**
     * @var ODataClient
     */
    protected $client;

    /**
     * Client constructor.
     *
     * @param ODataClient $client
     */
    public function __construct( ODataClient $client ) {
        $this->client = $client;
    }

    /**
     * Creates a link between records.
     *
     * @param string $entityName
     * @param string $entityId
     * @param Relationship $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     * @throws Exception
     * @throws AuthenticationException
     * @throws InaccessibleMetadataException
     * @throws EntityNotSupportedException
     */
    public function Associate( string $entityName, $entityId, Relationship $relationship, array $relatedEntities ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->getEntitySetName( $entityName );

        try {
            foreach ( $relatedEntities as $ref ) {
                $associatedCollectionName = $metadata->getEntitySetName( $ref->LogicalName );

                // TODO: execute in one request with a batch request
                $this->client->associate( $collectionName, $entityId, $relationship->SchemaName, $associatedCollectionName, $ref->Id );
            }
        } catch ( ODataException $e ) {
            throw new Exception( 'Associate request failed: ' . $e->getMessage(), $e );
        }
    }

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return string ID of the new record.
     * @throws AuthenticationException
     * @throws Exception
     * @throws InaccessibleMetadataException
     * @throws EntityNotSupportedException
     */
    public function Create( Entity $entity ) {
        $serializer = new SerializationHelper( $this->client );
        $translatedData = $serializer->serializeEntity( $entity );

        $collectionName = $this->client->getMetadata()->getEntitySetName( $entity->LogicalName );

        try {
            $responseId = $this->client->create( $collectionName, $translatedData );
        } catch ( ODataException $e ) {
            throw new Exception( 'Create request failed: ' . $e->getMessage(), $e );
        }

        $entity->getAttributeState()->reset();

        return $responseId;
    }

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     *
     * @return void
     * @throws InaccessibleMetadataException
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws Exception
     */
    public function Delete( string $entityName, $entityId ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->getEntitySetName( $entityName );

        try {
            $this->client->delete( $collectionName, $entityId );
        } catch ( ODataException $e ) {
            throw new Exception( 'Delete request failed: '. $e->getMessage(), $e );
        }
    }

    /**
     * Deletes a link between records.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     * @param Relationship $relationship
     * @param EntityReference[] $relatedEntities
     *
     * @return void
     * @throws InaccessibleMetadataException
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws Exception
     */
    public function Disassociate( string $entityName, $entityId, Relationship $relationship, array $relatedEntities ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->getEntitySetName( $entityName );

        try {
            foreach ( $relatedEntities as $ref ) {
                $associatedCollectionName = $metadata->getEntitySetName( $ref->LogicalName );

                // TODO: execute in one request with a batch request
                $this->client->disassociate( $collectionName, $entityId, $relationship->SchemaName, $associatedCollectionName, $ref->Id );
            }
        } catch ( ODataException $e ) {
            throw new Exception( 'Disassociate request failed: ' . $e->getMessage(), $e );
        }
    }

    /**
     * Executes a function or action formed as a request. Not implemented.
     *
     * Use \AlexaCRM\WebAPI\OData\Client::ExecuteFunction() and \AlexaCRM\WebAPI\OData\Client::ExecuteAction() instead.
     * Access to \AlexaCRM\WebAPI\OData\Client is provided via Client::getClient().
     *
     * @param $request
     */
    public function Execute( $request ) {
        throw new \BadMethodCallException( 'Execute request not implemented' );
    }

    /**
     * Retrieves a record,
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     * @param ColumnSet $columnSet
     *
     * @return Entity
     * @throws InaccessibleMetadataException
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws Exception
     */
    public function Retrieve( string $entityName, $entityId, ColumnSet $columnSet ) : Entity {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->getEntitySetName( $entityName );
        $entityMap = $metadata->getEntityMap( $entityName );
        $inboundMap = $entityMap->inboundMap;

        $options = [];
        if ( $columnSet->AllColumns !== true ) {
            $options['Select'] = [];

            // $select must not be empty. Add primary key.
            $options['Select'][] = $entityMap->key;

            $columnMapping = array_flip( $inboundMap );
            foreach ( $columnSet->Columns as $column ) {
                if ( !array_key_exists( $column, $columnMapping ) ) {
                    $this->getLogger()->warning( "No inbound attribute mapping found for {$entityName}[{$column}]" );
                    continue;
                }

                $options['Select'][] = $columnMapping[$column];
            }
        }

        try {
            $response = $this->client->getRecord( $collectionName, $entityId, $options );
        } catch ( ODataException $e ) {
            throw new Exception( 'Retrieve request failed: ' . $e->getMessage(), $e );
        }

        $serializer = new SerializationHelper( $this->client );
        $entity = $serializer->deserializeEntity( $response, new EntityReference( $entityName, $entityId ) );

        return $entity;
    }

    /**
     * Retrieves a collection of records.
     *
     * @param QueryBase $query A query that determines the set of records to retrieve.
     *
     * @return EntityCollection
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws InaccessibleMetadataException
     * @throws Exception
     */
    public function RetrieveMultiple( QueryBase $query ) {
        if ( $query instanceof FetchExpression ) {
            return $this->retrieveViaFetchXML( $query );
        } elseif ( $query instanceof QueryByAttribute ) {
            return $this->retrieveViaQueryByAttribute( $query );
        }

        return new EntityCollection();
    }

    /**
     * Updates an existing record.
     *
     * @param Entity $entity
     *
     * @return void
     * @throws InaccessibleMetadataException
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws Exception
     */
    public function Update( Entity $entity ) {
        $serializer = new SerializationHelper( $this->client );
        $translatedData = $serializer->serializeEntity( $entity );

        $collectionName = $this->client->getMetadata()->getEntitySetName( $entity->LogicalName );

        try {
            $this->client->update( $collectionName, $entity->Id, $translatedData );
        } catch ( ODataException $e ) {
            throw new Exception( 'Update request failed: ' . $e->getMessage(), $e );
        }

        $entity->getAttributeState()->reset();
    }

    /**
     * Returns an instance of ODataClient for direct access to OData service and underlying transport.
     *
     * @return ODataClient
     */
    public function getClient() : ODataClient {
        return $this->client;
    }

    /**
     * @param FetchExpression $query
     *
     * @return EntityCollection
     * @throws InaccessibleMetadataException
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws Exception
     */
    protected function retrieveViaFetchXML( FetchExpression $query ) {
        $fetchDOM = new \DOMDocument( '1.0', 'utf-8' );
        $fetchDOM->loadXML( $query->Query );

        $entityTag = $fetchDOM->getElementsByTagName( 'entity' )->item( 0 );
        if ( !( $entityTag instanceof \DOMElement ) || !$entityTag->hasAttribute( 'name' ) ) {
            throw new Exception( 'Malformed FetchXML query: could not locate the <entity/> element or entity name not specified' );
        }

        $entityName = $entityTag->getAttribute( 'name' );

        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->getEntitySetName( $entityName );
        $entityMap = $metadata->getEntityMap( $entityName );

        try {
            $response = $this->client->getList( $collectionName, [
                'FetchXml' => $query->Query,
            ] );
        } catch ( ODataException $e ) {
            throw new Exception( 'RetrieveMultiple (FetchXML) request failed: ' . $e->getMessage(), $e );
        }

        $collection = new EntityCollection();
        $collection->EntityName = $entityName;
        $collection->MoreRecords = false;

        if ( !$response->Count ) {
            return $collection;
        }

        if ( isset( $response->SkipToken ) ) {
            preg_match( '~pagingcookie="(.*?)"~', $response->SkipToken, $tokenMatch );
            $collection->PagingCookie = urldecode( urldecode( $tokenMatch[1] ) );
            $collection->MoreRecords = true;
        }

        $serializer = new SerializationHelper( $this->client );
        $entityRefTypeMap = $serializer->getFetchXMLAliasedLookupTypes( $query->Query );

        /*
         * Deserialize all fields as usual.
         *
         * If the value looks like GUID and has a FormattedValue annotation but no lookuplogicalname,
         * it's a lookup from a linked entity. Look up its logical name in FetchXML.
         * If any x002e are found, replace the divider with dot (.).
         */
        foreach ( $response->List as $item ) {
            $ref = new EntityReference( $entityName );
            $recordKey = $entityMap->key;
            if ( array_key_exists( $recordKey, $item ) ) {
                $ref->Id = $item->{$recordKey};
            }

            $record = $serializer->deserializeEntity( $item, $ref, $entityRefTypeMap );

            $collection->Entities[] = $record;
        }

        return $collection;
    }

    /**
     * @param QueryByAttribute $query
     *
     * @return EntityCollection
     * @throws AuthenticationException
     * @throws InaccessibleMetadataException
     * @throws EntityNotSupportedException
     * @throws Exception
     */
    protected function retrieveViaQueryByAttribute( QueryByAttribute $query ) {
        $metadata = $this->client->getMetadata();
        $entityMap = $metadata->getEntityMap( $query->EntityName );
        $inboundMap = $entityMap->inboundMap;
        $columnMap = array_flip( $inboundMap );

        $queryData = [];
        $filterQuery = [];
        foreach ( $query->Attributes as $attributeName => $value ) {
            $queryAttributeName = $columnMap[$attributeName];
            switch ( true ) {
                case (is_string( $value ) && !preg_match('/^\{?[a-zA-Z0-9]{8}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{4}-[a-zA-Z0-9]{12}\}?$/', $value)):
                    $queryValue ="'{$value}'"; break;
                case is_bool( $value):
                    $queryValue = $value? 'true' : 'false'; break;
                case $value === null:
                    $queryValue = 'null'; break;
                default:
                    $queryValue = $value;
            }

            $filterQuery[] = $queryAttributeName . ' eq ' . $queryValue;
        }
        if ( count( $filterQuery ) ) {
            $queryData['Filter'] = implode( ' and ', $filterQuery );
        }

        if ( $query->ColumnSet instanceof ColumnSet && !$query->ColumnSet->AllColumns ) {
            foreach ( $query->ColumnSet->Columns as $column ) {
                if ( !array_key_exists( $column, $columnMap ) ) {
                    $this->getLogger()->warning( "No inbound attribute mapping found for {$query->EntityName}[{$column}]" );
                    continue;
                }

                $queryData['Select'][] = $columnMap[$column];
            }
        }

        $orderMap = [
            0 => 'asc',
            1 => 'desc',
        ];
        foreach ( $query->Orders as $attributeName => $orderType ) {
            if ( !array_key_exists( $attributeName, $columnMap ) ) {
                $this->getLogger()->warning( "No inbound attribute mapping found for {$query->EntityName}[{$attributeName}] order setting" );
                continue;
            }

            $queryData['OrderBy'][] = $columnMap[$attributeName] . ' ' . $orderMap[$orderType->getValue()];
        }

        if ( $query->TopCount > 0 && $query->PageInfo instanceof PagingInfo ) {
            throw new \InvalidArgumentException( 'QueryByAttribute cannot have both TopCount and PageInfo properties set' );
        }

        if ( $query->TopCount > 0 ) {
            $queryData['Top'] = $query->TopCount;
        }

        if ( $query->PageInfo instanceof PagingInfo ) {
            if ( $query->PageInfo->Count > 0 ) {
                $queryData['MaxPageSize'] = $query->PageInfo->Count;
            }

            if ( isset( $query->PageInfo->PagingCookie ) ) {
                $queryData['SkipToken'] = $query->PageInfo->PagingCookie;
            }
        }

        $collectionName = $metadata->getEntitySetName( $query->EntityName );
        try {
            $response = $this->client->getList( $collectionName, $queryData );
        } catch ( ODataException $e ) {
            throw new Exception( 'RetrieveMultiple (QueryByAttribute) request failed: ' . $e->getMessage(), $e );
        }

        $collection = new EntityCollection();
        $collection->EntityName = $query->EntityName;
        $collection->MoreRecords = false;

        if ( !$response->Count ) {
            return $collection;
        }

        if ( isset( $response->SkipToken ) ) {
            $collection->PagingCookie = $response->SkipToken;
            $collection->MoreRecords = true;
        }

        $serializer = new SerializationHelper( $this->client );

        foreach ( $response->List as $item ) {
            $ref = new EntityReference( $query->EntityName );
            $recordKey = $entityMap->key;
            if ( array_key_exists( $recordKey, $item ) ) {
                $ref->Id = $item->{$recordKey};
            }

            $record = $serializer->deserializeEntity( $item, $ref );

            $collection->Entities[] = $record;
        }

        return $collection;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCachePool() : CacheItemPoolInterface {
        return $this->client->getCachePool();
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger() : LoggerInterface {
        return $this->client->getLogger();
    }

}

<?php
/**
 * Copyright 2018-2020 AlexaCRM
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
use AlexaCRM\WebAPI\OData\Client as ODataClient;
use AlexaCRM\WebAPI\OData\EntityNotSupportedException;
use AlexaCRM\WebAPI\OData\ODataException;
use AlexaCRM\WebAPI\OData\TransportException;
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
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;

/**
 * Represents the Organization-compatible Dynamics 365 Web API client.
 */
class Client implements IOrganizationService {

    /**
     * @var ODataClient
     */
    protected ODataClient $client;

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
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    public function Associate( string $entityName, string $entityId, Relationship $relationship, array $relatedEntities ): void {
        try {
            $metadata = $this->client->getMetadata();
            $collectionName = $metadata->getEntitySetName( $entityName );

            foreach ( $relatedEntities as $ref ) {
                $associatedCollectionName = $metadata->getEntitySetName( $ref->LogicalName );

                // TODO: execute in one request with a batch request
                $this->client->associate( $collectionName, $entityId, $relationship->SchemaName, $associatedCollectionName, $ref->Id );
            }
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'Associate request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot associate: entity `{$entityName}` is not supported", $e );
        }
    }

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return string ID of the new record.
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    public function Create( Entity $entity ): string {
        try {
            $serializer = new SerializationHelper( $this->client );
            $translatedData = $serializer->serializeEntity( $entity );

            $collectionName = $this->client->getMetadata()->getEntitySetName( $entity->LogicalName );

            $responseId = $this->client->create( $collectionName, $translatedData );

            $entity->getAttributeState()->reset();

            return $responseId;
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'Create request failed: ' . $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot create: entity `{$entity->LogicalName}` is not supported", $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        }
    }

    /**
     * Deletes a record.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     *
     * @return void
     * @throws OrganizationException
     * @throws ToolkitException
     * @throws AuthenticationException
     */
    public function Delete( string $entityName, string $entityId ): void {
        try {
            $metadata = $this->client->getMetadata();
            $collectionName = $metadata->getEntitySetName( $entityName );

            $this->client->delete( $collectionName, $entityId );
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'Delete request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot delete: entity `{$entityName}` is not supported", $e );
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
     * @throws OrganizationException
     * @throws ToolkitException
     * @throws AuthenticationException
     */
    public function Disassociate( string $entityName, string $entityId, Relationship $relationship, array $relatedEntities ): void {
        try {
            $metadata = $this->client->getMetadata();
            $collectionName = $metadata->getEntitySetName( $entityName );

            foreach ( $relatedEntities as $ref ) {
                $associatedCollectionName = $metadata->getEntitySetName( $ref->LogicalName );

                // TODO: execute in one request with a batch request
                $this->client->disassociate( $collectionName, $entityId, $relationship->SchemaName, $associatedCollectionName, $ref->Id );
            }
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'Disassociate request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot disassociate: entity `{$entityName}` is not supported", $e );
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
     * Retrieves a record.
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     * @param ColumnSet $columnSet
     *
     * @return Entity|null
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    public function Retrieve( string $entityName, string $entityId, ColumnSet $columnSet ): ?Entity {
        try {
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

                    $options['Select'][] = $columnMapping[ $column ];
                }
            }
            $options['Expand'] = $columnSet->GetExpandQueryOption();

            $response = $this->client->getRecord( $collectionName, $entityId, $options );

            $serializer = new SerializationHelper( $this->client );
            $entity = $serializer->deserializeEntity( $response, new EntityReference( $entityName, $entityId ) );

            return $entity;
        } catch ( ODataException $e ) {
            if ( $e->getCode() === 404 ) {
                return null;
            }

            throw new OrganizationException( 'Retrieve request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot retrieve: entity `{$entityName}` is not supported", $e );
        }
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface {
        return $this->client->getLogger();
    }

    /**
     * Retrieves a collection of records.
     *
     * @param QueryBase $query A query that determines the set of records to retrieve.
     *
     * @return EntityCollection
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    public function RetrieveMultiple( QueryBase $query ): EntityCollection {
        if ( $query instanceof FetchExpression ) {
            return $this->retrieveViaFetchXML( $query );
        }

        if ( $query instanceof QueryByAttribute ) {
            return $this->retrieveViaQueryByAttribute( $query );
        }

        return new EntityCollection();
    }

    /**
     * @param FetchExpression $query
     *
     * @return EntityCollection
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    protected function retrieveViaFetchXML( FetchExpression $query ): EntityCollection {
        try {
            $fetchDOM = new \DOMDocument( '1.0', 'utf-8' );
            $fetchDOM->loadXML( $query->Query );

            $entityTag = $fetchDOM->getElementsByTagName( 'entity' )->item( 0 );
            if ( !( $entityTag instanceof \DOMElement ) || !$entityTag->hasAttribute( 'name' ) ) {
                throw new ToolkitException( 'Malformed FetchXML query: could not locate the <entity/> element or entity name not specified' );
            }

            $entityName = $entityTag->getAttribute( 'name' );

            $metadata = $this->client->getMetadata();
            $collectionName = $metadata->getEntitySetName( $entityName );
            $entityMap = $metadata->getEntityMap( $entityName );

            $response = $this->client->getList( $collectionName, [
                'FetchXml' => $query->Query,
            ] );

            $collection = new EntityCollection();
            $collection->EntityName = $entityName;
            $collection->MoreRecords = false;
            $collection->TotalRecordCount = $response->TotalRecordCount;
            $collection->TotalRecordCountLimitExceeded = $response->TotalRecordCountLimitExceeded;

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
                if ( property_exists( $item, $recordKey ) ) {
                    $ref->Id = $item->{$recordKey};
                }

                $record = $serializer->deserializeEntity( $item, $ref, $entityRefTypeMap );

                $collection->Entities[] = $record;
            }

            return $collection;
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'RetrieveMultiple (FetchXML) request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot retrieve via FetchXML: entity `{$entityName}` is not supported", $e );
        }
    }

    /**
     * @param QueryByAttribute $query
     *
     * @return EntityCollection
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    protected function retrieveViaQueryByAttribute( QueryByAttribute $query ): EntityCollection {
        try {
            $metadata = $this->client->getMetadata();
            $entityMap = $metadata->getEntityMap($query->EntityName);
            $inboundMap = $entityMap->inboundMap;
            $columnMap = array_flip($inboundMap);

            $queryData = [];
            $filterQuery = [];
            foreach ($query->Filters as $filter) {
                $filterQuery[] = $filter->toString();
            }
            foreach ( $query->Attributes as $attributeName => $value ) {
                $queryAttributeName = $columnMap[ $attributeName ];

                $attributeType = '';
                if ( array_key_exists( $attributeName, $entityMap->fieldTypes ) ) {
                    $attributeType = $entityMap->fieldTypes[ $attributeName ];
                }

                if (is_array($value)) {
                    // If we have a group of filters, make sure to format them correctly
                    if (isset($value['filterSet'])) {
                        $filterSet = [];
                        foreach ($value['filterSet'] as $queryAttributeName => $filter) {
                            // For now - assume only 1 level of nesting ever.
                            if (is_array($value) && isset($filter['operator']) && is_string($filter['value'])) {
                                switch ($filter['operator']) {
                                    case 'contains':
                                    case 'endswith':
                                    case 'startswith':
                                        $filterSet[] = $filter['operator'] . '(' . $queryAttributeName . ', \'' . $filter['value'] . '\')';
                                        break;
                                    default:
                                        $filterSet[] = $queryAttributeName . ' ' . $filter['operator'] . ' ' . $filter['value'];
                                }
                            } elseif (is_array($value) && isset($filter['operator']) && is_array($filter['value'])) {
                                switch ($filter['operator']) {
                                    case 'NotIn':
                                        $propertyValues = json_encode($filter['value']);
                                        $filterSet[] = 'Microsoft.Dynamics.CRM.' . $filter['operator'] . '(PropertyName=\'' . $queryAttributeName . '\',PropertyValues=' . $propertyValues . ')';
                                        break;
                                }
                            }
                            else {
                                $filterSet[] = $queryAttributeName . ' eq ' . $filter;
                            }
                        }
                        $filterQuery[] = '(' . implode(' ' . $value['logicalOperator'] . ' ', $filterSet) . ')';

                    } else {
                        switch ($value['operator']) {
                            case 'contains':
                            case 'endswith':
                            case 'startswith':
                                $filterQuery[] = $value['operator'] . '(' . $queryAttributeName . ', \'' . $value['value'] . '\')';
                                break;
                            default:
                                $filterQuery[] = $queryAttributeName . ' ' . $value['operator'] . ' ' . $value['value'];
                        }
                    }
                }
                else {
                    switch ( true ) {
                        /*
                         * GUIDs may be stored as strings,
                         * but GUIDs in UniqueIdentifier attributes must not be enclosed in quotes.
                         */
                        case ( is_string( $value ) && $attributeType !== 'Edm.Guid' ):
                            $queryValue = "'{$value}'";
                            break;
                        case is_bool( $value ):
                            $queryValue = $value? 'true' : 'false';
                            break;
                        case $value === null:
                            $queryValue = 'null';
                            break;
                        default:
                            $queryValue = $value;
                    }

                    $filterQuery[] = $queryAttributeName . ' eq ' . $queryValue;
                }
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

                    $queryData['Select'][] = $columnMap[ $column ];
                }
                $queryData['Expand'] = $query->ColumnSet->GetExpandQueryOption();
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

                $queryData['OrderBy'][] = $columnMap[ $attributeName ] . ' ' . $orderMap[ $orderType->getValue() ];
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

                if ( $query->PageInfo->ReturnTotalRecordCount ) {
                    $queryData['IncludeCount'] = true;
                }
            }

            $collectionName = $metadata->getEntitySetName( $query->EntityName );
            $response = $this->client->getList( $collectionName, $queryData );

            $collection = new EntityCollection();
            $collection->EntityName = $query->EntityName;
            $collection->MoreRecords = false;
            $collection->TotalRecordCount = $response->TotalRecordCount;
            $collection->TotalRecordCountLimitExceeded = $response->TotalRecordCountLimitExceeded;

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
                if ( property_exists( $item, $recordKey ) ) {
                    $ref->Id = $item->{$recordKey};
                }

                $record = $serializer->deserializeEntity( $item, $ref );

                $collection->Entities[] = $record;
            }

            return $collection;
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'RetrieveMultiple (QueryByAttribute) request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot retrieve via QueryByAttribute: entity `{$query->EntityName}` is not supported", $e );
        }
    }

    /**
     * Updates an existing record.
     *
     * @param Entity $entity
     *
     * @return void
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */
    public function Update( Entity $entity ): void {
        try {
            $serializer = new SerializationHelper( $this->client );
            $translatedData = $serializer->serializeEntity( $entity );

            $collectionName = $this->client->getMetadata()->getEntitySetName( $entity->LogicalName );

            $this->client->update( $collectionName, $entity->Id, $translatedData );

            $entity->getAttributeState()->reset();
        } catch ( ODataException $e ) {
            throw new OrganizationException( 'Update request failed: ' . $e->getMessage(), $e );
        } catch ( TransportException $e ) {
            throw new ToolkitException( $e->getMessage(), $e );
        } catch ( EntityNotSupportedException $e ) {
            throw new ToolkitException( "Cannot update: entity `{$entity->LogicalName}` is not supported", $e );
        }
    }

    /**
     * Returns an instance of ODataClient for direct access to OData service and underlying transport.
     *
     * @return ODataClient
     */
    public function getClient(): ODataClient {
        return $this->client;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCachePool(): CacheItemPoolInterface {
        return $this->client->getCachePool();
    }

}

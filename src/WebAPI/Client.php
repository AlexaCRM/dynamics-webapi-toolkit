<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\OData\AuthenticationException;
use AlexaCRM\WebAPI\OData\InaccessibleMetadataException;
use AlexaCRM\WebAPI\OData\ODataException;
use AlexaCRM\WebAPI\OData\EntityNotSupportedException;
use AlexaCRM\WebAPI\OData\SerializationHelper;
use AlexaCRM\Xrm\ColumnSet;
use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityCollection;
use AlexaCRM\Xrm\EntityReference;
use AlexaCRM\Xrm\IOrganizationService;
use AlexaCRM\Xrm\Query\FetchExpression;
use AlexaCRM\Xrm\Query\OrderType;
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
        $entityMap = $metadata->entityMaps[$entityName]->inboundMap;

        $options = [];
        if ( $columnSet->AllColumns !== true ) {
            $options['Select'] = [];

            // $select must not be empty. Add primary key.
            $options['Select'][] = $metadata->entityMaps[$entityName]->key;

            $columnMapping = array_flip( $entityMap );
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

        $entity = new Entity( $entityName, $entityId );

        foreach ( $response as $field => $value ) {
            if ( stripos( $field, '@Microsoft' ) !== false || stripos( $field, '@OData' ) !== false ) {
                continue;
            }

            if ( !array_key_exists( $field, $entityMap ) || $value === null ) {
                $this->getLogger()->warning( "Received {$entityName}[$field] from Web API which is absent in the inbound attribute map" );
                continue;
            }

            $targetField = $entityMap[$field];
            $logicalNameField = $field . '@Microsoft.Dynamics.CRM.lookuplogicalname';
            $formattedValueField = $field . '@OData.Community.Display.V1.FormattedValue';
            if ( array_key_exists( $logicalNameField, $response ) ) {
                $entityRefValue = new EntityReference( $response->{$logicalNameField}, $value );
                if ( array_key_exists( $formattedValueField, $response ) ) {
                    $entityRefValue->Name = $response->{$formattedValueField};
                }

                $entity->Attributes[$targetField] = $entityRefValue;
                continue;
            }

            $entity->Attributes[$targetField] = $value; // TODO: convert to OptionSetValue if required per Metadata
            if ( array_key_exists( $formattedValueField, $response ) ) {
                $entity->FormattedValues[$targetField] = $response[$formattedValueField];
            }
        }


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
        $x = new \DOMXPath( $fetchDOM );

        $attrToEntity = [];
        $fetchAttributes = $x->query( '//attribute' );
        foreach ( $fetchAttributes as $attr ) {
            /**
             * @var \DOMElement $attr
             */
            $targetField = $attr->getAttribute( 'name' );
            if ( $attr->parentNode->nodeName === 'link-entity' ) {
                $targetField = $attr->parentNode->getAttribute( 'alias' ) . '.' . $targetField;
            }
            if ( $attr->hasAttribute( 'alias' ) ) {
                $targetField = $attr->getAttribute( 'alias' );
            }
            $attrToEntity[$targetField] = $attr->parentNode->getAttribute( 'name' );
        }

        $entityName = $fetchDOM->getElementsByTagName( 'entity' )->item( 0 )->getAttribute( 'name' );

        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->getEntitySetName( $entityName );
        $entityMap = $metadata->entityMaps[$entityName]->inboundMap;

        try {
            $response = $this->client->getList( $collectionName, [
                'FetchXml' => $query->Query,
            ] );
        } catch ( ODataException $e ) {
            throw new Exception( 'Retrieve (FetchXML) request failed: ' . $e->getMessage(), $e );
        }

        $collection = new EntityCollection();
        $collection->EntityName = $entityName;
        $collection->MoreRecords = false;

        if ( !$response->Count ) {
            return $collection;
        }

        /*
         * Unmarshal all fields as usual.
         *
         * If the value looks like GUID and has a FormattedValue annotation but no lookuplogicalname,
         * it's a lookup from a linked entity. Look up its logical name in FetchXML.
         * If any x002e are found, replace the divider with dot (.).
         */
        foreach ( $response->List as $item ) {
            $record = new Entity( $entityName );
            $recordKey = $metadata->entityMaps[$entityName]->key;
            if ( array_key_exists( $recordKey, $item ) ) {
                $record->Id = $item->{$recordKey};
            }

            foreach ( $item as $key => $value ) {
                if ( stripos( $key, '@Microsoft' ) !== false || stripos( $key, '@OData' ) !== false ) {
                    continue;
                }

                $targetField = array_key_exists( $key, $entityMap )? $entityMap[$key] : $key;
                $lookupField = $key . '@Microsoft.Dynamics.CRM.lookuplogicalname';
                $formattedField = $key . '@OData.Community.Display.V1.FormattedValue';
                $targetValue = $value;

                if ( strpos( $targetField, '_x002e_' ) !== false ) {
                    $targetField = str_replace( '_x002e_', '.', $targetField );
                }

                if ( array_key_exists( $lookupField, $item ) ) {
                    $targetValue = new EntityReference( $item->{$lookupField}, $value );
                    $targetValue->Name = $item->{$formattedField};
                } elseif ( preg_match( '~^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$~', $value ) && array_key_exists( $formattedField, $item ) ) {
                    // might be an aliased entity reference
                    if ( array_key_exists( $targetField, $attrToEntity ) ) {
                        $targetValue = new EntityReference( $attrToEntity[$targetField], $value );
                        $targetValue->Name = $item->{$formattedField};
                    }
                }

                $formattedValue = null;
                if ( array_key_exists( $formattedField, $item ) ) {
                    $formattedValue = $item->{$formattedField};
                }

                $record->Attributes[$targetField] = $targetValue;

                if ( $formattedValue !== null ) {
                    $record->FormattedValues[$targetField] = $formattedValue;
                }
            }

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
        $collectionName = $metadata->getEntitySetName( $query->EntityName );
        $entityMap = $metadata->entityMaps[$query->EntityName]->inboundMap;
        $columnMap = array_flip( $entityMap );

        $queryData = [];
        $filterQuery = [];
        foreach ( $query->Attributes as $attributeName => $value ) {
            $queryAttributeName = $columnMap[$attributeName];
            switch ( true ) {
                case is_string( $value ):
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

            /**
             * @var OrderType $orderType
             */

            $queryData['OrderBy'][] = $columnMap[$attributeName] . ' ' . $orderMap[$orderType->getValue()];
        }

        if ( $query->TopCount > 0 ) {
            $queryData['Top'] = $query->TopCount;
        }

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

        foreach ( $response->List as $item ) {
            $record = new Entity( $query->EntityName );
            $recordKey = $metadata->entityMaps[$query->EntityName]->key;
            if ( array_key_exists( $recordKey, $item ) ) {
                $record->Id = $item->{$recordKey};
            }

            foreach ( $item as $key => $value ) {
                if ( stripos( $key, '@Microsoft' ) !== false || stripos( $key, '@OData' ) !== false ) {
                    continue;
                }

                $targetField = array_key_exists( $key, $entityMap )? $entityMap[$key] : $key;
                $lookupField = $key . '@Microsoft.Dynamics.CRM.lookuplogicalname';
                $formattedField = $key . '@OData.Community.Display.V1.FormattedValue';
                $targetValue = $value;

                if ( array_key_exists( $lookupField, $item ) ) {
                    $targetValue = new EntityReference( $item->{$lookupField}, $value );
                    $targetValue->Name = $item->{$formattedField};
                }

                $formattedValue = null;
                if ( array_key_exists( $formattedField, $item ) ) {
                    $formattedValue = $item->{$formattedField};
                }

                $record->Attributes[$targetField] = $targetValue;

                if ( $formattedValue !== null ) {
                    $record->FormattedValues[$targetField] = $formattedValue;
                }
            }

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

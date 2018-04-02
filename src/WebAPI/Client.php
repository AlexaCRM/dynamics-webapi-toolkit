<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\Xrm\ColumnSet;
use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityCollection;
use AlexaCRM\Xrm\EntityReference;
use AlexaCRM\Xrm\IOrganizationService;
use AlexaCRM\Xrm\Query\FetchExpression;
use AlexaCRM\Xrm\Query\QueryBase;
use AlexaCRM\Xrm\Query\QueryByAttribute;
use AlexaCRM\Xrm\Relationship;
use AlexaCRM\WebAPI\OData\Client as ODataClient;

class Client implements IOrganizationService {

    /**
     * @var ODataClient
     */
    protected $client;

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
     */
    public function Associate( string $entityName, $entityId, Relationship $relationship, $relatedEntities ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->entitySetMap[$entityName];

        foreach ( $relatedEntities as $ref ) {
            $associatedCollectionName = $metadata->entitySetMap[$ref->LogicalName];

            // TODO: execute in one request with a batch request
            $this->client->Associate( $collectionName, $entityId, $relationship->SchemaName, $associatedCollectionName, $ref->Id );
        }
    }

    /**
     * Creates a record.
     *
     * @param Entity $entity
     *
     * @return string ID of the new record.
     */
    public function Create( Entity $entity ) {
        $metadata = $this->client->getMetadata();

        $collectionName = $metadata->entitySetMap[$entity->LogicalName];
        $data = [];
        foreach ( $entity->getAttributeState() as $fieldName => $_ ) {
            $data[$fieldName] = $entity[$fieldName];
        }

        $outboundMap = $metadata->entityMaps[$entity->LogicalName]->outboundMap;
        $translatedData = [];
        foreach ( $data as $field => $value ) {
            $outboundMapping = $outboundMap[$field];
            if ( is_string( $outboundMapping ) ) {
                $translatedData[$outboundMapping] = $value;
                continue;
            }

            if ( is_array( $outboundMapping ) && ( $value instanceof EntityReference || $value instanceof Entity ) ) {
                $logicalName = $value->LogicalName;
                if ( !array_key_exists( $logicalName, $outboundMapping) ) {
                    continue; // TODO: throw a typed exception
                }

                $fieldCollectionName = $metadata->entitySetMap[$logicalName];

                $translatedData[$outboundMapping[$logicalName] . '@odata.bind'] = sprintf( '/%s(%s)', $fieldCollectionName, $value->Id );
            }
        }

        $responseId = $this->client->Create( $collectionName, $translatedData );

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
     */
    public function Delete( string $entityName, $entityId ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->entitySetMap[$entityName]; // TODO: throw typed exception if no entity set found
        $this->client->Delete( $collectionName, $entityId );
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
     */
    public function Disassociate( string $entityName, $entityId, Relationship $relationship, $relatedEntities ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->entitySetMap[$entityName];

        foreach ( $relatedEntities as $ref ) {
            $associatedCollectionName = $metadata->entitySetMap[$ref->LogicalName];

            // TODO: execute in one request with a batch request
            $this->client->DeleteAssociation( $collectionName, $entityId, $relationship->SchemaName, $associatedCollectionName, $ref->Id );
        }
    }

    /**
     * Executes a function or action formed as a request.
     *
     * @param $request
     *
     * @return mixed
     */
    public function Execute( $request ) {
        return $this->client->ExecuteFunction( $request );
    }

    /**
     * Retrieves a record,
     *
     * @param string $entityName
     * @param string $entityId Record ID.
     * @param ColumnSet $columnSet
     *
     * @return Entity
     */
    public function Retrieve( string $entityName, $entityId, ColumnSet $columnSet ) : Entity {
        $metadata = $this->client->getMetadata();
        $entityMap = $metadata->entityMaps[$entityName]->inboundMap;
        $collectionName = $metadata->entitySetMap[$entityName]; // TODO: throw typed exception if no entity set found

        $options = [];
        if ( $columnSet->AllColumns !== true ) {
            $options['Select'] = [];
            $columnMapping = array_flip( $entityMap );
            foreach ( $columnSet->Columns as $column ) {
                if ( !array_key_exists( $column, $columnMapping ) ) {
                    continue;
                }

                $options['Select'][] = $columnMapping[$column];
            }
        }
        $response = $this->client->Get( $collectionName, $entityId, $options );

        $entity = new Entity( $entityName, $entityId );

        foreach ( $response as $field => $value ) {
            if ( !array_key_exists( $field, $entityMap ) || $value === null ) {
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

            $entity->Attributes[$targetField] = $value; // TODO: convert to OptionSetValue if required acc. to Metadata
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
     */
    public function Update( Entity $entity ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->entitySetMap[$entity->LogicalName];

        $data = [];
        foreach ( $entity->getAttributeState() as $fieldName => $_ ) {
            $data[$fieldName] = $entity[$fieldName];
        }

        $outboundMap = $metadata->entityMaps[$entity->LogicalName]->outboundMap;
        $translatedData = [];
        foreach ( $data as $field => $value ) {
            $outboundMapping = $outboundMap[$field];
            if ( is_string( $outboundMapping ) ) {
                $translatedData[$outboundMapping] = $value;
                continue;
            }

            if ( is_array( $outboundMapping ) && ( $value instanceof EntityReference || $value instanceof Entity ) ) {
                $logicalName = $value->LogicalName;
                if ( !array_key_exists( $logicalName, $outboundMapping) ) {
                    continue; // TODO: throw a typed exception
                }

                $fieldCollectionName = $metadata->entitySetMap[$logicalName];

                $translatedData[$outboundMapping[$logicalName] . '@odata.bind'] = sprintf( '/%s(%s)', $fieldCollectionName, $value->Id );
            }
        }

        $this->client->Update( $collectionName, $entity->Id, $translatedData );

        $entity->getAttributeState()->reset();
    }

    public function getClient() : ODataClient {
        return $this->client;
    }

    /**
     * @param FetchExpression $query
     *
     * @return EntityCollection
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
        $collectionName = $metadata->entitySetMap[$entityName];
        $entityMap = $metadata->entityMaps[$entityName]->inboundMap;

        $response = $this->client->GetList( $collectionName, [
            'FetchXml' => $query->Query,
        ] );

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
                if ( $key === '@odata.etag' || strpos( $key, '@Microsoft' ) !== false || strpos( $key, '@OData' ) !== false ) {
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

    protected function retrieveViaQueryByAttribute( QueryByAttribute $query ) {
        $metadata = $this->client->getMetadata();
        $collectionName = $metadata->entitySetMap[$query->EntityName];
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
                    continue;
                }

                $queryData['Select'][] = $columnMap[$column];
            }
        }

        foreach ( $query->Orders as $attributeName => $orderType ) {
            if ( !array_key_exists( $attributeName, $columnMap ) ) {
                continue;
            }

            $queryData['OrderBy'][] = $columnMap[$attributeName] . ' ' . $orderType;
        }

        if ( $query->TopCount > 0 ) {
            $queryData['Top'] = $query->TopCount;
        }

        $response = $this->client->GetList( $collectionName, $queryData );

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
                if ( $key === '@odata.etag' || strpos( $key, '@Microsoft' ) !== false || strpos( $key, '@OData' ) !== false ) {
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

}

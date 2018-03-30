<?php

namespace AlexaCRM\WebAPI;

use AlexaCRM\Xrm\ColumnSet;
use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityReference;
use AlexaCRM\Xrm\IOrganizationService;
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
        // TODO: Implement Associate() method.
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
        // TODO: Implement Disassociate() method.
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
     * @param $query
     *
     * @return mixed
     */
    public function RetrieveMultiple( $query ) {
        // TODO: Implement RetrieveMultiple() method.
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
}

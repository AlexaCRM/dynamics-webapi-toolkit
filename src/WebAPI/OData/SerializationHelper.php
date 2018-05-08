<?php

namespace AlexaCRM\WebAPI\OData;

use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityReference;

class SerializationHelper {

    /**
     * @var Metadata
     */
    protected $client;

    public function __construct( Client $client ) {
        $this->client = $client;
    }

    /**
     * Translate CRM attribute names to Web API outbound field names,
     * including odata.bind annotation for lookup attributes.
     *
     * @param Entity $entity
     *
     * @return array
     * @throws AuthenticationException
     * @throws EntityNotSupportedException
     * @throws InaccessibleMetadataException
     */
    public function serializeEntity( Entity $entity ) {
        $metadata = $this->client->getMetadata();

        $touchedFields = [];
        foreach ( $entity->getAttributeState() as $fieldName => $_ ) {
            $touchedFields[$fieldName] = $entity[$fieldName];
        }

        $outboundMap = $metadata->entityMaps[$entity->LogicalName]->outboundMap;
        $translatedData = [];

        /*
         */
        foreach ( $touchedFields as $field => $value ) {
            $outboundMapping = $outboundMap[$field];
            if ( is_string( $outboundMapping ) ) {
                $translatedData[$outboundMapping] = $value;
                continue; // Simple value mapping found.
            }

            if ( is_array( $outboundMapping ) && ( $value instanceof EntityReference || $value instanceof Entity ) ) {
                $logicalName = $value->LogicalName;
                if ( !array_key_exists( $logicalName, $outboundMapping) ) {
                    $this->client->getLogger()->error( "{$entity->LogicalName}[{$field}] lookup supplied with an unsupported entity type `{$logicalName}`" );
                    continue;
                }

                $fieldCollectionName = $metadata->getEntitySetName( $logicalName );

                $translatedData[$outboundMapping[$logicalName] . '@odata.bind'] = sprintf( '/%s(%s)', $fieldCollectionName, $value->Id );
            }

            $this->client->getLogger()->warning( "No outbound attribute mapping found for {$entity->LogicalName}[{$field}]" );
        }

        return $translatedData;
    }

}

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

namespace AlexaCRM\WebAPI\OData;

use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityReference;

/**
 * A helper library to facilitate serialization of Xrm objects for Web API.
 */
class SerializationHelper {

    /**
     * @var Client
     */
    protected $client;

    /**
     * SerializationHelper constructor.
     *
     * @param Client $client
     */
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

    /**
     * @param mixed $rawEntity Output from the OData Client.
     * @param EntityReference $reference A reference containing the logical name and ID of the processed record.
     * @param array $attributeToEntityMap
     *
     * @return Entity
     * @throws AuthenticationException
     * @throws InaccessibleMetadataException
     */
    public function deserializeEntity( $rawEntity, EntityReference $reference, $attributeToEntityMap = null ) {
        $targetEntity = new Entity( $reference->LogicalName, $reference->Id );

        $metadata = $this->client->getMetadata();
        $entityMap = $metadata->entityMaps[$targetEntity->LogicalName]->inboundMap;

        foreach ( $rawEntity as $field => $value ) {
            if ( stripos( $field, '@Microsoft' ) !== false || stripos( $field, '@OData' ) !== false ) {
                continue;
            }

            if ( $attributeToEntityMap === null && ( !array_key_exists( $field, $entityMap ) || $value === null ) ) {
                $this->client->getLogger()->warning( "Received {$targetEntity->LogicalName}[$field] from Web API which is absent in the inbound attribute map" );
                continue;
            }

            $targetField = array_key_exists( $field, $entityMap )? $entityMap[$field] : $field;
            $logicalNameField = $field . '@Microsoft.Dynamics.CRM.lookuplogicalname';
            $formattedValueField = $field . '@OData.Community.Display.V1.FormattedValue';
            $targetValue = $value;

            if ( $attributeToEntityMap !== null && strpos( $targetField, '_x002e_' ) !== false ) {
                $targetField = str_replace( '_x002e_', '.', $targetField );
            }

            if ( array_key_exists( $logicalNameField, $rawEntity ) ) {
                $targetValue = new EntityReference( $rawEntity->{$logicalNameField}, $value );
            } elseif ( $attributeToEntityMap !== null && preg_match( '~^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$~', $value ) && array_key_exists( $formattedValueField, $rawEntity ) ) {
                // might be an aliased entity reference
                if ( array_key_exists( $targetField, $attributeToEntityMap ) ) {
                    $targetValue = new EntityReference( $attributeToEntityMap[$targetField], $value );
                }
            }

            $targetEntity->Attributes[$targetField] = $targetValue; // TODO: convert to OptionSetValue if required per Organization Metadata

            // Import formatted value.
            if ( array_key_exists( $formattedValueField, $rawEntity ) ) {
                $targetEntity->FormattedValues[$targetField] = $rawEntity->{$formattedValueField};

                if ( $targetValue instanceof EntityReference ) {
                    $targetValue->Name = $rawEntity->{$formattedValueField};
                }
            }
        }

        return $targetEntity;
    }

    /**
     * Returns a map of aliased lookup to lookup type associations.
     *
     * Web API doesn't return a Microsoft.Dynamics.CRM.lookuplogicalname annotation
     * for aliased lookup attributes (both from entity and link-entity).
     *
     * @param string $fetchXML
     *
     * @return array
     */
    public function getFetchXMLAliasedLookupTypes( $fetchXML ) {
        $fetchDOM = new \DOMDocument( '1.0', 'utf-8' );
        $fetchDOM->loadXML( $fetchXML );
        $x = new \DOMXPath( $fetchDOM );

        $attrToEntity = [];
        $fetchAttributes = $x->query( '//attribute' );
        foreach ( $fetchAttributes as $attr ) {
            /**
             * @var \DOMElement $attr
             */
            $targetField = $attr->getAttribute( 'name' );
            $attributeEntity = $attr->parentNode->getAttribute( 'name' );
            $attributeMap = $this->client->getMetadata()->entityMaps[$attributeEntity]->outboundMap[$targetField];
            if ( !is_array( $attributeMap ) ) {
                continue;
            }

            $targetEntity = array_shift( array_keys( $attributeMap ) );

            if ( $attr->parentNode->nodeName === 'link-entity' ) {
                $targetField = $attr->parentNode->getAttribute( 'alias' ) . '.' . $targetField;
            }
            if ( $attr->hasAttribute( 'alias' ) ) {
                $targetField = $attr->getAttribute( 'alias' );
            }
            $attrToEntity[$targetField] = $targetEntity;
        }

        return $attrToEntity;
    }

}

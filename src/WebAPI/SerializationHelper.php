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

use AlexaCRM\WebAPI\OData\Annotation;
use AlexaCRM\Xrm\Entity;
use AlexaCRM\Xrm\EntityReference;

/**
 * A helper library to facilitate serialization of Xrm objects for Web API.
 */
class SerializationHelper {

    /**
     * @var OData\Client
     */
    protected $client;

    /**
     * SerializationHelper constructor.
     *
     * @param OData\Client $client
     */
    public function __construct( OData\Client $client ) {
        $this->client = $client;
    }

    /**
     * Translate CRM attribute names to Web API outbound field names,
     * including odata.bind annotation for lookup attributes.
     *
     * @param Entity $entity
     *
     * @return array
     * @throws OData\AuthenticationException
     * @throws OData\EntityNotSupportedException
     * @throws OData\TransportException
     */
    public function serializeEntity( Entity $entity ) {
        $metadata = $this->client->getMetadata();

        $entityMap = $metadata->getEntityMap( $entity->LogicalName );
        $outboundMap = $entityMap->outboundMap;

        $touchedFields = [];
        foreach ( $entity->getAttributeState() as $fieldName => $_ ) {
            $touchedFields[$fieldName] = $entity[$fieldName];
        }

        $translatedData = [];

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

                $translatedData[$outboundMapping[$logicalName] . Annotation::ODATA_BIND] = sprintf( '/%s(%s)', $fieldCollectionName, $value->Id );

                continue;
            }

            $this->client->getLogger()->warning( "No outbound attribute mapping found for {$entity->LogicalName}[{$field}]" );
        }

        return $translatedData;
    }

    /**
     * Creates a new Entity instance from the OData entity object.
     *
     * $attributeToEntityMap is used to create proper EntityReference instances
     * for FetchXML results with aliased lookups - Web API loses lookup information for these
     * and doesn't produce appropriate annotations.
     *
     * @param mixed $rawEntity Output from the OData Client.
     * @param EntityReference $reference A reference containing the logical name and ID of the processed record.
     * @param array $attributeToEntityMap
     *
     * @return Entity
     * @throws OData\AuthenticationException
     * @throws OData\EntityNotSupportedException
     * @throws OData\TransportException
     */
    public function deserializeEntity( $rawEntity, EntityReference $reference, $attributeToEntityMap = null ) {
        $metadata = $this->client->getMetadata();
        $entityMap = $metadata->getEntityMap( $reference->LogicalName );

        $targetEntity = new Entity( $reference->LogicalName, $reference->Id );

        $inboundMap = $entityMap->inboundMap;

        foreach ( $rawEntity as $field => $value ) {
            if ( stripos( $field, '@Microsoft' ) !== false || stripos( $field, '@OData' ) !== false ) {
                continue;
            }

            if ( !array_key_exists( $field, $inboundMap ) ) {
                $this->client->getLogger()->warning( "Received {$targetEntity->LogicalName}[$field] from Web API which is absent in the inbound attribute map", [ 'inboundMap' => $inboundMap ] );
            }

            if ( $attributeToEntityMap === null && ( !array_key_exists( $field, $inboundMap ) || $value === null ) ) {
                continue;
            }

            $targetField = array_key_exists( $field, $inboundMap )? $inboundMap[$field] : $field;
            $logicalNameField = $field . Annotation::CRM_LOOKUPLOGICALNAME;
            $formattedValueField = $field . Annotation::ODATA_FORMATTEDVALUE;
            $targetValue = $value;

            if ( $attributeToEntityMap !== null && strpos( $targetField, '_x002e_' ) !== false ) {
                $targetField = str_replace( '_x002e_', '.', $targetField );
            }

            /*
             * CRM aliased FetchXML attribute quirk.
             *
             * TL;DR When querying FetchXML against CRM < 9.0(?) entity references may hold a wrong entity type
             * if the lookup attribute is aliased or comes from a joined entity and the lookup has multiple
             * targets.
             *
             * Usually, lookup attributes are accompanied by a `Microsoft.Dynamics.CRM.lookuplogicalname` annotation.
             * Prior to CRM 9.0 (no info on when precisely it was fixed), aliased lookup attributes in FetchXML queries
             * were only accompanied by an `OData.Community.Display.V1.FormattedValue` annotation,
             * and entity type information was lost and had to be GUESSED.
             *
             * It is critical for navigation properties which can accept multiple entity types (Customer, Principal,
             * etc.). We could have deduce the entity type from the `Microsoft.Dynamics.CRM.associatednavigationproperty`
             * annotation, but it is not available for aliased attributes either.
             *
             * To make things worse, link-entity attributes are aliased all the time and get the same treatment.
             * It means, prior to 9.0 there is absolutely no entity type information, and in 9.0+ you only get
             * the logical name.
             *
             * It is why we have to make a guess and map the entity type statically to the first available
             * entity type targeted by the lookup attribute. We could validate logical name / ID pair in CRM
             * but it is a costly procedure, especially for lookups with a long Targets list.
             */
            if ( array_key_exists( $logicalNameField, $rawEntity ) ) {
                $targetValue = new EntityReference( $rawEntity->{$logicalNameField}, $value );
            } elseif ( $attributeToEntityMap !== null
                       && preg_match( '~^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$~', $value )
                       && array_key_exists( $formattedValueField, $rawEntity ) ) {
                /*
                 * Map to a static entity type if we've got a GUID and a formatted value and no entity type information.
                 * It means we're likely in the quirk mode and have to try guessing the lookup entity type.
                 */
                if ( array_key_exists( $targetField, $attributeToEntityMap ) ) {
                    $targetValue = new EntityReference( $attributeToEntityMap[$targetField], $value );
                }
            }

            $targetEntity->Attributes[$targetField] = $targetValue;

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
        $attrToEntity = [];

        try {
            $metadata = $this->client->getMetadata();
        } catch ( OData\Exception $e ) {
            return $attrToEntity;
        }

        $fetchDOM = new \DOMDocument( '1.0', 'utf-8' );
        $fetchDOM->loadXML( $fetchXML );
        $x = new \DOMXPath( $fetchDOM );
        $fetchAttributes = $x->query( '//attribute' );
        foreach ( $fetchAttributes as $attr ) {
            /**
             * @var \DOMElement $attr
             */
            $targetField = $attr->getAttribute( 'name' );
            $attributeEntity = $attr->parentNode->getAttribute( 'name' );

            if ( !array_key_exists( $attributeEntity, $metadata->entityMaps ) ) {
                continue;
            }

            $entityMap = $metadata->entityMaps[$attributeEntity];

            if ( !array_key_exists( $targetField, $entityMap->outboundMap ) ) {
                continue;
            }

            $attributeMap = $entityMap->outboundMap[$targetField];
            if ( !is_array( $attributeMap ) ) {
                continue;
            }

            $attributeEntities = array_keys( $attributeMap );
            $targetEntity = array_shift( $attributeEntities );

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

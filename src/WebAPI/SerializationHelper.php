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
    protected OData\Client $client;

    /**
     * SerializationHelper constructor.
     *
     * @param OData\Client $client
     */
    public function __construct( OData\Client $client ) {
        $this->client = $client;
    }

    /**
     * Loop through the outbound map for the Entity.
     * If the $field is found, return the key - this will be the entity type name.
     */
    public function findEntityType($outboundMap, $field): ?string
    {
        $type = null;

        array_walk_recursive($outboundMap, function ($value, $key) use (&$type, $field) {
            if ($value == $field) {
                $type = $key;

                return;
            }
        });

        return $type;
    }

    /**
     * Translates CRM attribute names to Web API outbound field names,
     * including the `odata.bind` annotation for lookup attributes.
     *
     * @param Entity $entity
     *
     * @return array
     * @throws OData\AuthenticationException
     * @throws OData\EntityNotSupportedException
     * @throws OData\TransportException
     */
    public function serializeEntity( Entity $entity ): array {
        $metadata = $this->client->getMetadata();

        $entityMap = $metadata->getEntityMap( $entity->LogicalName );
        $outboundMap = $entityMap->outboundMap;

        // Use an inverted inbound map to disassociate records with AutoDisassociate: true.
        $disassociateMap = array_flip( $entityMap->inboundMap );

        $touchedFields = [];
        foreach ( $entity->getAttributeState() as $fieldName => $_ ) {
            $touchedFields[$fieldName] = $entity[$fieldName];
        }

        $translatedData = [];

        foreach ( $touchedFields as $field => $value ) {
            $outboundMapping = $outboundMap[$field];
            $isLookup = is_array( $outboundMapping );

            if ( is_string( $outboundMapping ) ) {
                $translatedData[$outboundMapping] = $value;
                continue; // Simple value mapping found.
            }

            if ( $isLookup && $value === null ) {
                /*
                 * Exploit the AutoDisassociate workaround to seamlessly disassociate lookup records
                 * on record update.
                 *
                 * Utilizes the `AutoDisassociate: true` header with the corresponding read-only single-valued
                 * navigation properties being set to NULL.
                 *
                 * Proven to work in Web API 9.0 and 9.1.
                 *
                 * {
                 *   "_primarycontactid_value": null
                 * }
                 */
                $translatedData[ $disassociateMap[ $field ] ] = null;
                continue;
            } elseif ( $isLookup && ( $value instanceof EntityReference || $value instanceof Entity ) ) {
                /*
                 * Associate records using the @odata.bind annotation.
                 */
                $logicalName = $value->LogicalName;

                if ( !array_key_exists( $logicalName, $outboundMapping ) ) {
                    $this->client->getLogger()->error( "{$entity->LogicalName}[{$field}] lookup supplied with an unsupported entity type `{$logicalName}`" );
                    continue;
                }

                $fieldCollectionName = $metadata->getEntitySetName( $logicalName );

                $annotation = $outboundMapping[$logicalName] . Annotation::ODATA_BIND;
                $translatedData[ $annotation ] = sprintf( '/%s(%s)', $fieldCollectionName, $value->Id );

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
     * for FetchXML results with aliased lookups - Web API loses lookup type information for these
     * and doesn't produce appropriate annotations.
     *
     * @param object $rawEntity Output from the OData Client.
     * @param EntityReference $reference A reference containing the logical name and ID of the processed record.
     * @param array|null $attributeToEntityMap
     *
     * @return Entity
     * @throws OData\AuthenticationException
     * @throws OData\EntityNotSupportedException
     * @throws OData\TransportException
     */
    public function deserializeEntity(
      object $rawEntity,
      EntityReference $reference,
      ?array $attributeToEntityMap = null
    ): Entity {
        $metadata = $this->client->getMetadata();
        $entityMap = $metadata->getEntityMap($reference->LogicalName);

        $targetEntity = new Entity($reference->LogicalName, $reference->Id);

        $inboundMap = $entityMap->inboundMap;

        foreach ($rawEntity as $field => $value) {
            // Skip irrelevant data
            if (stripos($field, '@Microsoft') !== false || stripos($field, '@OData') !== false) {
                continue;
            }

            $targetField = array_key_exists($field, $inboundMap) ? $inboundMap[$field] : $field;
            $logicalNameField = $field.Annotation::CRM_LOOKUPLOGICALNAME;
            $formattedValueField = $field.Annotation::ODATA_FORMATTEDVALUE;
            $targetValue = $value;

            // If the field doesn't exist, check if we're expanding a relationship, otherwise log it.
            if (! array_key_exists($field, $inboundMap)) {
                /**
                 * If the type is an 'object', assume it's a nested relationship entity
                 * Deserialise the data and set the $targetValue to the Entity.
                 */
                if (gettype($value) == 'object') {
                    if ($relationType = $this->findEntityType($entityMap->outboundMap, $field)) {
                        $targetValue = $this->deserializeEntity($value, new EntityReference($relationType, $value->{"{$relationType}id"}));
                    }
                } else {
                    $this->client->getLogger()
                      ->debug("Received {$targetEntity->LogicalName}[$field] from Web API which is absent in the inbound attribute map",
                        [
                          'field' => $field,
                          'inboundMap' => $inboundMap,
                        ]);
                }
            }

            if ($attributeToEntityMap !== null && strpos($targetField, '_x002e_') !== false) {
                $targetField = str_replace('_x002e_', '.', $targetField);
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
            if (property_exists($rawEntity, $logicalNameField)) {
                $targetValue = new EntityReference($rawEntity->{$logicalNameField}, $value);
            } elseif ($attributeToEntityMap !== null
              && is_string($value)
              && preg_match('~^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$~', $value)
              && property_exists($rawEntity, $formattedValueField)) {
                /*
                 * Map to a static entity type if we've got a GUID and a formatted value and no entity type information.
                 * It means we're likely in the quirk mode and have to try guessing the lookup entity type.
                 */
                if (array_key_exists($targetField, $attributeToEntityMap)) {
                    $targetValue = new EntityReference($attributeToEntityMap[$targetField], $value);
                }
            }

            $targetEntity->Attributes[$targetField] = $targetValue;

            // Import formatted value.
            if (property_exists($rawEntity, $formattedValueField)) {
                $targetEntity->FormattedValues[$targetField] = $rawEntity->{$formattedValueField};

                if ($targetValue instanceof EntityReference) {
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
    public function getFetchXMLAliasedLookupTypes( string $fetchXML ): array {
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

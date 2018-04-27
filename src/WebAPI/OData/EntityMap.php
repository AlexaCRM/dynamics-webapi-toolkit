<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * Represents an OData EntityType, including its key, type hierarchy,
 * and incoming/outgoing attribute maps.
 */
class EntityMap {

    /**
     * Entity name.
     *
     * @var string
     */
    public $name;

    /**
     * Entity primary key name.
     *
     * @var string
     */
    public $key;

    /**
     * Whether the entity is abstract.
     *
     * @var bool
     */
    public $isAbstract;

    /**
     * Name of the base entity.
     *
     * @var string
     */
    public $baseEntity;

    /**
     * Map of field names coming from Web API into concrete field names.
     *
     * Allows addressing read-only navigation properties (_field_value)
     * by their real name.
     *
     * @var array
     *
     * @see \AlexaCRM\WebAPI\Client::Retrieve() Used for entity unmarshalling and ColumnSet marshalling.
     * @see \AlexaCRM\WebAPI\Client::RetrieveMultiple() Used for entity unmarshalling.
     */
    public $inboundMap = [];

    /**
     * Map of concrete field names into OData basic / navigation property names.
     *
     * For basic properties, the mapping is [ concreteFieldName => ODataFieldName ], which is identical.
     *
     * For navigation properties, the mapping is [ concreteFieldName => [ type => ODataFieldName ] ].
     * The rationale is that some concrete field names may be represented as multiple navigation property,
     * e.g. customerid converting to customerid_account for 'account' records, and customerid_contact
     * for 'contact' records.
     *
     * @var array
     */
    public $outboundMap = [];

    /**
     * Creates an entity map from an CSDL EntityType node.
     *
     * @param \DOMElement $element EntityType DOM node.
     * @param Metadata $metadata OData metadata object.
     *
     * @return static
     */
    public static function createFromDOM( \DOMElement $element, Metadata $metadata ) {
        $map = new static();

        $x = new \DOMXPath( $element->ownerDocument );
        $x->registerNamespace( 'edmx', Metadata::NS_EDMX );
        $x->registerNamespace( 'edm', Metadata::NS_EDM );

        $map->name = $element->getAttribute( 'Name' );
        $map->key = $x->evaluate( 'string(edm:Key/edm:PropertyRef/@Name)', $element );
        if ( $map->key === '' ) {
            $map->key = null;
        }

        $map->isAbstract = $element->hasAttribute( 'Abstract' ) && $element->getAttribute( 'Abstract' ) === 'true';
        $map->baseEntity = $element->hasAttribute( 'BaseType' )? $metadata->stripNamespace( $element->getAttribute( 'BaseType' ) ) : null;

        $propertiesList = $x->query( 'edm:Property', $element );
        foreach ( $propertiesList as $propertyElement ) {
            /**
             * @var \DOMElement $propertyElement
             */
            $propertyName = $propertyElement->getAttribute( 'Name' );
            $concretePropertyName = preg_replace( '~^_(.*)_value$~', '$1', $propertyName );

            /*
             * Build the inbound map.
             *
             * As-is properties by default, _(.*)_value => $1
             */
            $map->inboundMap[$propertyName] = $concretePropertyName;

            /*
             * Build the outbound map.
             *
             * As-is properties by default.
             * For _(.*)_value, find corresponding navigation properties.
             * We get a map $1 => [ Type => NavigationPropertyName ]
             */
            $referentialConstraints = $x->query( "edm:NavigationProperty/edm:ReferentialConstraint[@Property='{$propertyName}']", $element );
            if ( !$referentialConstraints->length ) {
                $map->outboundMap[$propertyName] = $propertyName;
                continue;
            }
            $map->outboundMap[$concretePropertyName] = [];
            foreach ( $referentialConstraints as $referentialConstraint ) {
                /**
                 * @var \DOMElement $referentialConstraint
                 */
                $navigationProperty = $referentialConstraint->parentNode;
                $navType = $metadata->stripNamespace( $navigationProperty->getAttribute( 'Type' ) );

                $map->outboundMap[$concretePropertyName][$navType] = $navigationProperty->getAttribute( 'Name' );

                /*
                 * Resolve possible abstract types into concrete types. E.g. principal => systemuser, team.
                 */
                if ( array_key_exists( $navType, $metadata->parentTypesMap ) ) {
                    foreach ( $metadata->parentTypesMap[$navType] as $concreteType ) {
                        $map->outboundMap[$concretePropertyName][$concreteType] = $navigationProperty->getAttribute( 'Name' );
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Applies the current entity map on top of the given entity.
     *
     * @param EntityMap $map Base entity map.
     */
    public function rebuildFromBase( EntityMap $map ) {
        $current = clone $this;

        $this->key = $map->key;
        if ( $current->key !== null ) {
            $this->key = $current->key;
        }

        $this->inboundMap = array_merge( $map->inboundMap, $current->inboundMap );
        $this->outboundMap = $map->outboundMap;
        foreach ( $current->outboundMap as $propName => $mapping ) {
            if ( is_string( $mapping ) ) {
                $this->outboundMap[$propName] = $mapping;
                continue;
            }

            $baseMapping = array_key_exists( $propName, $map->outboundMap )? $map->outboundMap[$propName] : [];
            $this->outboundMap[$propName] = array_merge( $baseMapping, $mapping );
        }

        unset( $current );
    }

}

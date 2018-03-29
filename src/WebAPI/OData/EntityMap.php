<?php

namespace AlexaCRM\WebAPI\OData;

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

    public $inboundMap;

    public $outboundMap;

    /**
     * @param \DOMElement $element
     * @param Metadata $metadata
     *
     * @return static
     */
    public static function createFromDOM( \DOMElement $element, Metadata $metadata ) {
        $map = new static();

        $x = new \DOMXPath( $element->ownerDocument );
        $x->registerNamespace( 'edmx', 'http://docs.oasis-open.org/odata/ns/edmx' );
        $x->registerNamespace( 'edm', 'http://docs.oasis-open.org/odata/ns/edm' );

        $map->name = $element->getAttribute( 'Name' );
        $map->key = $x->evaluate( 'string(edm:Key/edm:PropertyRef/@Name)', $element );
        if ( $map->key === '' ) {
            $map->key = null;
        }

        $map->isAbstract = $element->hasAttribute( 'Abstract' ) && $element->getAttribute( 'Abstract' ) === 'true';
        $map->baseEntity = $element->hasAttribute( 'BaseType' )? $metadata->stripNamespace( $element->getAttribute( 'BaseType' ) ) : null;

        $propertiesList = $x->query( 'edm:Property', $element );


        $map->inboundMap = $map->outboundMap = [];
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

            /**
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

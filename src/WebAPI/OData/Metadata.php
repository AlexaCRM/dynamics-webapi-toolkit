<?php

namespace AlexaCRM\WebAPI\OData;

use AlexaCRM\WebAPI\OData\Metadata\EntitySet;
use AlexaCRM\WebAPI\OData\Metadata\EntityType;

class Metadata {

    /**
     * Service schema namespace;
     *
     * @var string
     */
    public $namespace;

    /**
     * Registered namespace alias.
     *
     * @var string
     */
    public $alias;

    /**
     * Map of declared entity types.
     *
     * @var EntityType[]
     */
    public $entityTypes;

    /**
     * Map of declared entity sets.
     *
     * @var EntitySet[]
     */
    public $entitySets;

    public static function createFromXML( $xml ) {
        $metadata = new Metadata();
        $metadata->entityTypes = [];

        $dom = new \DOMDocument( '1.0', 'utf-8' );
        if ( !$dom->loadXML( $xml ) ) {
            return null;
        }

        $x = new \DOMXPath( $dom );
        $x->registerNamespace( 'edmx', 'http://docs.oasis-open.org/odata/ns/edmx' );
        $x->registerNamespace( 'edm', 'http://docs.oasis-open.org/odata/ns/edm' );

        $metadata->namespace = $x->evaluate( 'string(/edmx:Edmx/edmx:DataServices/edm:Schema/@Namespace)' );
        $metadata->alias = $x->evaluate( 'string(/edmx:Edmx/edmx:DataServices/edm:Schema/@Alias)' );

        $abstractEntities = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType[@Abstract='true']" );
        foreach ( $abstractEntities as $abstractEntity ) {
            $newType = EntityType::createFromDOMNode( $abstractEntity, $metadata );
            $metadata->entityTypes[$newType->name] = $newType;
        }

        $entityTypes = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType[not(@Abstract='true')]" );
        foreach ( $entityTypes as $entityType ) {
            $newType = EntityType::createFromDOMNode( $entityType, $metadata );
            $metadata->entityTypes[$newType->name] = $newType;
        }

        return $metadata;
    }

    public function stripNamespace( $typeName ) {
        $typeName = str_replace( $this->namespace . '.', '', $typeName );
        $typeName = str_replace( $this->alias . '.', '', $typeName );

        return $typeName;
    }

}

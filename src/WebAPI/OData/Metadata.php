<?php

namespace AlexaCRM\WebAPI\OData;

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
     * @var EntityMap[]
     */
    public $entityMaps;

    /**
     * Lists all types which have children, together with their descendants.
     *
     * @var array
     */
    public $parentTypesMap;

    public static function createFromXML( $xml ) {
        $metadata = new Metadata();

        $dom = new \DOMDocument( '1.0', 'utf-8' );
        if ( !$dom->loadXML( $xml ) ) {
            return null;
        }

        $x = new \DOMXPath( $dom );
        $x->registerNamespace( 'edmx', 'http://docs.oasis-open.org/odata/ns/edmx' );
        $x->registerNamespace( 'edm', 'http://docs.oasis-open.org/odata/ns/edm' );

        $metadata->namespace = $x->evaluate( 'string(/edmx:Edmx/edmx:DataServices/edm:Schema/@Namespace)' );
        $metadata->alias = $x->evaluate( 'string(/edmx:Edmx/edmx:DataServices/edm:Schema/@Alias)' );

        $baseTypeList = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType/@BaseType" );
        $baseTypes = [];
        foreach ( $baseTypeList as $baseTypeElement ) {
            $baseTypes[] = $baseTypeElement->nodeValue;
        }
        $baseTypes = array_map( [ $metadata, 'stripNamespace'], array_unique( $baseTypes ) );

        $metadata->parentTypesMap = [];
        foreach ( $baseTypes as $baseType ) {
            $typeChildren = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType[@BaseType='mscrm.{$baseType}']" );
            foreach ( $typeChildren as $typeChild ) {
                $metadata->parentTypesMap[$baseType][] = $typeChild->getAttribute( 'Name' );
            }
        }

        foreach ( $baseTypes as $baseType ) {
            $baseEntity = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType[@Name='{$baseType}']" );
            if ( !$baseEntity->length ) {
                continue;
            }

            $metadata->entityMaps[$baseType] = EntityMap::createFromDOM( $baseEntity->item( 0 ), $metadata );
        }

        foreach ( $metadata->entityMaps as $entityMap ) {
            if ( $entityMap->baseEntity === null ) {
                continue;
            }

            $baseEntityName = $entityMap->baseEntity;
            $baseType = $metadata->entityMaps[$baseEntityName];
            $entityMap->rebuildFromBase( $baseType );
        }

        $skipEntities = array_keys( $metadata->entityMaps );
        $typesList = $x->query( '/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType' );
        foreach ( $typesList as $type ) {
            /**
             * @var \DOMElement $type
             */
            $typeName = $type->getAttribute( 'Name' );
            if ( in_array( $typeName, $skipEntities ) ) {
                continue;
            }
            $newMap = EntityMap::createFromDOM( $type, $metadata );
            $metadata->entityMaps[$typeName] = $newMap;

            $baseType = $metadata->entityMaps[$newMap->baseEntity];
            $metadata->entityMaps[$typeName]->rebuildFromBase( $baseType );
        }

        return $metadata;
    }

    public function stripNamespace( $typeName ) {
        $typeName = str_replace( [ $this->namespace . '.', $this->alias . '.' ], '', $typeName );

        return $typeName;
    }

}

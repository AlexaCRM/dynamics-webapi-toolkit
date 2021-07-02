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

/**
 * Represents Web API OData service metadata.
 */
class Metadata {

    const NS_EDMX = 'http://docs.oasis-open.org/odata/ns/edmx';
    const NS_EDM = 'http://docs.oasis-open.org/odata/ns/edm';

    /**
     * Service schema namespace;
     */
    public string $namespace;

    /**
     * Registered namespace alias.
     */
    public string $alias;

    /**
     * Collection of entity type maps, covering incoming and outgoing field conversions.
     *
     * It is assumed that this collection contains AT LEAST entities listed in Metadata::$entitySetMap.
     *
     * @var EntityMap[]
     */
    public array $entityMaps = [];

    /**
     * Lists all types which have children, together with their descendants.
     *
     * @var array
     */
    public array $parentTypesMap = [];

    /**
     * EntityType to EntitySet map.
     *
     * @var array
     */
    public array $entitySetMap = [];

    /**
     * Creates a new metadata object from a CSDL document.
     *
     * @param string $xml
     *
     * @return Metadata|null
     */
    public static function createFromXML( string $xml ): ?self {
        $metadata = new self();

        $dom = new \DOMDocument( '1.0', 'utf-8' );
        if ( !$dom->loadXML( $xml ) ) {
            return null;
        }

        $x = new \DOMXPath( $dom );
        $x->registerNamespace( 'edmx', static::NS_EDMX );
        $x->registerNamespace( 'edm', static::NS_EDM );

        $metadata->namespace = $x->evaluate( 'string(/edmx:Edmx/edmx:DataServices/edm:Schema/@Namespace)' );
        $metadata->alias = $x->evaluate( 'string(/edmx:Edmx/edmx:DataServices/edm:Schema/@Alias)' );

        // Extract base types first.
        $baseTypeList = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType/@BaseType" );
        $baseTypes = [];
        foreach ( $baseTypeList as $baseTypeElement ) {
            $baseTypes[] = $baseTypeElement->nodeValue;
        }
        $baseTypes = array_map( [ $metadata, 'stripNamespace'], array_unique( $baseTypes ) );

        /*
         * Build a plain (non-nested) type dependency hierarchy.
         *
         * Used to expand the list of accepted types in the outbound mapping.
         * Example: navigation property is of type mscrm.principal, which is an abstract type
         * having mscrm.systemuser and mscrm.team descendants.
         */
        $metadata->parentTypesMap = [];
        foreach ( $baseTypes as $baseType ) {
            $typeChildren = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType[@BaseType='mscrm.{$baseType}']" );
            foreach ( $typeChildren as $typeChild ) {
                /**
                 * @var \DOMElement $typeChild
                 */
                $metadata->parentTypesMap[$baseType][] = $typeChild->getAttribute( 'Name' );
            }
        }

        /*
         * Create base types' entity maps.
         */
        foreach ( $baseTypes as $baseType ) {
            $baseEntity = $x->query( "/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityType[@Name='{$baseType}']" );
            if ( !$baseEntity->length ) {
                continue;
            }

            $metadata->entityMaps[$baseType] = EntityMap::createFromDOM( $baseEntity->item( 0 ), $metadata );
        }

        /*
         * As some of the base types may be derived from one another, rebuild them on top of existing base types.
         */
        foreach ( $metadata->entityMaps as $entityMap ) {
            if ( $entityMap->baseEntity === null ) {
                continue;
            }

            $baseEntityName = $entityMap->baseEntity;
            $baseType = $metadata->entityMaps[$baseEntityName];
            $entityMap->rebuildFromBase( $baseType );
        }

        /*
         * Create remaining entity maps and skip the already created ones.
         */
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
            if ($baseType !== null) {
                $metadata->entityMaps[$typeName]->rebuildFromBase( $baseType );
            }
        }

        /*
         * Create the EntityType -> EntitySet map.
         */
        $entitySetsList = $x->query( '/edmx:Edmx/edmx:DataServices/edm:Schema/edm:EntityContainer/edm:EntitySet' );
        foreach ( $entitySetsList as $entitySet ) {
            /**
             * @var \DOMElement $entitySet
             */
            $entityType = $metadata->stripNamespace( $entitySet->getAttribute( 'EntityType' ) );
            $metadata->entitySetMap[$entityType] = $entitySet->getAttribute( 'Name' );
        }

        return $metadata;
    }

    /**
     * Returns an OData entity map.
     *
     * @param string $entityName
     *
     * @return EntityMap
     * @throws EntityNotSupportedException
     */
    public function getEntityMap( string $entityName ) {
        if ( !array_key_exists( $entityName, $this->entityMaps ) ) {
            throw new EntityNotSupportedException( "Entity `{$entityName}` is not supported by Web API" );
        }

        return $this->entityMaps[$entityName];
    }

    /**
     * Returns an entity set name corresponding to the given entity name.
     *
     * Throws a typed exception if the entity doesn't have its own entity set.
     *
     * @param string $entityName
     *
     * @return string
     * @throws EntityNotSupportedException
     */
    public function getEntitySetName( string $entityName ) {
        if ( !array_key_exists( $entityName, $this->entitySetMap ) ) {
            throw new EntityNotSupportedException( "Entity `{$entityName}` is not supported by Web API" );
        }

        return $this->entitySetMap[$entityName];
    }

    /**
     * Strips the namespace or namespace alias from the type identifier.
     *
     * @param string $typeName
     *
     * @return string
     */
    public function stripNamespace( $typeName ) {
        $typeName = str_replace( [ $this->namespace . '.', $this->alias . '.' ], '', $typeName );

        return $typeName;
    }

}

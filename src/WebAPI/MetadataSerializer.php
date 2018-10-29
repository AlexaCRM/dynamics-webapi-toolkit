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
 */

namespace AlexaCRM\WebAPI;

use AlexaCRM\WebAPI\Serializer\Reference;
use AlexaCRM\Xrm\Metadata\EntityMetadata;
use Elao\Enum\EnumInterface;

/**
 * Provides a facility to deserialize organization metadata.
 */
class MetadataSerializer {

    /**
     * FQCN => strongly-typed properties map.
     *
     * @var array
     */
    protected static $map;

    /**
     * Deserializes the given data object into a concrete metadata object.
     *
     * @param object $data
     *
     * @return EntityMetadata
     */
    public function createEntityMetadata( $data ) {
        $md = $this->createPerDefinition( $data, new Reference( EntityMetadata::class ) );

        return $md;
    }

    /**
     * Creates a strongly-typed object or a collection or map of such objects.
     *
     * It further casts certain fields to referenced types as described in the type definition.
     *
     * @param object|object[] $data Generic object with properties.
     * @param Reference $typeDefinition Type definition.
     *
     * @return array|object
     */
    protected function createPerDefinition( $data, Reference $typeDefinition ) {
        switch ( true ) {
            case $typeDefinition->isMap():
                $obj = $this->createStrongObjMap( $data, $typeDefinition );
                break;
            case $typeDefinition->isCollection():
                $obj = $this->createStrongObjCollection( $data, $typeDefinition );
                break;
            default:
                $obj = $this->createStrongObj( $data, $typeDefinition );
        }

        foreach ( $typeDefinition->getCastMap() as $fieldName => $ref ) {
            if ( !property_exists( $obj, $fieldName ) ) {
                continue;
            }

            $obj->{$fieldName} = $this->createPerDefinition( $obj->{$fieldName}, $ref );
        }

        return $obj;
    }

    /**
     * Creates a strongly-typed object. $type holds the FQCN of the type.
     *
     * @param object
     * @param Reference $type
     *
     * @return object
     */
    protected function createStrongObj( $data, Reference $type ) {
        if ( $data === null ) {
            return null;
        }

        $className = $type->getClassName( $data );

        // Create enums separately.
        if ( is_subclass_of( $className, EnumInterface::class ) ) {
            return $className::$data();
        }

        $obj = new $className();

        $typedProperties = $this->getClassTypedProperties( $className );

        foreach ( $data as $key => $value ) {
            if ( strpos( $key, '@' ) !== false ) {
                continue;
            }

            if ( !array_key_exists( $key, $typedProperties ) ) {
                $obj->{$key} = $value;
                continue;
            }

            $obj->{$key} = $this->createPerDefinition( $value, $typedProperties[$key] );
        }

        return $obj;
    }

    /**
     * Creates a collection of strongly-typed objects.
     *
     * @param object[] $data
     * @param Reference $type
     *
     * @return array
     */
    protected function createStrongObjCollection( $data, Reference $type ) {
        $collection = [];

        foreach ( $data as $value ) {
            $collection[] = $this->createStrongObj( $value, $type );
        }

        return $collection;
    }

    /**
     * Creates a collection of strongly-typed objects enumerated by object key value.
     *
     * @param object[] $data
     * @param Reference $type
     *
     * @return array
     */
    protected function createStrongObjMap( $data, Reference $type ) {
        $collection = $this->createStrongObjCollection( $data, $type );

        $map = [];
        $keyName = $type->getMapKey();
        foreach ( $collection as $obj ) {
            $map[ $obj->{$keyName} ] = $obj;
        }

        return $map;
    }

    /**
     * Returns a map of type references for the given class including class hierarchy.
     *
     * @param string $className
     *
     * @return Reference[]
     */
    protected function getClassTypedProperties( string $className ) {
        if ( static::$map === null ) {
            static::$map = require 'metadataClassMap.php';
        }

        $parents = class_parents( $className );
        if ( $parents === false ) {
            return [];
        }

        $map = [];
        $parents = array_reverse( array_values( $parents ) );

        foreach ( $parents as $parentClassName ) {
            if ( !array_key_exists( $parentClassName, static::$map ) ) {
                continue;
            }

            $map = array_merge( $parents, static::$map[$parentClassName] );
        }

        if ( array_key_exists( $className, static::$map ) ) {
            $map = array_merge( $map, static::$map[$className] );
        }

        return $map;
    }

}

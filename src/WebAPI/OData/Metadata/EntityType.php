<?php

namespace AlexaCRM\WebAPI\OData\Metadata;

use AlexaCRM\WebAPI\OData\Metadata;

class EntityType {

    public $name;

    /**
     * @var bool
     */
    public $isAbstract;

    /**
     * Primary key of the entity type.
     *
     * @var string
     */
    public $key;

    /**
     * Map of entity type properties indexed by property name.
     *
     * @var Property[]
     */
    public $properties;

    public $navigationProperties;

    /**
     * @param \DOMElement $node
     *
     * @return EntityType
     */
    public static function createFromDOMNode( $node, Metadata $metadata = null ) {
        $entityType = new EntityType();

        if ( $node->hasAttribute( 'BaseType' ) ) {
            $baseTypeName = $metadata->stripNamespace( $node->getAttribute( 'BaseType' ) );
            $entityType = clone $metadata->entityTypes[$baseTypeName];
        }

        $entityType->name = $node->getAttribute( 'Name' );
        $entityType->isAbstract = $node->hasAttribute( 'Abstract' ) && $node->getAttribute( 'Abstract' ) === 'true';

        return $entityType;
    }

}

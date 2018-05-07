<?php

namespace AlexaCRM\Xrm;

/**
 * Represents a relationship between two entities.
 */
class Relationship {

    /**
     * Entity role: referencing or referenced.
     *
     * @var EntityRole|null
     */
    public $PrimaryEntityRole;

    /**
     * The name of the relationship.
     *
     * @var string
     */
    public $SchemaName;

    /**
     * Relationship constructor.
     *
     * @param string $schemaName The name of the relationship.
     */
    public function __construct( string $schemaName = null ) {
        if ( $schemaName !== null ) {
            $this->SchemaName = $schemaName;
        }
    }

}

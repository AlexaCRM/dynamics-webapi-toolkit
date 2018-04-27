<?php

namespace AlexaCRM\Xrm;

/**
 * Represents a relationship between two entities.
 */
class Relationship {

    /**
     * Specifies that the entity is the referencing entity.
     */
    const ROLE_REFERENCING = 0;

    /**
     * Specifies that the entity is the referenced entity.
     */
    const ROLE_REFERENCED = 1;

    /**
     * Entity role: referencing or referenced.
     *
     * @var int
     * @see Relationship::ROLE_REFERENCING
     * @see Relationship::ROLE_REFERENCED
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

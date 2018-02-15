<?php

namespace AlexaCRM\Xrm;

use AlexaCRM\Guid;
use Ramsey\Uuid\UuidInterface;

/**
 * Identifies a record in Dynamics 365.
 */
class EntityReference {

    /**
     * Unique ID of the record.
     *
     * @var Guid
     */
    public $Id;

    /**
     * Logical name of the entity.
     *
     * @var string
     */
    public $LogicalName;

    /**
     * Value of the primary attribute of the entity.
     *
     * @var string|null
     */
    public $Name;

    /**
     * Key attributes of the record.
     *
     * @var string[string]
     */
    public $KeyAttributes;

    /**
     * EntityReference constructor.
     *
     * An EntityReference instance may be created without any parameters specified,
     * or with entity name specified, or with entity name and record ID specified,
     * or with entity name and collection of KeyAttributes specified,
     * or with entity name, key name and key value specified.
     *
     * @param string $entityName
     * @param Guid|KeyAttributeCollection|string $entityId
     * @param mixed $keyValue Blabla
     */
    public function __construct( string $entityName = null, $entityId = null, $keyValue = null ) {
        if ( $entityName !== null ) {
            $this->LogicalName = $entityName;
        } else {
            return; // other properties cannot be set without a concrete entityName value
        }

        if ( $entityId instanceof UuidInterface ) {
            $this->Id = $entityId;

            return;
        }

        if ( $entityId instanceof KeyAttributeCollection ) {
            $keyAttributes = $entityId;
            $this->KeyAttributes = $keyAttributes;

            return;
        }

        if ( is_string( $entityId ) && $keyValue !== null ) {
            $this->KeyAttributes = new KeyAttributeCollection();
            $keyName = $entityId;
            $this->KeyAttributes->Add( $keyName, $keyValue );
        }
    }

}

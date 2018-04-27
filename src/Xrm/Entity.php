<?php

namespace AlexaCRM\Xrm;

/**
 * Represents a record in Dynamics 365.
 *
 * TODO: Missing fields (EntityState, RelatedEntities, RowVersion)
 * TODO: Missing Methods (GetRelatedEntities, GetRelatedEntity, SetRelatedEntities, SetRelatedEntity)
 */
class Entity implements \ArrayAccess {

    /**
     * Unique ID of the record.
     *
     * @var string
     */
    public $Id;

    /**
     * Logical name of the entity.
     *
     * @var string
     */
    public $LogicalName;

    /**
     * Key attributes of the record.
     *
     * @var string[string]
     */
    public $KeyAttributes;

    /**
     * Collection of entity attributes.
     *
     * @var array
     */
    public $Attributes;

    /**
     * Collection of formatted values for the entity attributes.
     *
     * @var array
     */
    public $FormattedValues;

    /**
     * Collection of attributes' states.
     * If the attribute key exists and equals true, the attribute value has been changed
     * since the last time entity was persisted in CRM.
     *
     * @var AttributeState
     */
    protected $attributeState;

    /**
     * Entity instance constructor.
     *
     * An Entity instance may be created without any parameters specified,
     * or with entity name specified, or with entity name and record ID specified,
     * or with entity name and collection of KeyAttributes specified,
     * or with entity name, key name and key value specified.
     *
     * @param string $entityName Entity logical name
     * @param string|KeyAttributeCollection $entityId Record ID, KeyAttributeCollection, or key name
     * @param mixed $keyValue Key value
     */
    public function __construct( string $entityName = null, $entityId = null, $keyValue = null ) {
        $this->attributeState = new AttributeState();

        if ( $entityName === null ) {
            return;
        }

        $this->LogicalName = $entityName;

        if ( $entityId === null && $keyValue === null ) {
            return;
        }

        if ( $entityId instanceof KeyAttributeCollection ) {
            $keyAttributes = $entityId;
            $this->KeyAttributes = $keyAttributes;

            return;
        }

        if ( is_string( $entityId ) && $keyValue === null ) {
            $this->Id = $entityId;

            return;
        }

        $this->KeyAttributes = new KeyAttributeCollection();
        $keyName = $entityId;
        $this->KeyAttributes->Add( $keyName, $keyValue );
    }

    /**
     * Tells whether specified attribute value exists.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function Contains( string $attribute ) {
        return array_key_exists( $attribute, $this->Attributes );
    }

    /**
     * Gets the value of the attribute.
     *
     * Returns NULL if the entity doesn't have the specified attribute.
     *
     * @param string $attribute
     *
     * @return mixed|null
     */
    public function GetAttributeValue( string $attribute ) {
        if ( !$this->Contains( $attribute ) ) {
            return null;
        }

        return $this->Attributes[$attribute];
    }

    /**
     * Gets the formatted value of the attribute.
     *
     * Returns empty string if the entity doesn't have the specified formatted value.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function GetFormattedAttributeValue( string $attribute ) {
        if ( !array_key_exists( $attribute, $this->FormattedValues ) ) {
            return '';
        }

        return $this->FormattedValues[$attribute];
    }

    /**
     * Sets the value of the attribute.
     *
     * @param string $attribute
     * @param $value
     */
    public function SetAttributeValue( string $attribute, $value ) {
        $this->Attributes[$attribute] = $value;
        $this->attributeState[$attribute] = true;
    }

    /**
     * Gets an entity reference for this entity instance.
     *
     * @return EntityReference
     */
    public function ToEntityReference() {
        $ref = new EntityReference( $this->LogicalName );

        if ( $this->Id !== null ) {
            $ref->Id = $this->Id;

            return $ref;
        }

        if ( $this->KeyAttributes instanceof KeyAttributeCollection && $this->KeyAttributes->Count > 0 ) {
            $ref->KeyAttributes = clone $this->KeyAttributes;
        }

        // TODO: Fill in EntityReference::$Name via MetadataRegistry

        return $ref;
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists( $offset ) {
        return $this->Contains( $offset );
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ) {
        return $this->GetAttributeValue( $offset );
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->SetAttributeValue( $offset, $value );
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset) {
        unset( $this->Attributes[$offset] );
        unset( $this->attributeState[$offset] );
    }

    public function getAttributeState() {
        return $this->attributeState;
    }

}

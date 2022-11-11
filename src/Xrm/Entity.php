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

namespace AlexaCRM\Xrm;

/**
 * Represents a record in Dynamics 365.
 */
class Entity implements \ArrayAccess {

    use EntityLikeTrait;

    /**
     * Collection of entity attributes.
     *
     * @var array
     */
    public array $Attributes = [];

    /**
     * Collection of formatted values for the entity attributes.
     *
     * @var array
     */
    public array $FormattedValues = [];

    /**
     * Collection of attributes' states.
     * If the attribute key exists and equals true, the attribute value has been changed
     * since the last time entity was persisted in CRM.
     *
     * @var AttributeState
     */
    protected AttributeState $attributeState;

    /**
     * Entity instance constructor.
     *
     * An Entity instance may be created without any parameters specified,
     * or with entity name specified, or with entity name and record ID specified,
     * or with entity name and collection of KeyAttributes specified,
     * or with entity name, key name and key value specified.
     *
     * @param string|null $entityName Entity logical name
     * @param string|KeyAttributeCollection|null $entityId Record ID, KeyAttributeCollection, or key name
     * @param mixed $keyValue Key value
     */
    public function __construct( string $entityName = null, $entityId = null, $keyValue = null ) {
        $this->attributeState = new AttributeState();
        $this->constructOverloaded( $entityName, $entityId, $keyValue );
    }

    /**
     * Tells whether specified attribute value exists.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function Contains( string $attribute ): bool {
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

        return $this->Attributes[ $attribute ];
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
    public function GetFormattedAttributeValue( string $attribute ): string {
        if ( !array_key_exists( $attribute, $this->FormattedValues ) ) {
            return '';
        }

        return $this->FormattedValues[ $attribute ];
    }

    /**
     * Sets the value of the attribute.
     *
     * @param string $attribute
     * @param mixed $value
     */
    public function SetAttributeValue( string $attribute, $value ): void {
        $this->Attributes[ $attribute ] = $value;
        $this->attributeState[ $attribute ] = true;
    }

    /**
     * Gets an entity reference for this entity instance.
     *
     * @return EntityReference
     */
    public function ToEntityReference(): EntityReference {
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
    public function offsetExists( $offset ): bool {
        return $this->Contains( $offset );
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ): mixed {
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
    public function offsetSet( $offset, $value ): void {
        $this->SetAttributeValue( $offset, $value );
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset( $offset ): void {
        unset( $this->Attributes[ $offset ], $this->attributeState[ $offset ] );
    }

    public function getAttributeState(): AttributeState {
        return $this->attributeState;
    }

    /**
     * When an object is cloned, PHP 5 will perform a shallow copy of all of the object's properties.
     * Any properties that are references to other variables, will remain references.
     * Once the cloning is complete, if a __clone() method is defined,
     * then the newly created object's __clone() method will be called, to allow any necessary properties that need to be changed.
     * NOT CALLABLE DIRECTLY.
     *
     * @return void
     * @link https://php.net/manual/en/language.oop5.cloning.php
     */
    public function __clone() {
        $this->attributeState = clone $this->attributeState;

        foreach ( $this->Attributes as $field => &$value ) {
            if ( !is_object( $value ) ) {
                continue;
            }

            $value = clone $value;
        }
    }
}

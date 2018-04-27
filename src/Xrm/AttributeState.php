<?php

namespace AlexaCRM\Xrm;

use Traversable;

/**
 * Represents the state of fields in an entity.
 *
 * $attributeState['fieldname'] returns true if the changed attribute hasn't been persisted yet.
 */
class AttributeState implements \ArrayAccess, \IteratorAggregate {

    /**
     * @var array
     */
    protected $attributes;

    public function reset() {
        foreach ( $this->attributes as $attribute => &$state ) {
            $state = false;
        }
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset An offset to check for.
     *
     * @return boolean true on success or false on failure.
     */
    public function offsetExists( $offset ) {
        return array_key_exists( $offset, $this->attributes );
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ) {
        return array_key_exists( $offset, $this->attributes ) && $this->attributes[$offset] === true;
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     *
     * @return void
     */
    public function offsetSet( $offset, $value ) {
        $this->attributes[$offset] = $value;
    }

    /**
     * Offset to unset.
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset( $offset ) {
        unset( $this->attributes[$offset] );
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Traversable An instance of an object implementing Iterator or Traversable.
     */
    public function getIterator() {
        return new \ArrayIterator( $this->attributes );
    }

}

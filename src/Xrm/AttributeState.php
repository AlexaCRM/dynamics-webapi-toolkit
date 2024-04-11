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
    protected array $attributes = [];

    public function reset(): void {
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
    public function offsetExists( $offset ): bool {
        return array_key_exists( $offset, $this->attributes );
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ): mixed {
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
    public function offsetSet( $offset, $value ): void {
        $this->attributes[$offset] = $value;
    }

    /**
     * Offset to unset.
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset( $offset ): void {
        unset( $this->attributes[$offset] );
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Traversable An instance of an object implementing Iterator or Traversable.
     */
    public function getIterator(): Traversable {
        return new \ArrayIterator( $this->attributes );
    }

}

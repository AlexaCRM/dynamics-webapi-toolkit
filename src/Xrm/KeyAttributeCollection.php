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
 * Represents the key attribute collection.
 *
 * @property-read int $Count Gets the number of key attributes in the collection.
 */
class KeyAttributeCollection implements \Iterator, \Countable, \ArrayAccess {

    /**
     * Collection of key attributes and values.
     *
     * @var array
     */
    protected array $keys = [];

    /**
     * Adds a key attribute value to the collection.
     *
     * @param string $key
     * @param mixed $value
     */
    public function Add( string $key, $value ): void {
        $this->keys[ $key ] = $value;
    }

    /**
     * Removes a key attribute from the collection.
     *
     * @param string $key
     *
     * @return bool
     */
    public function Remove( string $key ): bool {
        if ( !array_key_exists( $key, $this->keys ) ) {
            return false;
        }

        unset( $this->keys[ $key ] );

        return true;
    }

    /**
     * Getters.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get( string $name ) {
        switch ( $name ) {
            case 'Count':
                return count( $this->keys );
        }

        return null;
    }

    /**
     * Return the current element.
     *
     * @return mixed Can return any type.
     */
    public function current() {
        return current( $this->keys );
    }

    /**
     * Move forward to next element.
     *
     * @return void Any returned value is ignored.
     */
    public function next(): void {
        next( $this->keys );
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key() {
        return key( $this->keys );
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function valid(): bool {
        $key = $this->key();

        return ( $key !== null && $key !== false );
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void Any returned value is ignored.
     */
    public function rewind(): void {
        reset( $this->keys );
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset
     *
     * @return bool True on success or false on failure.
     */
    public function offsetExists( $offset ): bool {
        return array_key_exists( $offset, $this->keys );
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet( $offset ) {
        return $this->keys[ $offset ];
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @return void
     */
    public function offsetSet( $offset, $value ): void {
        $this->Add( $offset, $value );
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset The offset to unset.
     *
     * @return void
     */
    public function offsetUnset( $offset ): void {
        $this->Remove( $offset );
    }

    /**
     * Count elements of an object.
     *
     * @return int The custom count as an integer.
     */
    public function count(): int {
        return count( $this->keys );
    }

}

<?php

namespace AlexaCRM\Xrm;

/**
 * Represents the key attribute collection.
 *
 * @property-read int $Count Gets the number of key attributes in the collection.
 */
class KeyAttributeCollection implements \Iterator {

    /**
     * Collection of key attributes and values.
     *
     * @var array
     */
    protected $keys = [];

    /**
     * Adds a key attribute value to the collection.
     *
     * @param string $key
     * @param mixed $value
     */
    public function Add( $key, $value ) {
        $this->keys[ $key ] = $value;
    }

    /**
     * Removes a key attribute from the collection.
     *
     * @param string $key
     *
     * @return bool
     */
    public function Remove( $key ) : bool {
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
    public function __get( $name ) {
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
    public function next() {
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
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid() {
        $key = $this->key();

        return ( $key !== null && $key !== false );
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void Any returned value is ignored.
     */
    public function rewind() {
        reset( $this->keys );
    }

}

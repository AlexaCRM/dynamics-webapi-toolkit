<?php

namespace AlexaCRM\Xrm;

class KeyAttributeCollection implements \Iterator {

    /**
     * @var array
     */
    protected $keys = [];

    public function Add( string $key, $value ) : void {
        $this->keys[ $key ] = $value;
    }

    public function Remove( string $key ) : bool {
        if ( !array_key_exists( $key, $this->keys ) ) {
            return false;
        }

        unset( $this->keys[ $key ] );

        return true;
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current() {
        return current( $this->keys );
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        next( $this->keys );
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return key( $this->keys );
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        $key = $this->key();

        return ( $key !== null && $key !== false );
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        reset( $this->keys );
    }

}

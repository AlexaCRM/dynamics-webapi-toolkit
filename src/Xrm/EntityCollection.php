<?php

namespace AlexaCRM\Xrm;

/**
 * Contains a collection of entity instances.
 */
class EntityCollection implements \Iterator {

    /**
     * Collection of entities.
     *
     * @var Entity[]
     */
    public $Entities = [];

    /**
     * Logical name of the entity.
     *
     * @var string
     */
    public $EntityName;

    /**
     * Shows whether there are more records available.
     *
     * @var bool
     */
    public $MoreRecords;

    /**
     * Paging information.
     *
     * @var string
     */
    public $PagingCookie;

    /**
     * Total number of records.
     *
     * @var int
     */
    public $TotalRecordCount;

    /**
     * Return the current element.
     *
     * @return mixed
     */
    public function current() {
        return current( $this->Entities );
    }

    /**
     * Move forward to next element.
     *
     * @return void
     */
    public function next() {
        next( $this->Entities );
    }

    /**
     * Return the key of the current element.
     *
     * @return mixed Scalar on success, or null on failure.
     */
    public function key() {
        return key( $this->Entities );
    }

    /**
     * Checks if current position is valid.
     *
     * @return boolean Returns true on success or false on failure.
     */
    public function valid() {
        return isset( $this->Entities[ $this->key() ] );
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @return void
     */
    public function rewind() {
        reset( $this->Entities );
    }

}

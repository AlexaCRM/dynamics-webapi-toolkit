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
     * Whether the results of the query exceeds the total record count.
     *
     * @var bool
     */
    public $TotalRecordCountLimitExceeded;

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

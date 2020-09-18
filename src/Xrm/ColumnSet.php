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
 * Specifies the attributes for which non-null values are returned from a query.
 */
class ColumnSet {

    /**
     * Whether to retrieve all attributes of a record.
     *
     * @var bool
     */
    public bool $AllColumns = false;

    /**
     * Collection of attribute names to be retrieved.
     *
     * @var array
     */
    public array $Columns = [];

    /**
     * ColumnSet constructor.
     *
     * @param array|bool $columns If the parameter is boolean, ColumnSet::$AllColumns is set.
     */
    public function __construct( $columns = [] ) {
        if ( is_bool( $columns ) && $columns === true ) {
            $this->AllColumns = true;

            return;
        }

        $this->Columns = $columns;
    }

    /**
     * Adds an attribute to the column set.
     *
     * @param string $column
     */
    public function AddColumn( string $column ): void {
        if ( in_array( $column, $this->Columns, true ) ) {
            return;
        }

        $this->Columns[] = $column;
        $this->Columns = array_unique( $this->Columns );
    }

    /**
     * Adds multiple columns to the column set.
     *
     * @param string[] $columns
     */
    public function AddColumns( array $columns ): void {
        $this->Columns = array_merge( $this->Columns, $columns );
        $this->Columns = array_unique( $this->Columns );
    }

}

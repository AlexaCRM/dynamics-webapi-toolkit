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

namespace AlexaCRM\Xrm\Query;

use AlexaCRM\Xrm\ColumnSet;

/**
 * Contains a query that is expressed as a set of attribute and value pairs.
 */
class QueryByAttribute extends QueryBase {

    /**
     * Map of field => value used in the query.
     *
     * @var array
     */
    public $Attributes = [];

    /**
     * The column set selected for the query.
     *
     * @var ColumnSet
     */
    public $ColumnSet;

    /**
     * Name of the entity to query.
     *
     * @var string
     */
    public $EntityName;

    /**
     * Specifies the order in which the entity instances are returned from the query.
     *
     * @var OrderType[]
     */
    public $Orders = [];

    /**
     * The number of pages and the number of entity instances per page returned from the query.
     *
     * @var PagingInfo
     */
    public $PageInfo;

    /**
     * The number of rows to be returned.
     *
     * @var int
     */
    public $TopCount;

    /**
     * QueryByAttribute constructor.
     *
     * @param string $entityName
     */
    public function __construct( string $entityName = null ) {
        if ( $entityName !== null ) {
            $this->EntityName = $entityName;
        }
    }

    /**
     * Adds an attribute value to the attributes collection.
     *
     * @param string $attributeName The logical name of the attribute.
     * @param mixed $value The attribute value.
     */
    public function AddAttributeValue( string $attributeName, $value ) {
        $this->Attributes[$attributeName] = $value;
    }

    /**
     * Adds an order to the orders collection.
     *
     * @param string $attributeName The logical name of the attribute.
     * @param OrderType $orderType The order for that attribute.
     */
    public function AddOrder( string $attributeName, OrderType $orderType ) {
        $this->Orders[$attributeName] = $orderType;
    }

}

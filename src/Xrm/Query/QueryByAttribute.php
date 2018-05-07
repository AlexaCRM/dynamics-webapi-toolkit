<?php

namespace AlexaCRM\Xrm\Query;

use AlexaCRM\Xrm\ColumnSet;

/**
 * Contains a query that is expressed as a set of attribute and value pairs.
 */
class QueryByAttribute extends QueryBase {

    const ORDER_ASCENDING = 'asc';

    const ORDER_DESCENDING = 'desc';

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
     * @var array
     *
     * @see OrderType
     */
    public $Orders = [];

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

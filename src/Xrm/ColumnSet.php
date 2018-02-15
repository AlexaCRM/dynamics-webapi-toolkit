<?php

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
    public $AllColumns;

    /**
     * Collection of attribute names to be retrieved.
     *
     * @var string[]
     */
    public $Columns;

    /**
     * ColumnSet constructor.
     *
     * @param string[]|bool $columns If the parameter is boolean, ColumnSet::$AllColumns is set.
     */
    public function __construct( $columns = [] ) {
    }

    /**
     * Adds an attribute to the column set.
     *
     * @param string $column
     */
    public function AddColumn( string $column ) {
        if ( in_array( $column, $this->Columns, true ) ) {
            return;
        }

        $this->Columns[] = $column;
    }

    /**
     * Adds multiple columns to the column set.
     *
     * @param string[] $columns
     */
    public function AddColumns( array $columns ) {
        $this->Columns = array_merge( $this->Columns, $columns );
        $this->Columns = array_unique( $this->Columns );
    }

}

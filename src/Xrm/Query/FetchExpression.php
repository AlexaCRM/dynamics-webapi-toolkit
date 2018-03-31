<?php

namespace AlexaCRM\Xrm\Query;

class FetchExpression extends QueryBase {

    /**
     * The FetchXML query string.
     *
     * @var string
     */
    public $Query;

    /**
     * Initializes a new instance of the FetchExpression class.
     *
     * @param string $query The FetchXML query string.
     */
    public function __construct( string $query = '' ) {
        $this->Query = $query;
    }

}

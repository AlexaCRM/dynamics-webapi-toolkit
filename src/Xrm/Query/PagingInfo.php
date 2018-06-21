<?php

namespace AlexaCRM\Xrm\Query;

/**
 * Specifies a number of pages and a number of entity instances per page to return from the query.
 */
class PagingInfo {

    /**
     * The number of entity instances returned per page.
     *
     * @var int
     */
    public $Count;

    /**
     * The number of pages returned from the query.
     *
     * @var int
     */
    public $PageNumber;

    /**
     * The info used to page large result sets.
     *
     * @var string
     */
    public $PagingCookie;

    /**
     * Whether the total number of records should be returned from the query.
     *
     * @var bool
     */
    public $ReturnTotalRecordCount;

}

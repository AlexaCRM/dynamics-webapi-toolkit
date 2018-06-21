<?php

namespace AlexaCRM\WebAPI\OData;

class ListResponse {

    /**
     * List of JSON-deserialized objects containing entity record values and annotations.
     *
     * @var object[]
     */
    public $List;

    /**
     * The number of records returned.
     *
     * @var int
     */
    public $Count;

    /**
     * The info used to page large result sets.
     *
     * @var string
     */
    public $SkipToken;

}

<?php

namespace AlexaCRM\Xrm;

class EntityCollection {

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

}

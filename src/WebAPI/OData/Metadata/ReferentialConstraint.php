<?php

namespace AlexaCRM\WebAPI\OData\Metadata;

class ReferentialConstraint {

    /**
     * Referential constraint property name.
     *
     * @var string
     */
    public $property;

    /**
     * Name of the property referenced by the referencing property.
     *
     * @var string
     */
    public $referencedProperty;

}

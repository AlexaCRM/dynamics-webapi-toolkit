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
 */

namespace AlexaCRM\Xrm\Metadata;

use AlexaCRM\Xrm\ManagedProperty;

/**
 * Contains the metadata for an entity relationship.
 */
class RelationshipMetadataBase extends MetadataBase {

    /**
     * Gets a string identifying the solution version that the solution component was added in.
     *
     * @var string
     */
    public $IntroducedVersion;

    /**
     * Gets or sets whether the entity relationship is customizable.
     *
     * @var ManagedProperty
     */
    public $IsCustomizable;

    /**
     * Gets whether the relationship is a custom relationship.
     *
     * @var bool
     */
    public $IsCustomRelationship;

    /**
     * Gets whether the entity relationship is part of a managed solution.
     *
     * @var bool
     */
    public $IsManaged;

    /**
     * Gets or sets whether the entity relationship should be shown in Advanced Find.
     *
     * @var bool
     */
    public $IsValidForAdvancedFind;

    /**
     * Gets the type of relationship.
     *
     * @var RelationshipType
     */
    public $RelationshipType;

    /**
     * Gets or sets the schema name for the entity relationship.
     *
     * @var string
     */
    public $SchemaName;

    /**
     * Gets or sets the security type for the relationship.
     *
     * @var SecurityTypes
     */
    public $SecurityTypes;

}

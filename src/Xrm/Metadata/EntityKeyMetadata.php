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

use AlexaCRM\Xrm\EntityKeyIndexStatus;
use AlexaCRM\Xrm\EntityReference;
use AlexaCRM\Xrm\Label;
use AlexaCRM\Xrm\ManagedProperty;

/**
 * Represents the entity key metadata.
 */
class EntityKeyMetadata extends MetadataBase {

    /**
     * Gets or sets the asynchronous job.
     *
     * @var EntityReference
     */
    public $AsyncJob;

    /**
     * Gets or sets the display name.
     *
     * @var Label
     */
    public $DisplayName;

    /**
     * Gets or sets the entity key index status.
     *
     * @var EntityKeyIndexStatus
     */
    public $EntityKeyIndexStatus;

    /**
     * Gets or sets the entity logical name.
     *
     * @var string
     */
    public $EntityLogicalName;

    /**
     * Gets or sets the introduced version.
     *
     * @var string
     */
    public $IntroducedVersion;

    /**
     * Gets or sets a Boolean value that specifies whether the entity key metadata is customizable.
     *
     * @var ManagedProperty
     */
    public $IsCustomizable;

    /**
     * Gets or sets a Boolean value that specifies whether entity key metadata is managed or not.
     *
     * @var bool
     */
    public $IsManaged;

    /**
     * Gets or sets the key attributes.
     *
     * @var string[]
     */
    public $KeyAttributes;

    /**
     * Gets or sets the logical name.
     *
     * @var string
     */
    public $LogicalName;

    /**
     * Gets or sets the schema name.
     *
     * @var string
     */
    public $SchemaName;

}

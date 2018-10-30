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

use AlexaCRM\Xrm\Label;
use AlexaCRM\Xrm\ManagedProperty;

/**
 * Contains data that defines a set of options.
 */
abstract class OptionSetMetadataBase extends MetadataBase {

    /**
     * Gets or sets a description for the option set.
     *
     * @var Label
     */
    public $Description;

    /**
     * Gets or sets a display name for a global option set.
     *
     * @var Label
     */
    public $DisplayName;

    /**
     * Gets a string identifying the solution version that the solution component was added in.
     *
     * @var string
     */
    public $IntroducedVersion;

    /**
     * Gets or sets a property that determines whether the option set is customizable.
     *
     * @var ManagedProperty
     */
    public $IsCustomizable;

    /**
     * Gets or sets whether the option set is a custom option set.
     *
     * @var bool
     */
    public $IsCustomOptionSet;

    /**
     * Gets or sets whether the option set is a global option set.
     *
     * @var bool
     */
    public $IsGlobal;

    /**
     * Gets or sets whether the option set is part of a managed solution.
     *
     * @var bool
     */
    public $IsManaged;

    /**
     * Gets or sets the name of a global option set.
     *
     * @var string
     */
    public $Name;

    /**
     * Gets or sets the type of option set.
     *
     * @var OptionSetType
     */
    public $OptionSetType;

}

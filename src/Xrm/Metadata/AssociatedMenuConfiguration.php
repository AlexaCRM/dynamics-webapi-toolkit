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

/**
 * Defines how the associated records are displayed for an entity relationship.
 */
class AssociatedMenuConfiguration {

    /**
     * The behavior of the associated menu for an entity relationship.
     *
     * @var AssociatedMenuBehavior
     */
    public $Behavior;

    /**
     * The group for the associated menu for an entity relationship.
     *
     * @var AssociatedMenuGroup
     */
    public $Group;

    /**
     * The order for the associated menu.
     *
     * @var int
     */
    public $Order;

    /**
     * @var bool
     */
    public $IsCustomizable;

    /**
     * @var string
     */
    public $Icon;

    /**
     * @var string
     */
    public $ViewId;

    /**
     * @var bool
     */
    public $AvailableOffline;

    /**
     * @var string
     */
    public $MenuId;

    /**
     * @var string
     */
    public $QueryApi;

    /**
     * The label for the associated menu.
     *
     * @var Label
     */
    public $Label;

}

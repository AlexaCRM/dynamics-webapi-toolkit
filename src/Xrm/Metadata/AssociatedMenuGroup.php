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

use AlexaCRM\Enum\ChoiceEnum;

/**
 * Describes the group in which to display the associated menu for an entity relationship.
 *
 * @method static Details() Show the associated menu in the details group.
 * @method static Sales() Show the associated menu in the sales group.
 * @method static Service() Show the associated menu in the service group.
 * @method static Marketing() Show the associated menu in the marketing group.
 */
class AssociatedMenuGroup extends ChoiceEnum {

    /**
     * Show the associated menu in the details group.
     */
    const Details = 0;

    /**
     * Show the associated menu in the sales group.
     */
    const Sales = 1;

    /**
     * Show the associated menu in the service group.
     */
    const Service = 2;

    /**
     * Show the associated menu in the marketing group.
     */
    const Marketing = 3;

}

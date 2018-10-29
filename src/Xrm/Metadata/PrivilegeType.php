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
 * Describes the type of operation for the privilege.
 *
 * @method static None() Specifies no privilege.
 * @method static Create() The create privilege.
 * @method static Read() The read privilege.
 * @method static Write() The write privilege.
 * @method static Delete() The delete privilege.
 * @method static Assign() The assign privilege.
 * @method static Share() The share privilege.
 * @method static Append() The append privilege.
 * @method static AppendTo() The append to privilege.
 */
class PrivilegeType extends ChoiceEnum {

    /**
     * Specifies no privilege.
     */
    const None = 0;

    /**
     * The create privilege.
     */
    const Create = 1;

    /**
     * The read privilege.
     */
    const Read = 2;

    /**
     * The write privilege.
     */
    const Write = 3;

    /**
     * The delete privilege.
     */
    const Delete = 4;

    /**
     * The assign privilege.
     */
    const Assign = 5;

    /**
     * The share privilege.
     */
    const Share = 6;

    /**
     * The append privilege.
     */
    const Append = 7;

    /**
     * The append to privilege.
     */
    const AppendTo = 8;

}

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
 * Describes the requirement level for an attribute.
 *
 * @method static None() No requirements are specified.
 * @method static SystemRequired() The attribute is required to have a value.
 * @method static ApplicationRequired() The attribute is required to have a value.
 * @method static Recommended() It is recommended that the attribute has a value.
 */
class AttributeRequiredLevel extends ChoiceEnum {

    /**
     * No requirements are specified.
     */
    const None = 0;

    /**
     * The attribute is required to have a value.
     */
    const SystemRequired = 1;

    /**
     * The attribute is required to have a value.
     */
    const ApplicationRequired = 2;

    /**
     * It is recommended that the attribute has a value.
     */
    const Recommended = 3;

}

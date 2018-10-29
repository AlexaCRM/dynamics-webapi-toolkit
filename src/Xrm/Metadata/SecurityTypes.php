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

use AlexaCRM\Enum\FlaggedEnum;

/**
 * Describes the security type for the relationship.
 *
 * @method static None() No security privileges are checked during create or update operations.
 * @method static Append() The Append and AppendTo privileges are checked for create or update operations.
 * @method static ParentChild() Security for the referencing entity record is derived from the referenced entity record.
 * @method static Pointer() Security for the referencing entity record is derived from a pointer record.
 * @method static Inheritance() The referencing entity record inherits security from the referenced security record.
 */
class SecurityTypes extends FlaggedEnum {

    /**
     * No security privileges are checked during create or update operations.
     */
    const None = 0;

    /**
     * The Append and AppendTo privileges are checked for create or update operations.
     */
    const Append = 1;

    /**
     * Security for the referencing entity record is derived from the referenced entity record.
     */
    const ParentChild = 2;

    /**
     * Security for the referencing entity record is derived from a pointer record.
     */
    const Pointer = 4;

    /**
     * The referencing entity record inherits security from the referenced security record.
     */
    const Inheritance = 8;

}

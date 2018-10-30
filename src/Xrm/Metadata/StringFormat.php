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
 * Describes the formatting of a string attribute for the StringAttributeMetadata::$Format property.
 */
class StringFormat extends ChoiceEnum {

    /**
     * Specifies to display the string as an e-mail.
     */
    const Email = 0;

    /**
     * For internal use only.
     */
    const Phone = 7;

    /**
     * Specifies to display the string as a phonetic guide.
     */
    const PhoneticGuide = 5;

    /**
     * Specifies to display the string as text.
     */
    const Text = 1;

    /**
     * Specifies to display the string as a text area.
     */
    const TextArea = 2;

    /**
     * Specifies to display the string as a ticker symbol.
     */
    const TickerSymbol = 4;

    /**
     * Specifies to display the string as a URL.
     */
    const Url = 3;

    /**
     * Specifies to display the string as a version number.
     */
    const VersionNumber = 6;

}

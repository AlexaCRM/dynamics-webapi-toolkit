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

namespace AlexaCRM\Xrm;

use AlexaCRM\Xrm\Metadata\MetadataBase;

/**
 * Contains a localized label, including the label string and the language code.
 */
class LocalizedLabel extends MetadataBase {

    /**
     * Whether the label is managed.
     *
     * @var bool
     */
    public $IsManaged;

    /**
     * The localized label string.
     *
     * @var string
     */
    public $Label;

    /**
     * The language code for the label.
     *
     * @var int
     * @see https://docs.microsoft.com/en-us/previous-versions/windows/embedded/ms912047(v=winembedded.10)
     */
    public $LanguageCode;

    /**
     * LocalizedLabel constructor.
     *
     * @param string $label The localized label string.
     * @param int $languageCode The language code for the label.
     */
    public function __construct( string $label = null, int $languageCode = null ) {
        $this->Label = $label;
        $this->LanguageCode = $languageCode;
    }

}

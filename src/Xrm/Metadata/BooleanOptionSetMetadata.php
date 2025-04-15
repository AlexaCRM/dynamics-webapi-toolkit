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

/**
 * Contains the metadata for an attribute of type Boolean.
 */
class BooleanOptionSetMetadata extends OptionSetMetadataBase {

    /**
     * The option displayed when the attribute is false.
     *
     * @var OptionMetadata
     */
    public $FalseOption;

    /**
     * The option displayed when the attribute is true.
     *
     * @var OptionMetadata
     */
    public $TrueOption;

    /**
     * BooleanOptionSetMetadata constructor.
     *
     * @param OptionMetadata|null $trueOption
     * @param OptionMetadata|null $falseOption
     */
    public function __construct( ?OptionMetadata $trueOption = null, ?OptionMetadata $falseOption = null ) {
        $this->TrueOption = $trueOption;
        $this->FalseOption = $falseOption;
    }

}

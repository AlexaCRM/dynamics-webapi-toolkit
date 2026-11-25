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
 * Contains the metadata for an attribute type Money.
 */
class MoneyAttributeMetadata extends AttributeMetadata {

    /**
     * @var string
     */
    public $CalculationOf;

    /**
     * The formula definition for calculated and rollup attributes.
     *
     * @var string
     */
    public $FormulaDefinition;

    /**
     * The input method editor (IME) mode for the attribute.
     *
     * @var ImeMode
     */
    public $ImeMode;

    /**
     * Whether the attribute represents the base currency or the transaction currency.
     *
     * @var bool
     */
    public $IsBaseCurrency;

    /**
     * The maximum value for the attribute.
     *
     * @var int|float
     */
    public $MaxValue;

    /**
     * The minimum value for the attribute.
     *
     * @var int|float
     */
    public $MinValue;

    /**
     * The precision for the attribute.
     *
     * @var int
     */
    public $Precision;

    /**
     * The precision source for the attribute.
     *
     * @var int
     */
    public $PrecisionSource;

    /**
     * The bitmask value that describes the sources of data used in a calculated attribute or whether the data sources are invalid.
     *
     * @var int
     */
    public $SourceTypeMask;

    /**
     * MoneyAttributeMetadata constructor.
     *
     * @param string|null $schemaName
     */
    public function __construct( ?string $schemaName = null ) {
        parent::__construct( $schemaName );
        $this->SchemaName = $schemaName;
    }

}

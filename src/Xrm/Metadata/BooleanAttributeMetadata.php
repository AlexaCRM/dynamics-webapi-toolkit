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
 * Contains the metadata for an attribute type Boolean.
 */
class BooleanAttributeMetadata extends AttributeMetadata {

    /**
     * The default value for a Boolean option set.
     *
     * @var bool
     */
    public $DefaultValue;

    /**
     * The formula definition for calculated and rollup attributes.
     *
     * @var string
     */
    public $FormulaDefinition;

    /**
     * The options for a boolean attribute.
     *
     * @var BooleanOptionSetMetadata
     */
    public $OptionSet;

    /**
     * The bitmask value that describes the sources of data
     * used in a calculated attribute or whether the data sources are invalid.
     *
     * @var int
     * @see https://docs.microsoft.com/en-us/dotnet/api/microsoft.xrm.sdk.metadata.booleanattributemetadata.sourcetypemask
     */
    public $SourceTypeMask;

    /**
     * BooleanAttributeMetadata constructor.
     *
     * @param string|BooleanOptionSetMetadata $schemaName
     * @param BooleanOptionSetMetadata $optionSet
     */
    public function __construct( $schemaName = null, $optionSet = null ) {
        if ( $schemaName instanceof BooleanOptionSetMetadata ) {
            $this->OptionSet = $schemaName;
        } elseif ( is_string( $schemaName ) ) {
            $this->SchemaName = $schemaName;

            if ( $optionSet instanceof BooleanOptionSetMetadata ) {
                $this->OptionSet = $optionSet;
            }
        }
    }

}

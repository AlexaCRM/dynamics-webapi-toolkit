<?php
/**
 * Copyright 2018, 2021 AlexaCRM
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
 * Contains the metadata for an attribute type Image.
 */
class ImageAttributeMetadata extends AttributeMetadata {

    /**
     * Whether the image attribute can store a full-size image in addition to a thumbnail image.
     *
     * @var bool
     */
    public $CanStoreFullImage;

    /**
     * Whether the attribute is the primary image for the entity.
     *
     * @var bool
     */
    public $IsPrimaryImage;

    /**
     * The maximum height of the image.
     *
     * @var int
     */
    public $MaxHeight;

    /**
     * The maximum allowable size (in kilobytes) of the stored image.
     *
     * @var int
     */
    public $MaxSizeInKB;

    /**
     * The maximum width of the image.
     *
     * @var int
     */
    public $MaxWidth;

    /**
     * ImageAttributeMetadata constructor.
     *
     * @param string|null $schemaName
     */
    public function __construct( ?string $schemaName = null ) {
        parent::__construct( $schemaName );
        if ( $schemaName !== null ) {
            $this->SchemaName = $schemaName;
        }
    }

}

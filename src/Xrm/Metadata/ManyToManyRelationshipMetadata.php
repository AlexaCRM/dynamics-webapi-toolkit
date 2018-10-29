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
 * Contains the metadata for a many-to-many entity relationship.
 */
class ManyToManyRelationshipMetadata extends RelationshipMetadataBase {

    /**
     * Gets or sets the associated menu configuration for the first entity.
     *
     * @var AssociatedMenuConfiguration
     */
    public $Entity1AssociatedMenuConfiguration;

    /**
     * Gets or sets the attribute that defines the relationship in the first entity.
     *
     * @var string
     */
    public $Entity1IntersectAttribute;

    /**
     * Gets or sets the logical name of the first entity in the relationship.
     *
     * @var string
     */
    public $Entity1LogicalName;

    /**
     * Gets or sets the navigation property name for the first entity in the relationship.
     *
     * @var string
     */
    public $Entity1NavigationPropertyName;

    /**
     * Gets or sets the associated menu configuration for the second entity.
     *
     * @var AssociatedMenuConfiguration
     */
    public $Entity2AssociatedMenuConfiguration;

    /**
     * Gets or sets the attribute that defines the relationship in the second entity.
     *
     * @var string
     */
    public $Entity2IntersectAttribute;

    /**
     * Gets or sets the logical name of the second entity in the relationship.
     *
     * @var string
     */
    public $Entity2LogicalName;

    /**
     * Gets or sets the navigation property name for the second entity in the relationship.
     *
     * @var string
     */
    public $Entity2NavigationPropertyName;

    /**
     * Gets or sets the name of the intersect entity for the relationship.
     *
     * @var string
     */
    public $IntersectEntityName;

}

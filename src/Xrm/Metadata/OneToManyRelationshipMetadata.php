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
 * Gets the array of many-to-one relationships for the entity.
 */
class OneToManyRelationshipMetadata extends RelationshipMetadataBase {

    /**
     * Gets or sets the associated menu configuration.
     *
     * @var AssociatedMenuConfiguration
     */
    public $AssociatedMenuConfiguration;

    /**
     * Gets or sets cascading behaviors for the entity relationship.
     *
     * @var CascadeConfiguration
     */
    public $CascadeConfiguration;

    /**
     * Gets or sets whether this relationship is the designated hierarchical self-referential relationship for this entity.
     *
     * @var bool
     */
    public $IsHierarchical;

    /**
     * Get or set the name of primary attribute for the referenced entity.
     *
     * @var string
     */
    public $ReferencedAttribute;

    /**
     * Get or set the name of the referenced entity.
     *
     * @var string
     */
    public $ReferencedEntity;

    /**
     * Gets or sets the name of the collection-valued navigation property used by this relationship.
     *
     * @var string
     */
    public $ReferencedEntityNavigationPropertyName;

    /**
     * Get or set the name of the referencing attribute.
     *
     * @var string
     */
    public $ReferencingAttribute;

    /**
     * Gets or sets the name of the referencing entity.
     *
     * @var string
     */
    public $ReferencingEntity;

    /**
     * Gets or sets the name of the single-valued navigation property used by this relationship.
     *
     * @var string
     */
    public $ReferencingEntityNavigationPropertyName;

}

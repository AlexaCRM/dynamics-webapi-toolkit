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

use AlexaCRM\Xrm\Label;
use AlexaCRM\Xrm\ManagedProperty;

/**
 * Contains all the metadata for an entity attribute.
 */
class AttributeMetadata extends MetadataBase {

    /**
     * Gets the name of the attribute that this attribute extends.
     *
     * @var string
     */
    public $AttributeOf;

    /**
     * Gets the type for the attribute.
     *
     * @var AttributeTypeCode
     */
    public $AttributeType;

    /**
     * Gets the name of the type for the attribute.
     *
     * @var AttributeTypeDisplayName
     */
    public $AttributeTypeName;

    /**
     * Gets whether field-level security can be applied to prevent a user from adding data to this attribute.
     *
     * @var bool
     */
    public $CanBeSecuredForCreate;

    /**
     * Gets whether field-level security can be applied to prevent a user from viewing data from this attribute.
     *
     * @var bool
     */
    public $CanBeSecuredForRead;

    /**
     * Gets whether field-level security can be applied to prevent a user from updating data for this attribute.
     *
     * @var bool
     */
    public $CanBeSecuredForUpdate;

    /**
     * Gets or sets the property that determines whether any settings not controlled by managed properties can be changed.
     *
     * @var ManagedProperty
     */
    public $CanModifyAdditionalSettings;

    /**
     * Gets an organization-specific ID for the attribute used for auditing.
     *
     * @var int
     */
    public $ColumnNumber;

    /**
     * Gets the Microsoft Dynamics 365 version that the attribute was deprecated in.
     *
     * @var string
     */
    public $DeprecatedVersion;

    /**
     * Gets or sets the description of the attribute.
     *
     * @var Label
     */
    public $Description;

    /**
     * Gets or sets the display name for the attribute.
     *
     * @var Label
     */
    public $DisplayName;

    /**
     * Gets the logical name of the entity that contains the attribute.
     *
     * @var string
     */
    public $EntityLogicalName;

    /**
     * Gets a string identifying the solution version that the solution component was added in.
     *
     * @var string
     */
    public $IntroducedVersion;

    /**
     * Gets or sets the property that determines whether the attribute is enabled for auditing.
     *
     * @var ManagedProperty
     */
    public $IsAuditEnabled;

    /**
     * Gets whether the attribute is a custom attribute.
     *
     * @var bool
     */
    public $IsCustomAttribute;

    /**
     * Gets or sets the property that determines whether the attribute allows customization.
     *
     * @var ManagedProperty
     */
    public $IsCustomizable;

    /**
     * This API supports the product infrastructure and is not intended to be used directly from your code.
     *
     * @var bool
     */
    public $IsFilterable;

    /**
     * @var ManagedProperty
     */
    public $IsGlobalFilterEnabled;

    /**
     * Gets whether the attribute is a logical attribute.
     *
     * @var bool
     */
    public $IsLogical;

    /**
     * Gets whether the attribute is part of a managed solution.
     *
     * @var bool
     */
    public $IsManaged;

    /**
     * Gets whether the attribute represents the unique identifier for the record.
     *
     * @var bool
     */
    public $IsPrimaryId;

    /**
     * Gets or sets whether the attribute represents the primary attribute for the entity.
     *
     * @var bool
     */
    public $IsPrimaryName;

    /**
     * Gets or sets the property that determines whether the attribute display name can be changed.
     *
     * @var ManagedProperty
     */
    public $IsRenameable;

    /**
     * This API supports the product infrastructure and is not intended to be used directly from your code.
     *
     * @var bool
     */
    public $IsRetrievable;

    /**
     * This API supports the product infrastructure and is not intended to be used directly from your code.
     *
     * @var bool
     */
    public $IsSearchable;

    /**
     * Gets or sets whether the attribute is secured for field-level security.
     *
     * @var bool
     */
    public $IsSecured;

    /**
     * Gets or sets the property that determines whether the attribute appears in Advanced Find.
     *
     * @var ManagedProperty
     */
    public $IsValidForAdvancedFind;

    /**
     * Gets whether the value can be set when a record is created.
     *
     * @var bool
     */
    public $IsValidForCreate;

    /**
     * Gets whether the value can be retrieved.
     *
     * @var bool
     */
    public $IsValidForRead;

    /**
     * Gets whether the value can be updated.
     *
     * @var bool
     */
    public $IsValidForUpdate;

    /**
     * Gets or sets an attribute that is linked between appointments and recurring appointments.
     *
     * @var string
     */
    public $LinkedAttributeId;

    /**
     * Gets or sets the logical name for the attribute.
     *
     * @var string
     */
    public $LogicalName;

    /**
     * Gets or sets the property that determines the data entry requirement level enforced for the attribute.
     *
     * @var ManagedProperty
     * @see AttributeRequiredLevel
     */
    public $RequiredLevel;

    /**
     * Gets or sets the schema name for the attribute.
     *
     * @var string
     */
    public $SchemaName;

    /**
     * Gets or sets the value that indicates the source type for a calculated or rollup attribute.
     *
     * @var int
     */
    public $SourceType;

    /**
     * AttributeMetadata constructor.
     *
     * @param AttributeTypeCode|null $attributeType
     * @param string|null $schemaName
     */
    public function __construct( ?AttributeTypeCode $attributeType = null, ?string $schemaName = null ) {
        if ( $attributeType !== null ) {
            $this->AttributeType = $attributeType;
        }

        if ( $schemaName !== null ) {
            $this->SchemaName = $schemaName;
        }
    }

}

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
 * Contains the metadata for an entity.
 */
class EntityMetadata extends MetadataBase {

    /**
     * Gets or sets whether a custom activity should appear in the activity menus in the Web application.
     *
     * @var int
     */
    public $ActivityTypeMask;

    /**
     * Gets the array of attribute metadata for the entity.
     *
     * @var AttributeMetadata[]
     */
    public $Attributes = [];

    /**
     * Gets or sets whether the entity is enabled for auto created access teams.
     *
     * @var bool
     */
    public $AutoCreateAccessTeams;

    /**
     * Gets or sets whether to automatically move records to the owner’s default queue when a record of this type is created or assigned.
     *
     * @var bool
     */
    public $AutoRouteToOwnerQueue;

    /**
     * @var ManagedProperty
     */
    public $CanBeInCustomEntityAssociation;

    /**
     * Gets the property that determines whether the entity can be in a Many-to-Many entity relationship.
     *
     * @var ManagedProperty
     */
    public $CanBeInManyToMany;

    /**
     * Gets the property that determines whether the entity can be the referenced entity in a One-to-Many entity relationship.
     *
     * @var ManagedProperty
     */
    public $CanBePrimaryEntityInRelationship;

    /**
     * Gets the property that determines whether the entity can be the referencing entity in a One-to-Many entity relationship.
     *
     * @var ManagedProperty
     */
    public $CanBeRelatedEntityInRelationship;

    /**
     * Gets or sets whether the hierarchical state of entity relationships included in your managed solutions can be changed.
     *
     * @var ManagedProperty
     */
    public $CanChangeHierarchicalRelationship;

    /**
     * @var ManagedProperty
     */
    public $CanChangeTrackingBeEnabled;

    /**
     * Gets or sets the property that determines whether additional attributes can be added to the entity.
     *
     * @var ManagedProperty
     */
    public $CanCreateAttributes;

    /**
     * Gets or sets the property that determines whether new charts can be created for the entity.
     *
     * @var ManagedProperty
     */
    public $CanCreateCharts;

    /**
     * Gets or sets the property that determines whether new forms can be created for the entity.
     *
     * @var ManagedProperty
     */
    public $CanCreateForms;

    /**
     * Gets or sets the property that determines whether new views can be created for the entity.
     *
     * @var ManagedProperty
     */
    public $CanCreateViews;

    /**
     * Gets or sets whether this entity can be enabled for relevance search when customizing a managed solution.
     *
     * @var ManagedProperty
     */
    public $CanEnableSyncToExternalSearchIndex;

    /**
     * Gets or sets the property that determines whether any other entity properties not represented by a managed property can be changed.
     *
     * @var ManagedProperty
     */
    public $CanModifyAdditionalSettings;

    /**
     * Gets whether the entity can trigger a workflow process.
     *
     * @var bool
     */
    public $CanTriggerWorkflow;

    /**
     * Gets or sets a Boolean value that specifies whether change tracking is enabled for an entity or not.
     *
     * @var bool
     */
    public $ChangeTrackingEnabled;

    /**
     * Gets or sets the collection schema name of the entity.
     *
     * @var string
     */
    public $CollectionSchemaName;

    /**
     * Gets or sets the label containing the description for the entity.
     *
     * @var Label
     */
    public $Description;

    /**
     * Gets or sets the label containing the plural display name for the entity.
     *
     * @var Label
     */
    public $DisplayCollectionName;

    /**
     * Gets or sets the label containing the display name for the entity.
     *
     * @var Label
     */
    public $DisplayName;

    /**
     * Gets whether the entity will enforce custom state transitions.
     *
     * @var bool
     */
    public $EnforceStateTransitions;

    /**
     * Gets or sets the hexadecimal code to represent the color to be used for this entity in the application.
     *
     * @var string
     */
    public $EntityColor;

    /**
     * Gets or sets the URL of the resource to display help content for this entity
     *
     * @var string
     */
    public $EntityHelpUrl;

    /**
     * Gets or sets whether the entity supports custom help content.
     *
     * @var bool
     */
    public $EntityHelpUrlEnabled;

    /**
     * Gets or sets the entity set name.
     *
     * @var string
     */
    public $EntitySetName;

    /**
     * Gets or sets the name of the image web resource for the large icon for the entity.
     *
     * @var string
     */
    public $IconLargeName;

    /**
     * Gets or sets the name of the image web resource for the medium icon for the entity.
     *
     * @var string
     */
    public $IconMediumName;

    /**
     * Gets or sets the name of the image web resource for the small icon for the entity.
     *
     * @var string
     */
    public $IconSmallName;

    /**
     * Gets a string identifying the solution version that the solution component was added in.
     *
     * @var string
     */
    public $IntroducedVersion;

    /**
     * Gets or sets whether the entity is an activity.
     *
     * @var bool
     */
    public $IsActivity;

    /**
     * Gets or sets whether the email messages can be sent to an email address stored in a record of this type.
     *
     * @var bool
     */
    public $IsActivityParty;

    /**
     * Gets whether the entity uses the updated user interface.
     *
     * @var bool
     */
    public $IsAIRUpdated;

    /**
     * Gets or sets the property that determines whether auditing has been enabled for the entity.
     *
     * @var ManagedProperty
     */
    public $IsAuditEnabled;

    /**
     * Gets or sets whether the entity is available offline.
     *
     * @var bool
     */
    public $IsAvailableOffline;

    /**
     * Gets whether the entity is enabled for business process flows.
     *
     * @var bool
     */
    public $IsBusinessProcessEnabled;

    /**
     * Gets whether the entity is a child entity.
     *
     * @var bool
     */
    public $IsChildEntity;

    /**
     * Gets or sets the property that determines whether connections are enabled for this entity.
     *
     * @var ManagedProperty
     */
    public $IsConnectionsEnabled;

    /**
     * Gets whether the entity is a custom entity.
     *
     * @var bool
     */
    public $IsCustomEntity;

    /**
     * Gets or sets the property that determines whether the entity is customizable.
     *
     * @var ManagedProperty
     */
    public $IsCustomizable;

    /**
     * Gets or sets the property that determines whether document management is enabled.
     *
     * @var bool
     */
    public $IsDocumentManagementEnabled;

    /**
     * This API supports the product infrastructure and is not intended to be used directly from your code. Gets or sets whether the entity is enabled for document recommendations.
     *
     * @var bool
     */
    public $IsDocumentRecommendationsEnabled;

    /**
     * Gets or sets the property that determines whether duplicate detection is enabled.
     *
     * @var ManagedProperty
     */
    public $IsDuplicateDetectionEnabled;

    /**
     * Gets whether charts are enabled.
     *
     * @var bool
     */
    public $IsEnabledForCharts;

    /**
     * This API supports the product infrastructure and is not intended to be used directly from your code.
     *
     * @var bool
     */
    public $IsEnabledForExternalChannels;

    /**
     * Gets whether the entity can be imported using the Import Wizard.
     *
     * @var bool
     */
    public $IsImportable;

    /**
     * Gets or sets whether the entity is enabled for interactive experience.
     *
     * @var bool
     */
    public $IsInteractionCentricEnabled;

    /**
     * Gets whether the entity is an intersection table for two other entities.
     *
     * @var bool
     */
    public $IsIntersect;

    /**
     * Gets or sets whether Parature knowledge management integration is enabled for the entity.
     *
     * @var bool
     */
    public $IsKnowledgeManagementEnabled;

    /**
     * Gets or sets the property that determines whether mail merge is enabled for this entity.
     *
     * @var ManagedProperty
     */
    public $IsMailMergeEnabled;

    /**
     * Gets whether the entity is part of a managed solution.
     *
     * @var bool
     */
    public $IsManaged;

    /**
     * Gets or sets the property that determines whether entity mapping is available for the entity.
     *
     * @var ManagedProperty
     */
    public $IsMappable;

    /**
     * @var ManagedProperty
     */
    public $IsOfflineInMobileClient;

    /**
     * Gets or sets whether OneNote integration is enabled for the.
     *
     * @var bool
     */
    public $IsOneNoteIntegrationEnabled;

    /**
     * Gets whether optimistic concurrency is enabled for the entity.
     *
     * @var bool
     */
    public $IsOptimisticConcurrencyEnabled;

    /**
     * Gets whether the entity is public or private.
     *
     * @var bool
     */
    public $IsPrivate;

    /**
     * Gets or sets the value indicating if the entity is enabled for quick create forms.
     *
     * @var bool
     */
    public $IsQuickCreateEnabled;

    /**
     * Gets or sets the property that determines whether Microsoft Dynamics 365 for tablets users can update data for this entity.
     *
     * @var ManagedProperty
     */
    public $IsReadOnlyInMobileClient;

    /**
     * Gets or sets the property that determines whether the entity DisplayName and DisplayCollectionName can be changed by editing the entity in the application.
     *
     * @var ManagedProperty
     */
    public $IsRenameable;

    /**
     * Gets or sets the value indicating if the entity is enabled for service level agreements (SLAs).
     *
     * @var bool
     */
    public $IsSLAEnabled;

    /**
     * Gets whether the entity supports setting custom state transitions.
     *
     * @var bool
     */
    public $IsStateModelAware;

    /**
     * Gets or sets whether the entity is will be shown in Advanced Find.
     *
     * @var bool
     */
    public $IsValidForAdvancedFind;

    /**
     * Gets or sets the property that determines whether the entity is enabled for queues.
     *
     * @var ManagedProperty
     */
    public $IsValidForQueue;

    /**
     * Gets or sets the property that determines whether Microsoft Dynamics 365 for phones users can see data for this entity.
     *
     * @var ManagedProperty
     */
    public $IsVisibleInMobile;

    /**
     * Gets or sets the property that determines whether Microsoft Dynamics 365 for tablets users can see data for this entity.
     *
     * @var ManagedProperty
     */
    public $IsVisibleInMobileClient;

    /**
     * Gets an array of keys for an entity.
     *
     * @var EntityKeyMetadata[]
     */
    public $Keys;

    /**
     * Gets the logical collection name.
     *
     * @var string
     */
    public $LogicalCollectionName;

    /**
     * Gets or sets the logical name for the entity.
     *
     * @var string
     */
    public $LogicalName;

    /**
     * Gets the array of many-to-many relationships for the entity.
     *
     * @var ManyToManyRelationshipMetadata[]
     */
    public $ManyToManyRelationships;

    /**
     * Gets the array of many-to-one relationships for the entity.
     *
     * @var OneToManyRelationshipMetadata[]
     */
    public $ManyToOneRelationships;

    /**
     * Gets the entity type code.
     *
     * @var int
     */
    public $ObjectTypeCode;

    /**
     * Gets the array of one-to-many relationships for the entity.
     *
     * @var OneToManyRelationshipMetadata[]
     */
    public $OneToManyRelationships;

    /**
     * Gets or sets the ownership type for the entity.
     *
     * @var OwnershipTypes
     */
    public $OwnershipType;

    /**
     * Gets the name of the attribute that is the primary id for the entity.
     *
     * @var string
     */
    public $PrimaryIdAttribute;

    /**
     * Gets the name of the primary image attribute for an entity.
     *
     * @var string
     */
    public $PrimaryImageAttribute;

    /**
     * Gets the name of the primary attribute for an entity.
     *
     * @var string
     */
    public $PrimaryNameAttribute;

    /**
     * Gets the privilege metadata for the entity.
     *
     * @var SecurityPrivilegeMetadata[]
     */
    public $Privileges;

    /**
     * Gets the name of the entity that is recurring.
     *
     * @var string
     */
    public $RecurrenceBaseEntityLogicalName;

    /**
     * Gets the name of the report view for the entity.
     *
     * @var string
     */
    public $ReportViewName;

    /**
     * Gets or sets the schema name for the entity.
     *
     * @var string
     */
    public $SchemaName;

    /**
     * Gets or sets whether this entity is searchable in relevance search.
     *
     * @var bool
     */
    public $SyncToExternalSearchIndex;

}

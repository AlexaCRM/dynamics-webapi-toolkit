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

/*
 * Organization metadata serialization class map.
 */

use AlexaCRM\StrongSerializer\Reference;
use AlexaCRM\Xrm\Metadata\AttributeTypeCode;

return [
    'AlexaCRM\Xrm\Label' => [
        'LocalizedLabels' => ( new Reference( 'AlexaCRM\Xrm\LocalizedLabel' ) )->toMap( 'LanguageCode' ),
        'UserLocalizedLabel' => new Reference( 'AlexaCRM\Xrm\LocalizedLabel' ),
    ],
    'AlexaCRM\Xrm\Metadata\EntityMetadata' => [
        'Attributes' => ( new Reference( function( $data ) {
            if ( !is_object( $data ) || !isset( $data->AttributeType ) ) {
                return 'AlexaCRM\Xrm\Metadata\AttributeMetadata';
            }

            if ( isset( $data->{'@odata.type'} ) ) {
                $odataType = str_replace( '#Microsoft.Dynamics.CRM.', '', $data->{'@odata.type' } );
                if ( class_exists( "AlexaCRM\\Xrm\\Metadata\\{$odataType}" ) ) {
                    return "AlexaCRM\\Xrm\\Metadata\\{$odataType}";
                }
            }

            $attrType = $data->AttributeType;

            /**
             * @var AttributeTypeCode $enum
             */
            $enum = AttributeTypeCode::$attrType();

            switch ( true ) {
                case $enum->is( AttributeTypeCode::Boolean ):
                    return 'AlexaCRM\Xrm\Metadata\BooleanAttributeMetadata';
                case $enum->is( AttributeTypeCode::DateTime ):
                    return 'AlexaCRM\Xrm\Metadata\DateTimeAttributeMetadata';
                case $enum->is( AttributeTypeCode::Decimal ):
                    return 'AlexaCRM\Xrm\Metadata\DecimalAttributeMetadata';
                case $enum->is( AttributeTypeCode::Double ):
                    return 'AlexaCRM\Xrm\Metadata\DoubleAttributeMetadata';
                case $enum->is( AttributeTypeCode::Integer ):
                    return 'AlexaCRM\Xrm\Metadata\IntegerAttributeMetadata';
                case $enum->is( AttributeTypeCode::Memo ):
                    return 'AlexaCRM\Xrm\Metadata\MemoAttributeMetadata';
                case $enum->is( AttributeTypeCode::Money ):
                    return 'AlexaCRM\Xrm\Metadata\MoneyAttributeMetadata';
                case $enum->is( AttributeTypeCode::Picklist ):
                    return 'AlexaCRM\Xrm\Metadata\PicklistAttributeMetadata';
                case $enum->is( AttributeTypeCode::State ):
                    return 'AlexaCRM\Xrm\Metadata\StateAttributeMetadata';
                case $enum->is( AttributeTypeCode::Status ):
                    return 'AlexaCRM\Xrm\Metadata\StatusAttributeMetadata';
                case $enum->is( AttributeTypeCode::String ):
                    return 'AlexaCRM\Xrm\Metadata\StringAttributeMetadata';
                case $enum->is( AttributeTypeCode::Uniqueidentifier ):
                    return 'AlexaCRM\Xrm\Metadata\UniqueIdentifierAttributeMetadata';
                case $enum->is( AttributeTypeCode::Customer ):
                case $enum->is( AttributeTypeCode::Lookup ):
                case $enum->is( AttributeTypeCode::Owner ):
                case $enum->is( AttributeTypeCode::PartyList ):
                    return 'AlexaCRM\Xrm\Metadata\LookupAttributeMetadata';
                case $enum->is( AttributeTypeCode::BigInt ):
                    return 'AlexaCRM\Xrm\Metadata\BigIntAttributeMetadata';
                case $enum->is( AttributeTypeCode::ManagedProperty ):
                    return 'AlexaCRM\Xrm\Metadata\ManagedPropertyAttributeMetadata';
                case $enum->is( AttributeTypeCode::EntityName ):
                    return 'AlexaCRM\Xrm\Metadata\EntityNameAttributeMetadata';
                default:
                    return 'AlexaCRM\Xrm\Metadata\AttributeMetadata';
            }
        } ) )->toMap( 'LogicalName' ),
        'CanBeInCustomEntityAssociation' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanBeInManyToMany' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanBePrimaryEntityInRelationship' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanBeRelatedEntityInRelationship' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanChangeHierarchicalRelationship' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanChangeTrackingBeEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanCreateAttributes' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanCreateCharts' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanCreateForms' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanCreateViews' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanEnableSyncToExternalSearchIndex' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'CanModifyAdditionalSettings' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'Description' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'DisplayCollectionName' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'DisplayName' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'IsAuditEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsConnectionsEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsCustomizable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsDuplicateDetectionEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsMailMergeEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsMappable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsOfflineInMobileClient' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsReadOnlyInMobileClient' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsRenameable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsValidForQueue' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsVisibleInMobile' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsVisibleInMobileClient' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'Keys' => ( new Reference( 'AlexaCRM\Xrm\Metadata\EntityKeyMetadata' ) )->toMap( 'LogicalName' ),
        'ManyToManyRelationships' => ( new Reference( 'AlexaCRM\Xrm\Metadata\ManyToManyRelationshipMetadata' ) )->toMap( 'SchemaName' ),
        'ManyToOneRelationships' => ( new Reference( 'AlexaCRM\Xrm\Metadata\OneToManyRelationshipMetadata' ) )->toMap( 'SchemaName' ),
        'OneToManyRelationships' => ( new Reference( 'AlexaCRM\Xrm\Metadata\OneToManyRelationshipMetadata' ) )->toMap( 'SchemaName' ),
        'OwnershipType' => new Reference( 'AlexaCRM\Xrm\Metadata\OwnershipTypes' ),
        'Privileges' => ( new Reference( 'AlexaCRM\Xrm\Metadata\SecurityPrivilegeMetadata' ) )->toMap( 'Name' ),
    ],
    'AlexaCRM\Xrm\Metadata\AssociatedMenuConfiguration' => [
        'Behavior' => new Reference( 'AlexaCRM\Xrm\Metadata\AssociatedMenuBehavior' ),
        'Group' => new Reference( 'AlexaCRM\Xrm\Metadata\AssociatedMenuGroup' ),
        'Label' => new Reference( 'AlexaCRM\Xrm\Label' ),
    ],
    'AlexaCRM\Xrm\Metadata\AttributeMetadata' => [
        'AttributeType' => new Reference( 'AlexaCRM\Xrm\Metadata\AttributeTypeCode' ),
        'AttributeTypeName' => new Reference( 'AlexaCRM\Xrm\Metadata\AttributeTypeDisplayName' ),
        'CanModifyAdditionalSettings' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'Description' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'DisplayName' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'IsAuditEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsCustomizable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsGlobalFilterEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsRenameable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsSortableEnabled' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'IsValidForAdvancedFind' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'RequiredLevel' => ( new Reference( 'AlexaCRM\Xrm\ManagedProperty' ) )->addFieldCast( 'Value', new Reference( 'AlexaCRM\Xrm\Metadata\AttributeRequiredLevel' ) ),
    ],
    'AlexaCRM\Xrm\Metadata\BooleanAttributeMetadata' => [
        'OptionSet' => new Reference( 'AlexaCRM\Xrm\Metadata\BooleanOptionSetMetadata' ),
    ],
    'AlexaCRM\Xrm\Metadata\BooleanOptionSetMetadata' => [
        'FalseOption' => new Reference( 'AlexaCRM\Xrm\Metadata\OptionMetadata' ),
        'TrueOption' => new Reference( 'AlexaCRM\Xrm\Metadata\OptionMetadata' ),
    ],
    'AlexaCRM\Xrm\Metadata\CascadeConfiguration' => [
        'Assign' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
        'Delete' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
        'Merge' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
        'Reparent' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
        'Share' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
        'Unshare' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
        'RollupView' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeType' ),
    ],
    'AlexaCRM\Xrm\Metadata\DateTimeAttributeMetadata' => [
        'CanChangeDateTimeBehavior' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'DateTimeBehavior' => new Reference( 'AlexaCRM\Xrm\Metadata\DateTimeBehavior' ),
        'Format' => new Reference( 'AlexaCRM\Xrm\Metadata\DateTimeFormat' ),
        'ImeMode' => new Reference( 'AlexaCRM\Xrm\Metadata\ImeMode' ),
    ],
    'AlexaCRM\Xrm\Metadata\DecimalAttributeMetadata' => [
        'ImeMode' => new Reference( 'AlexaCRM\Xrm\Metadata\ImeMode' ),
    ],
    'AlexaCRM\Xrm\Metadata\DoubleAttributeMetadata' => [
        'ImeMode' => new Reference( 'AlexaCRM\Xrm\Metadata\ImeMode' ),
    ],
    'AlexaCRM\Xrm\Metadata\EntityKeyMetadata' => [
        'DisplayName' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'EntityKeyIndexStatus' => new Reference( 'AlexaCRM\Xrm\EntityKeyIndexStatus' ),
        'IsCustomizable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
    ],
    'AlexaCRM\Xrm\Metadata\EnumAttributeMetadata' => [
        'OptionSet' => new Reference( 'AlexaCRM\Xrm\Metadata\OptionSetMetadata' ),
    ],
    'AlexaCRM\Xrm\Metadata\IntegerAttributeMetadata' => [
        'Format' => new Reference( 'AlexaCRM\Xrm\Metadata\IntegerFormat' ),
    ],
    'AlexaCRM\Xrm\Metadata\LookupAttributeMetadata' => [
        'Format' => new Reference( 'AlexaCRM\Xrm\Metadata\LookupFormat' ),
    ],
    'AlexaCRM\Xrm\Metadata\ManyToManyRelationshipMetadata' => [
        'Entity1AssociatedMenuConfiguration' => new Reference( 'AlexaCRM\Xrm\Metadata\AssociatedMenuConfiguration' ),
        'Entity2AssociatedMenuConfiguration' => new Reference( 'AlexaCRM\Xrm\Metadata\AssociatedMenuConfiguration' ),
    ],
    'AlexaCRM\Xrm\Metadata\MemoAttributeMetadata' => [
        'Format' => new Reference( 'AlexaCRM\Xrm\Metadata\StringFormat' ),
        'ImeMode' => new Reference( 'AlexaCRM\Xrm\Metadata\ImeMode' ),
    ],
    'AlexaCRM\Xrm\Metadata\MoneyAttributeMetadata' => [
        'ImeMode' => new Reference( 'AlexaCRM\Xrm\Metadata\ImeMode' ),
    ],
    'AlexaCRM\Xrm\Metadata\OneToManyRelationshipMetadata' => [
        'AssociatedMenuConfiguration' => new Reference( 'AlexaCRM\Xrm\Metadata\AssociatedMenuConfiguration' ),
        'CascadeConfiguration' => new Reference( 'AlexaCRM\Xrm\Metadata\CascadeConfiguration' ),
    ],
    'AlexaCRM\Xrm\Metadata\OptionMetadata' => [
        'Description' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'Label' => new Reference( 'AlexaCRM\Xrm\Label' ),
    ],
    'AlexaCRM\Xrm\Metadata\OptionSetMetadata' => [
        'Options' => ( new Reference( 'AlexaCRM\Xrm\Metadata\OptionMetadata' ) )->toMap( 'Value' ),
    ],
    'AlexaCRM\Xrm\Metadata\OptionSetMetadataBase' => [
        'Description' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'DisplayName' => new Reference( 'AlexaCRM\Xrm\Label' ),
        'IsCustomizable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'OptionSetType' => new Reference( 'AlexaCRM\Xrm\Metadata\OptionSetType' ),
    ],
    'AlexaCRM\Xrm\Metadata\RelationshipMetadataBase' => [
        'IsCustomizable' => new Reference( 'AlexaCRM\Xrm\ManagedProperty' ),
        'RelationshipType' => new Reference( 'AlexaCRM\Xrm\Metadata\RelationshipType' ),
        'SecurityTypes' => new Reference( 'AlexaCRM\Xrm\Metadata\SecurityTypes' ),
    ],
    'AlexaCRM\Xrm\Metadata\SecurityPrivilegeMetadata' => [
        'PrivilegeType' => new Reference( 'AlexaCRM\Xrm\Metadata\PrivilegeType' ),
    ],
    'AlexaCRM\Xrm\Metadata\StringAttributeMetadata' => [
        'Format' => new Reference( 'AlexaCRM\Xrm\Metadata\StringFormat' ),
        'FormatName' => new Reference( 'AlexaCRM\Xrm\Metadata\StringFormatName' ),
    ],
];

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
 * Attribute names used by AttributeMetadata::$AttributeTypeName
 */
class AttributeTypeDisplayName extends ConstantsBase {

    /**
     * An attribute type that stores a Long value.
     */
    const BigIntType = 'BigIntType';

    /**
     * An attribute type that stores a Boolean value.
     */
    const BooleanType = 'BooleanType';

    /**
     * An attribute type that stores an EntityCollection or CalendarRules[] value
     */
    const CalendarRulesType = 'CalendarRulesType';

    /**
     * An attribute type that stores an EntityReference to either an Account or Contact.
     */
    const CustomerType = 'CustomerType';

    /**
     * An attribute type that stores a System.DateTime value.
     */
    const DateTimeType = 'DateTimeType';

    /**
     * An attribute type that stores a Decimal value.
     */
    const DecimalType = 'DecimalType';

    /**
     * An attribute type that stores a Double value.
     */
    const DoubleType = 'DoubleType';

    /**
     * An attribute type that stores a reference to a logical entity name value.
     */
    const EntityNameType = 'EntityNameType';

    /**
     * An attribute type that enables access to a specific image for an entity instance.
     */
    const ImageType = 'ImageType';

    /**
     * An attribute type that stores an Integer value.
     */
    const IntegerType = 'IntegerType';

    /**
     * An attribute type that stores an EntityReference value.
     */
    const LookupType = 'LookupType';

    /**
     * An attribute type that stores a BooleanManagedProperty value.
     */
    const ManagedPropertyType = 'ManagedPropertyType';

    /**
     * An attribute type that stores a String value formatted for multiple lines of text.
     */
    const MemoType = 'MemoType';

    /**
     * An attribute type that stores a Money value.
     */
    const MoneyType = 'MoneyType';

    /**
     * An attribute type that stores an EntityReference value to a Team or SystemUser record.
     */
    const OwnerType = 'OwnerType';

    /**
     * An attribute type that stores an EntityCollection or ActivityParty[] value.
     */
    const PartyListType = 'PartyListType';

    /**
     * An attribute type that stores an OptionSetValue.
     */
    const PicklistType = 'PicklistType';

    /**
     * An attribute type that stores an OptionSetValue representing the valid statecode values for an entity.
     */
    const StateType = 'StateType';

    /**
     * An attribute type that stores an OptionSetValue representing the valid statuscode values for an entity.
     */
    const StatusType = 'StatusType';

    /**
     * An attribute type that stores a String value.
     */
    const StringType = 'StringType';

    /**
     * An attribute type that stores a System.Guid value.
     */
    const UniqueidentifierType = 'UniqueidentifierType';

    /**
     * An attribute type that represents a value not included within records.
     */
    const VirtualType = 'VirtualType';

}

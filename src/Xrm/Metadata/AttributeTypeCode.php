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

use AlexaCRM\Enum\ChoiceEnum;

/**
 * Describes the type of an attribute.
 *
 * @method static Boolean() A Boolean attribute.
 * @method static Customer() An attribute that represents a customer.
 * @method static DateTime() A date/time attribute.
 * @method static Decimal() A decimal attribute.
 * @method static Double() A double attribute.
 * @method static Integer() An integer attribute.
 * @method static Lookup() A lookup attribute.
 * @method static Memo() A memo attribute.
 * @method static Money() A money attribute.
 * @method static Owner() An owner attribute.
 * @method static PartyList() A partylist attribute.
 * @method static Picklist() A picklist attribute.
 * @method static State() A state attribute.
 * @method static Status() A status attribute.
 * @method static String() A string attribute.
 * @method static Uniqueidentifier() An attribute that is an ID.
 * @method static CalendarRules() An attribute that contains calendar rules.
 * @method static Virtual() An attribute that is created by the system at run time.
 * @method static BigInt() A big integer attribute.
 * @method static ManagedProperty() A managed property attribute.
 * @method static EntityName() An entity name attribute.
 */
class AttributeTypeCode extends ChoiceEnum {

    /**
     * A Boolean attribute.
     */
    const Boolean = 0;

    /**
     * An attribute that represents a customer.
     */
    const Customer = 1;

    /**
     * A date/time attribute.
     */
    const DateTime = 2;

    /**
     * A decimal attribute.
     */
    const Decimal = 3;

    /**
     * A double attribute.
     */
    const Double = 4;

    /**
     * An integer attribute.
     */
    const Integer = 5;

    /**
     * A lookup attribute.
     */
    const Lookup = 6;

    /**
     * A memo attribute.
     */
    const Memo = 7;

    /**
     * A money attribute.
     */
    const Money = 8;

    /**
     * An owner attribute.
     */
    const Owner = 9;

    /**
     * A partylist attribute.
     */
    const PartyList = 10;

    /**
     * A picklist attribute.
     */
    const Picklist = 11;

    /**
     * A state attribute.
     */
    const State = 12;

    /**
     * A status attribute.
     */
    const Status = 13;

    /**
     * A string attribute.
     */
    const String = 14;

    /**
     * An attribute that is an ID.
     */
    const Uniqueidentifier = 15;

    /**
     * An attribute that contains calendar rules.
     */
    const CalendarRules = 16;

    /**
     * An attribute that is created by the system at run time.
     */
    const Virtual = 17;

    /**
     * A big integer attribute.
     */
    const BigInt = 18;

    /**
     * A managed property attribute.
     */
    const ManagedProperty = 19;

    /**
     * An entity name attribute.
     */
    const EntityName = 20;

}

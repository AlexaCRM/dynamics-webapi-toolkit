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
 * Describes the type of behavior for a specific action applied to the referenced entity
 * in a one-to-many entity relationship.
 *
 * @method static NoCascade() Do nothing.
 * @method static Cascade() Perform the action on all referencing entity records associated with the referenced entity record.
 * @method static Active() Perform the action on all active referencing entity records associated with the referenced entity record.
 * @method static UserOwned() Perform the action on all referencing entity records owned by the same user as the referenced entity record.
 * @method static RemoveLink() Remove the value of the referencing attribute for all referencing entity records associated with the referenced entity record.
 * @method static Restrict() Prevent the Referenced entity record from being deleted when referencing entities exist.
 */
class CascadeType extends ChoiceEnum {

    /**
     * Do nothing.
     */
    const NoCascade = 0;

    /**
     * Perform the action on all referencing entity records associated with the referenced entity record.
     */
    const Cascade = 1;

    /**
     * Perform the action on all active referencing entity records associated with the referenced entity record.
     */
    const Active = 2;

    /**
     * Perform the action on all referencing entity records owned by the same user as the referenced entity record.
     */
    const UserOwned = 3;

    /**
     * Remove the value of the referencing attribute for all referencing entity records associated with the referenced entity record.
     */
    const RemoveLink = 4;

    /**
     * Prevent the Referenced entity record from being deleted when referencing entities exist.
     */
    const Restrict = 5;

}

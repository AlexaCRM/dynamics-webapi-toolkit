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

use AlexaCRM\Enum\FlaggedEnum;

/**
 * Specifies the type of ownership for an entity.
 *
 * @method static None() The entity does not have an owner. For internal use only.
 * @method static UserOwned() The entity is owned by a system user.
 * @method static TeamOwned() The entity is owned by a team. For internal use only.
 * @method static BusinessOwned() The entity is owned by a business unit. For internal use only.
 * @method static OrganizationOwned() The entity is owned by an organization.
 * @method static BusinessParented() The entity is parented by a business unit. For internal use only.
 */
class OwnershipTypes extends FlaggedEnum {

    /**
     * The entity does not have an owner. For internal use only.
     */
    const None = 0;

    /**
     * The entity is owned by a system user.
     */
    const UserOwned = 1;

    /**
     * The entity is owned by a team. For internal use only.
     */
    const TeamOwned = 2;

    /**
     * The entity is owned by a business unit. For internal use only.
     */
    const BusinessOwned = 4;

    /**
     * The entity is owned by an organization.
     */
    const OrganizationOwned = 8;

    /**
     * The entity is parented by a business unit. For internal use only.
     */
    const BusinessParented = 16;

}

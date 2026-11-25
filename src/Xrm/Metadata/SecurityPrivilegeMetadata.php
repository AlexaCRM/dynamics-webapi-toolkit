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
 * Contains the metadata that describes a security privilege for access to an entity.
 */
#[\AllowDynamicProperties]
final class SecurityPrivilegeMetadata {

    /**
     * Gets whether the privilege can be basic access level.
     *
     * @var bool
     */
    public $CanBeBasic;

    /**
     * Gets whether the privilege can be deep access level.
     *
     * @var bool
     */
    public $CanBeDeep;

    /**
     * @var bool
     */
    public $CanBeEntityReference;

    /**
     * Gets whether the privilege can be global access level.
     *
     * @var bool
     */
    public $CanBeGlobal;

    /**
     * Gets whether the privilege can be local access level.
     *
     * @var bool
     */
    public $CanBeLocal;

    /**
     * @var bool
     */
    public $CanBeParentEntityReference;

    /**
     * Gets the name of the privilege.
     *
     * @var string
     */
    public $Name;

    /**
     * Gets the ID of the privilege.
     *
     * @var string
     */
    public $PrivilegeId;

    /**
     * Gets the type of the privilege.
     *
     * @var PrivilegeType
     */
    public $PrivilegeType;

}

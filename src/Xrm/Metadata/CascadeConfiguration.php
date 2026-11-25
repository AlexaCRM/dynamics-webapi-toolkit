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
 * Contains properties representing actions that may be performed
 * on the referenced entity in a one-to-many entity relationship.
 */
#[\AllowDynamicProperties]
class CascadeConfiguration {

    /**
     * The referenced entity record owner is changed.
     *
     * @var CascadeType
     */
    public $Assign;

    /**
     * The referenced entity record is deleted.
     *
     * @var CascadeType
     */
    public $Delete;

    /**
     * The record is merged with another record.
     *
     * @var CascadeType
     */
    public $Merge;

    /**
     * The value of the referencing attribute in a parental relationship changes.
     *
     * @var CascadeType
     */
    public $Reparent;

    /**
     * Indicates whether the associated activities of the related entity should be included
     * in the Activity Associated View for the primary entity.
     *
     * @var CascadeType
     */
    public $RollupView;

    /**
     * The referenced entity record is shared with another user.
     *
     * @var CascadeType
     */
    public $Share;

    /**
     * Sharing is removed for the referenced entity record.
     *
     * @var CascadeType
     */
    public $Unshare;

}

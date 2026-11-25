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
 *
 */

namespace AlexaCRM\Xrm;

/**
 * Identifies a record in Dynamics 365.
 */
class EntityReference {

    use EntityLikeTrait;

    /**
     * Value of the primary attribute of the entity.
     *
     * @var string|null
     */
    public ?string $Name = null;

    /**
     * EntityReference constructor.
     *
     * An EntityReference instance may be created without any parameters specified,
     * or with entity name specified, or with entity name and record ID specified,
     * or with entity name and collection of KeyAttributes specified,
     * or with entity name, key name and key value specified.
     *
     * @param string|null $entityName
     * @param string|KeyAttributeCollection|null $entityId Record ID, KeyAttributeCollection, or key name
     * @param mixed $keyValue Key value.
     */
    public function __construct( ?string $entityName = null, $entityId = null, $keyValue = null ) {
        $this->constructOverloaded( $entityName, $entityId, $keyValue );
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->Name ?? '';
    }

}

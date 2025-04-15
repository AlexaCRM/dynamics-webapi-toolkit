<?php
/**
 * Copyright 2020 AlexaCRM
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

namespace AlexaCRM\Xrm;

/**
 * Provides essential properties and utility methods for entity-like types -- Entity and EntityReference.
 */
trait EntityLikeTrait {

    /**
     * Unique ID of the record.
     */
    public ?string $Id = null;

    /**
     * Logical name of the entity.
     */
    public ?string $LogicalName = null;

    /**
     * Key attributes of the record.
     */
    public ?KeyAttributeCollection $KeyAttributes = null;

    /**
     * Constructor with overloading to support multiple initialization strategies.
     *
     * @param string|null $entityName
     * @param string|KeyAttributeCollection|null $entityId Record ID, KeyAttributeCollection, or key name
     * @param mixed $keyValue Key value.
     */
    private function constructOverloaded( ?string $entityName = null, $entityId = null, $keyValue = null ): void {
        if ( $entityName === null ) {
            return;
        }

        $this->LogicalName = $entityName;

        if ( $entityId === null && $keyValue === null ) {
            return;
        }

        if ( $entityId instanceof KeyAttributeCollection ) {
            $keyAttributes = $entityId;
            $this->KeyAttributes = $keyAttributes;

            return;
        }

        if ( is_string( $entityId ) && $keyValue === null ) {
            $this->Id = $entityId;

            return;
        }

        $this->KeyAttributes = new KeyAttributeCollection();
        $keyName = $entityId;
        $this->KeyAttributes->Add( $keyName, $keyValue );
    }

}

<?php
/**
 * Copyright 2019 AlexaCRM
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

namespace AlexaCRM\WebAPI;

use AlexaCRM\StrongSerializer\Deserializer;
use AlexaCRM\StrongSerializer\Reference;
use Elao\Enum\EnumInterface;

/**
 * Provides deserializing capability with special enum treatment.
 */
class MetadataDeserializer extends Deserializer {

    /**
     * Creates a strongly-typed object. $type holds the FQCN of the type.
     *
     * @param object|object[] $data
     * @param Reference $type
     *
     * @return object
     */
    protected function toStrong( $data, Reference $type ) {
        if ( $data === null ) {
            return null;
        }

        $className = $type->getClassName( $data );

        // Create enums separately.
        if ( is_subclass_of( $className, EnumInterface::class ) ) {
            return $className::$data();
        }

        return parent::toStrong( $data, $type );
    }

}

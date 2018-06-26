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

namespace AlexaCRM\WebAPI;

/**
 * Represents a generic Web API exception.
 */
class Exception extends \Exception {

    /**
     * Underlying exception with relevant information about the error.
     *
     * @var \Exception
     */
    protected $innerException;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param \Exception $inner Inner exception containing more details about the exception.
     */
    public function __construct( string $message = '', $inner = null ) {
        $this->message = $message;
        $this->innerException = $inner;
    }

    /**
     * Returns the underlying exception with relevant information about the error.
     *
     * @return \Exception
     */
    public function getInnerException() {
        return $this->innerException;
    }

}

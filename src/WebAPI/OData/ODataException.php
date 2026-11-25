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

namespace AlexaCRM\WebAPI\OData;

use GuzzleHttp\Exception\RequestException;

/**
 * Represents an exception triggered by a non-succeeding response from the OData service.
 */
class ODataException extends Exception {

    /**
     * Underlying Guzzle-generated exception with relevant request and response objects.
     *
     * @var RequestException
     */
    protected $innerException;

    /**
     * Response from the service endpoint.
     *
     * @var object
     */
    protected $response;

    /**
     * ODataException constructor.
     *
     * @param object $response OData error response object
     * @param RequestException|null $inner
     */
    public function __construct( $response, ?RequestException $inner = null ) {
        parent::__construct();
        $this->message = $response;
        if ( $inner !== null ) {
            $guzzleRequest = $inner->getRequest();
            $guzzleResponse = $inner->getResponse();
            $statusCode = ( $guzzleResponse !== null ) ? $guzzleResponse->getStatusCode() : 0;

            $this->code = $statusCode;

            $level = (int) floor( $statusCode / 100 );
            if ( $level === 4 ) {
                $label = 'Client error';
            } elseif ( $level === 5 ) {
                $label = 'Server error';
            } else {
                $label = 'Unsuccessful request';
            }

            $uri = $guzzleRequest->getUri();

            $this->message = sprintf(
                '%s: `%s %s` resulted in a `%s %s` response with the following message: `%s`',
                $label,
                $guzzleRequest->getMethod(),
                $uri,
                $statusCode,
                ( $guzzleResponse !== null ) ? $guzzleResponse->getReasonPhrase() : '',
                $response->message ?? $response
            );
        }

        $this->response = $response;
        $this->innerException = $inner;
    }

    /**
     * Returns the error details response given by the OData service.
     *
     * @return object
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Returns the underlying Guzzle-generated exception with relevant request and response objects.
     *
     * @return RequestException
     */
    public function getInnerException() {
        return $this->innerException;
    }

}

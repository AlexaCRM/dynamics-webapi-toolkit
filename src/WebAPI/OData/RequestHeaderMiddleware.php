<?php
/**
 * Copyright 2025 AlexaCRM
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

namespace AlexaCRM\WebAPI\OData;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

use Psr\Http\Message\RequestInterface;

/**
 * Represents a generic middleware for modifying request headers for Guzzle Client.
 */
class RequestHeaderMiddleware implements MiddlewareInterface {

    /**
     * @var string
     */
    protected string $headerName;

    /**
     * @var mixed
     */
    protected mixed $headerValue;

    /**
     * RequestHeaderMiddleware constructor.
     *
     * @param string $headerName
     * @param mixed $headerValue
     */
    public function __construct( string $headerName, mixed $headerValue ) {
        $this->headerName = $headerName;
        $this->headerValue = self::normalizeHeaderValue( $headerValue );
    }

    /**
     * Casts scalar and null header values to strings.
     *
     * Guzzle PSR-7 2.x (Guzzle 7) cast scalar and null header values to strings implicitly,
     * Guzzle PSR-7 3.x (Guzzle 8) accepts only strings or arrays of strings.
     *
     * @param mixed $headerValue
     *
     * @return mixed
     */
    private static function normalizeHeaderValue( mixed $headerValue ): mixed {
        if ( is_array( $headerValue ) ) {
            return array_map( [ self::class, 'normalizeHeaderValue' ], $headerValue );
        }

        if ( is_scalar( $headerValue ) || $headerValue === null ) {
            return (string)$headerValue;
        }

        return $headerValue;
    }

    /**
     * Returns a Guzzle-compliant middleware.
     *
     * @return callable
     *
     * @see http://docs.guzzlephp.org/en/stable/handlers-and-middleware.html#creating-a-handler
     */
    public function getMiddleware(): callable {
        $self = $this;

        return static function ( callable $handler ) use ( $self ) {
            return static function ( RequestInterface $request, array $options ) use ( $handler, $self ) {
                $request = $request->withHeader( $self->headerName, $self->headerValue );

                return $handler( $request, $options );
            };
        };
    }
}

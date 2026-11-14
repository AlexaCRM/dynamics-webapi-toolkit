<?php
/**
 * Copyright 2026 AlexaCRM
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

use Psr\Http\Message\ResponseInterface;

/**
 * Bridges API differences between Guzzle 7 and Guzzle 8.
 *
 * @internal
 */
final class GuzzleCompat {

    private function __construct() {
    }

    /**
     * Returns the HTTP response attached to a Guzzle exception, if any.
     *
     * Guzzle 7 exposes getResponse() on every RequestException (nullable). Guzzle 8 removed it from
     * RequestException and exposes it only on ResponseException, which does not exist in Guzzle 7.
     *
     * @param \Throwable $e
     *
     * @return ResponseInterface|null
     */
    public static function getResponse( \Throwable $e ): ?ResponseInterface {
        if ( !method_exists( $e, 'getResponse' ) ) {
            return null;
        }

        $response = $e->getResponse();

        return $response instanceof ResponseInterface ? $response : null;
    }
}

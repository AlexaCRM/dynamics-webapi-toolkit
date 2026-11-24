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

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

/**
 * Helpers that behave identically on Guzzle 7 (psr7 2.x) and Guzzle 8 (psr7 3.x).
 *
 * Guzzle 7: RequestException::getResponse(): ?ResponseInterface exists on every RequestException.
 * Guzzle 8: RequestException has no getResponse(); only ResponseException and its subclasses
 *           (BadResponseException, ClientException, ServerException, ResponseTransferException,
 *           ResponseTimeoutException, TooManyRedirectsException) expose getResponse(): ResponseInterface.
 *           ConnectException/NetworkException (incl. timeouts) never carry a response.
 *
 * @internal
 */
final class GuzzleCompat {

    /**
     * Guzzle 8 only: the response head was received but the body transfer failed (e.g. ResponseTimeoutException).
     * Referenced by name so that the code keeps working when the class does not exist (Guzzle 7).
     */
    private const RESPONSE_TRANSFER_EXCEPTION = 'GuzzleHttp\Exception\ResponseTransferException';

    private function __construct() {
    }

    /**
     * Safely extracts the HTTP response from any throwable, regardless of the Guzzle major version.
     */
    public static function getResponse( ?\Throwable $e ): ?ResponseInterface {
        if ( $e === null ) {
            return null;
        }

        // Exists in both v7 and v8, always carries a response.
        if ( $e instanceof BadResponseException ) {
            return $e->getResponse();
        }

        if ( method_exists( $e, 'getResponse' ) ) {
            $response = $e->getResponse();

            return $response instanceof ResponseInterface ? $response : null;
        }

        return null;
    }

    /**
     * Tells whether the failure happened while receiving the response body (Guzzle 8 ResponseTransferException,
     * e.g. ResponseTimeoutException). Such responses must be treated as transport errors, not as OData errors,
     * because the status code may well be 2xx.
     */
    public static function isResponseTransferFailure( \Throwable $e ): bool {
        return is_a( $e, self::RESPONSE_TRANSFER_EXCEPTION );
    }

    /**
     * Reads the whole response body as a string, rewinding the stream if possible,
     * so the body can be read more than once (logging + error handling).
     */
    public static function getBody( ?ResponseInterface $response ): ?string {
        if ( $response === null ) {
            return null;
        }

        try {
            $body = $response->getBody();
            if ( $body->isSeekable() ) {
                $body->rewind();
            }
            $contents = $body->getContents();
            if ( $body->isSeekable() ) {
                $body->rewind();
            }

            return $contents;
        } catch ( \RuntimeException $e ) {
            return null;
        }
    }

    /**
     * Normalizes an HTTP method to the canonical upper-case form.
     * psr7 3.x no longer upper-cases methods automatically.
     */
    public static function normalizeMethod( string $method ): string {
        return strtoupper( trim( $method ) );
    }

    /**
     * Converts header values the same way Guzzle 7 (psr7 2.x) did implicitly, so that psr7 3.x
     * (Guzzle 8), which accepts only `string|string[]`, receives exactly what Guzzle 7 would have sent.
     *
     * Headers with an empty array value are dropped: Guzzle 7 accepted them but never put them on the wire,
     * psr7 3.x rejects them.
     *
     * @param array $headers
     *
     * @return array
     *
     * @see normalizeHeaderValue()
     */
    public static function normalizeHeaders( array $headers ): array {
        $normalized = [];
        foreach ( $headers as $name => $value ) {
            $value = self::normalizeHeaderValue( $value );
            if ( $value === [] ) {
                continue;
            }

            $normalized[ $name ] = $value;
        }

        return $normalized;
    }

    /**
     * Casts a header value (or each item of a list) the way psr7 2.x (Guzzle 7) did:
     * scalars and null are cast with `(string)` -- null => '', true => '1', false => '', 1.5 => '1.5'.
     *
     * Non-scalar values are returned unchanged, so Guzzle rejects them exactly as before
     * (InvalidArgumentException in both Guzzle 7 and 8). Whitespace trimming and validation
     * are left to Guzzle.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public static function normalizeHeaderValue( mixed $value ): mixed {
        if ( is_array( $value ) ) {
            return array_map( static fn( mixed $item ): mixed => self::scalarToString( $item ), array_values( $value ) );
        }

        return self::scalarToString( $value );
    }

    private static function scalarToString( mixed $value ): mixed {
        if ( is_string( $value ) || ( !is_scalar( $value ) && $value !== null ) ) {
            return $value;
        }

        // psr7 2.x converts non-finite floats explicitly (implicit NAN coercion warns on PHP 8.5).
        if ( is_float( $value ) && !is_finite( $value ) ) {
            return is_nan( $value ) ? 'NAN' : ( $value > 0 ? 'INF' : '-INF' );
        }

        return (string)$value;
    }
}


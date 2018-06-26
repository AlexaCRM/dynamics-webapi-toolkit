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

/**
 * Represents a Bearer token issued by an OAuth2 token endpoint.
 */
class Token {

    /**
     * Token type, usually `Bearer`.
     *
     * @var string
     */
    public $token_type;

    /**
     * @var int
     */
    public $expires_in;

    /**
     * @var int
     */
    public $ext_expires_in;

    /**
     * @var int
     */
    public $expires_on;

    /**
     * @var int
     */
    public $not_before;

    /**
     * Resource URI for which the token was granted.
     *
     * @var string
     */
    public $resource;

    /**
     * Token value.
     *
     * @var string
     */
    public $access_token;

    /**
     * Constructs a new Token object from a JSON received from an OAuth2 token endpoint.
     *
     * @param string $json
     *
     * @return Token
     */
    public static function createFromJson( $json ) {
        try {
            $tokenArray = \GuzzleHttp\json_decode( $json, true );
        } catch ( \InvalidArgumentException $e ) {
            $tokenArray = [];
        }

        $token = new Token();

        // Returns an empty token object in case of JSON decoding error.
        if ( !count( $tokenArray ) ) {
            return $token;
        }

        foreach ( $tokenArray as $key => $value ) {
            if ( !property_exists( Token::class, $key ) ) {
                continue;
            }

            $token->{$key} = $value;
        }

        $token->expires_in = (int)$token->expires_in;
        $token->ext_expires_in = (int)$token->ext_expires_in;
        $token->expires_on = (int)$token->expires_on;
        $token->not_before = (int)$token->not_before;

        return $token;
    }

    /**
     * Tells whether the token is not expired.
     *
     * @param int $time Specify time to check the token against.
     *
     * @return bool
     */
    public function isValid( $time = null ) {
        if ( $time === null ) {
            $time = time();
        }

        return ( $time >= $this->not_before && $time < $this->expires_on );
    }

}


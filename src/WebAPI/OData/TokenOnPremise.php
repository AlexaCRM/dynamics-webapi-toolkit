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

use GuzzleHttp\Utils;

/**
 * Represents a Bearer token issued by an OAuth2 token endpoint.
 */
class TokenOnPremise extends Token {

    /**
     * Refresh Token value.
     */
    public ?string $refreshToken = null;

    public ?int $refreshTokenExpiresIn = null;

    /**
     * Constructs a new Token object from a JSON received from an OAuth2 token endpoint.
     *
     * @param string $json
     *
     * @return TokenOnPremise
     */
    public static function createFromJson( string $json ): TokenOnPremise {
        try {
            $tokenArray = Utils::jsonDecode( $json, true );
        } catch ( \InvalidArgumentException $e ) {
            return new TokenOnPremise();
        }

        $token = new TokenOnPremise();
        $token->type = $tokenArray['token_type'] ?? null;
        $token->expiresIn = isset( $tokenArray['expires_in'] ) ? (int)$tokenArray['expires_in'] + time() : null;
        $token->resource = $tokenArray['resource'] ?? null;
        $token->token = $tokenArray['access_token'] ?? null;
        $token->refreshToken = $tokenArray['refresh_token'] ?? null;
        if ( isset( $tokenArray['refresh_token_expires_in'] ) ) {
            $token->refreshTokenExpiresIn = $tokenArray['refresh_token_expires_in'] + time();
        }

        return $token;
    }

    public function isValid( ?int $time = null ): bool {
        if ( $time === null ) {
            $time = time();
        }

        return ( $time < $this->expiresIn );
    }

}


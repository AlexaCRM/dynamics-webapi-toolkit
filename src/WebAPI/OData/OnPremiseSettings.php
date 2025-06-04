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
 * Contains Dynamics 365 (On Premise) credentials.
 */
class OnPremiseSettings extends Settings {

    /**
     * Authorization endpoint
     *
     * @var string
     */
    public string $authURI;

    /**
     * Refresh token endpoint
     *
     * @var string
     */
    public string $refreshTokenURI;

    /**
     * Azure AD application ID.
     */
    public string $applicationID;

    /**
     * Azure AD application secret.
     */
    public ?string $applicationSecret;

    public ?string $username = null;

    public ?string $password = null;

    public ?string $resource = null;

    /**
     * Returns Web API endpoint URI.
     */
    public function getEndpointURI(): string {
        return trim( $this->instanceURI, '/' ) . '/api/data/v' . $this->apiVersion . '/';
    }

    /**
     * Returns Web API endpoint URI.
     */
    public function getOnPremiseAuthURI(): string {
        return trim( $this->authURI, '/' );
    }

    /**
     * Returns refresh token endpoint URI.
     */
    public function getRefreshTokenURI(): string {
        return trim( $this->refreshTokenURI, '/' );
    }

}

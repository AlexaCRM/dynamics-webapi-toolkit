<?php

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


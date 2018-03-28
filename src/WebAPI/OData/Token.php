<?php

namespace AlexaCRM\WebAPI\OData;

class Token {

    public $token_type;

    public $expires_in;

    public $ext_expires_in;

    public $expires_on;

    public $not_before;

    public $resource;

    public $access_token;

    /**
     * @param string $json
     *
     * @return Token
     */
    public static function createFromJson( $json ) {
        $tokenArray = \GuzzleHttp\json_decode( $json, true );

        $token = new Token();
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


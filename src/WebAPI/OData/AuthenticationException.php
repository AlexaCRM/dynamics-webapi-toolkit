<?php

namespace AlexaCRM\WebAPI\OData;

class AuthenticationException extends Exception {

    /**
     * AuthenticationException constructor.
     *
     * @param string $message
     */
    public function __construct( $message = '' ) {
        $this->message = $message;
    }

}

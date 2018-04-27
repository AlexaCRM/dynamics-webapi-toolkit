<?php

namespace AlexaCRM\WebAPI;

/**
 * Represents a generic Web API exception.
 */
class Exception extends \Exception {

    /**
     * Underlying exception with relevant information about the error.
     *
     * @var \Exception
     */
    protected $innerException;

    /**
     * Exception constructor.
     *
     * @param string $message
     * @param \Exception $inner Inner exception containing more details about the exception.
     */
    public function __construct( string $message = '', $inner = null ) {
        $this->message = $message;
        $this->innerException = $inner;
    }

    /**
     * Returns the underlying exception with relevant information about the error.
     *
     * @return \Exception
     */
    public function getInnerException() {
        return $this->innerException;
    }

}

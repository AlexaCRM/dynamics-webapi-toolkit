<?php

namespace AlexaCRM\WebAPI\OData;

use GuzzleHttp\Exception\RequestException;

/**
 * Represents an exception triggered by a non-succeeding response from the OData service.
 */
class ODataException extends \AlexaCRM\WebAPI\Exception {

    /**
     * Underlying Guzzle-generated exception with relevant request and response objects.
     *
     * @var RequestException
     */
    protected $innerException;

    /**
     * Response from the service endpoint.
     *
     * @var object
     */
    protected $response;

    /**
     * ODataException constructor.
     *
     * @param object $response OData error response object
     * @param RequestException $previous
     */
    public function __construct( $response, RequestException $previous = null ) {
        $this->message = $response;
        if ( $previous !== null ) {
            $guzzleRequest = $previous->getRequest();
            $guzzleResponse = $previous->getResponse();

            $level = (int) floor($guzzleResponse->getStatusCode() / 100);
            if ($level === 4) {
                $label = 'Client error';
            } elseif ($level === 5) {
                $label = 'Server error';
            } else {
                $label = 'Unsuccessful request';
            }

            $uri = $guzzleRequest->getUri();

            $this->message = sprintf(
                '%s: `%s %s` resulted in a `%s %s` response with the following message: `%s`',
                $label,
                $guzzleRequest->getMethod(),
                $uri,
                $guzzleResponse->getStatusCode(),
                $guzzleResponse->getReasonPhrase(),
                $response->message
            );
        }

        $this->response = $response;
        $this->innerException = $previous;
    }

    /**
     * Returns the error details response given by the OData service.
     *
     * @return object
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * Returns the underlying Guzzle-generated exception with relevant request and response objects.
     *
     * @return RequestException
     */
    public function getInnerException() {
        return $this->innerException;
    }

}

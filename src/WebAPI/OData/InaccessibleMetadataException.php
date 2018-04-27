<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * InaccessibleMetadataException is thrown when Web API OData service metadata is requested
 * and an error response is returned instead.
 *
 * This case is non-recoverable for toolkit
 * as it relies strongly on service metadata for all operations.
 */
class InaccessibleMetadataException extends Exception {}

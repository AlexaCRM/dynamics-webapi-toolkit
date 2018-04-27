<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * EntityNotSupportedException is thrown in case an Entity instance is passed
 * to IOrganizationService and Web API doesn't have a corresponding EntitySet (collection)
 * for the specified entity name.
 */
class EntityNotSupportedException extends Exception {}

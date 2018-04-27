<?php

namespace AlexaCRM\WebAPI\OData;

/**
 * AuthenticationException is thrown if Dynamics 365 rejects the access token.
 *
 * In Dynamics 365 (online) the exception is thrown if Azure AD doesn't issue a new access token.
 */
class AuthenticationException extends Exception {}

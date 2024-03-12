<?php

namespace App\Service\Auth;

enum AuthTokenType: string
{
    /**
     * An OAuth type represents an Authentication Token created via an OAuth flow.
     * OAuth Tokens represent 3rd party apps acting on behalf of a User.
     */
    case OAuth = 'oat';

    /**
     * A Personal type represents an Authentication Token created via direct login.
     * Personal Access Tokens represent the User itself.
     */
    case Personal = 'pat';
}

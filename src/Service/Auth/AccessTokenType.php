<?php

namespace App\Service\Auth;

enum AccessTokenType: string
{
    /**
     * An OAuth type represents an Acces Token created via an OAuth flow.
     * OAuth Access Tokens represent 3rd party apps acting on behalf of a User.
     */
    case OAuth = 'oat';

    /**
     * A Personal type represents an Access Token created via direct login.
     * Personal Access Token represents the User itself.
     */
    case Personal = 'pat';
}

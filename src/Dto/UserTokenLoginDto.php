<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserTokens are created via a login flow where v4:
 * 1. Receives the User credentials
 * 2. Validates them
 * 3. Generates the UserToken for the authenticated User
 */
final class UserTokenLoginDto
{
    /**
     * The identifier (email, username) of the User to be authenticated.
     */
    #[Assert\NotBlank()]
    public readonly string $identifier;

    /**
     * The password of the User to be authenticated.
     */
    #[Assert\NotBlank()]
    public readonly string $password;

    public function __construct(string $identifier, string $password)
    {
        $this->identifier = $identifier;
        $this->password = $password;
    }
}

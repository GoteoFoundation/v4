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
     * The username of the User to be authenticated.
     */
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4, max: 30)]
    #[Assert\Regex('/^[a-z0-9_-]+$/')]
    public readonly string $username;

    /**
     * The password of the User to be authenticated.
     */
    #[Assert\NotBlank()]
    #[Assert\Length(min: 12)]
    public readonly string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
}

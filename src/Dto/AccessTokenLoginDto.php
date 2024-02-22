<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class AccessTokenLoginDto
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

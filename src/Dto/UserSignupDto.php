<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class UserSignupDto
{
    #[Assert\NotBlank()]
    #[Assert\Email()]
    public string $email;

    #[Assert\NotBlank()]
    #[Assert\Length(min: 8)]
    public string $password;

    /**
     * A unique, non white space, byte-safe string identifier for this User.
     */
    #[Assert\NotBlank()]
    #[Assert\Length(min: 4, max: 30)]
    #[Assert\Regex('/^[a-z0-9_]+$/')]
    public string $username;

    /**
     * Display name chosen by the User.
     */
    public string $name;
}

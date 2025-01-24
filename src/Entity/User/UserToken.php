<?php

namespace App\Entity\User;

use App\Entity\Interface\UserOwnedInterface;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\UserOwnedTrait;
use App\Repository\User\UserTokenRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * UserTokens authenticate requests on behalf of the User who owns them.\
 * \
 * When a UserToken is created v4 generates a SHA-256 hash that is unique for that Token and the User it represents.
 * The value of a token comes preceded by a 4-digit-length prefix based on the type of token it is.\
 * \
 * `oat_` means the token was created via an OAuth flow.\
 * `pat_` means the token was created via a login flow.
 */
#[ORM\Entity(repositoryClass: UserTokenRepository::class)]
class UserToken implements UserOwnedInterface
{
    use TimestampedCreationEntity;
    use UserOwnedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The value to be used in the Authorization header.
     */
    #[ORM\Column(length: 68)]
    private ?string $token = null;

    /**
     * The User on behalf of which this UserToken authenticates.
     */
    #[ORM\ManyToOne(inversedBy: 'tokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }
}

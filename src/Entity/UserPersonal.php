<?php

namespace App\Entity;

use Ambta\DoctrineEncryptBundle\Configuration\Encrypted;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserPersonalRepository;
use Doctrine\ORM\Mapping as ORM;


/**
 * UserPersonal is the detailed data of a User.
 *
 * The personal data of a user will be kept encrypted
 */
#[ORM\Entity(repositoryClass: UserPersonalRepository::class)]
#[ApiResource]
class UserPersonal
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'personalData', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * The Identity Document number of the User.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Encrypted]
    private ?string $identityDocument = null;

    /**
     * The type of indentity document that the User has, ie: Passport, DNI, NIE, etc.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Encrypted]
    private ?string $identityDocumentType = null;

    /**
     * The Postal Code of where the User lives.
     */
    #[ORM\Column(length: 255, nullable: true)]
    #[Encrypted]
    private ?string $postalCode = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getIdentityDocument(): ?string
    {
        return $this->identityDocument;
    }

    public function setIdentityDocument(?string $identityDocument): static
    {
        $this->identityDocument = $identityDocument;

        return $this;
    }

    public function getIndetityDocumentType(): ?string
    {
        return $this->identityDocumentType;
    }

    public function setIndetityDocumentType(?string $identityDocumentType): static
    {
        $this->identityDocumentType = $identityDocumentType;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }
}

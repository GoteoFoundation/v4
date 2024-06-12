<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserPersonalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPersonalRepository::class)]
#[ApiResource]
class UserPersonal
{
    #[ORM\Id]
    #[ORM\OneToOne(inversedBy: 'personalData', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Encrypted]
    private ?string $identity_document = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $indetity_document_type = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Encrypted]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Encrypted]
    private ?string $postal_code = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getIdentityDocument(): ?string
    {
        return $this->identity_document;
    }

    public function setIdentityDocument(?string $identity_document): static
    {
        $this->identity_document = $identity_document;

        return $this;
    }

    public function getIndetityDocumentType(): ?string
    {
        return $this->indetity_document_type;
    }

    public function setIndetityDocumentType(?string $indetity_document_type): static
    {
        $this->indetity_document_type = $indetity_document_type;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setCP(?string $postal_code): static
    {
        $this->postal_code = $postal_code;

        return $this;
    }

}

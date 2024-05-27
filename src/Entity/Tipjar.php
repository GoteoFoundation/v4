<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\TipjarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Unlike other Money recipients a Tipjar receives money with no further goal.\
 * \
 * Tips to the platform owners and other no-purpose money can target a Tipjar.
 */
#[UniqueEntity(fields: ['name'], message: 'A Tipjar with that name already exists.')]
#[API\ApiResource]
#[ORM\Entity(repositoryClass: TipjarRepository::class)]
class Tipjar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Human readable, non white space, unique string.
     */
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToOne(inversedBy: 'tipjar', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $accounting = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAccounting(): ?Accounting
    {
        return $this->accounting;
    }

    public function setAccounting(Accounting $accounting): static
    {
        $this->accounting = $accounting;

        return $this;
    }
}

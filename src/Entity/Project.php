<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Projects describe a community-led event that is to be discovered, developed and funded by other Users.\
 * \
 * Since they can be recipients of funding, they are assigned an Accounting when created.
 * A Project's Accounting represents how much money the Project has raised from the community.
 */
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Get()]
#[API\Put(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\Delete(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\Patch(security: 'is_granted("AUTH_PROJECT_EDIT")')]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $accounting = null;

    public function __construct()
    {
        $this->accounting = new Accounting;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

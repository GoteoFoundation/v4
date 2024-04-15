<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata as API;
use App\Repository\ProjectRepository;
use App\Entity\ProjectStatus as Status;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Projects describe a community-led event that is to be discovered, developed and funded by other Users.\
 * \
 * Since they can be recipients of funding, they are assigned an Accounting when created.
 * A Project's Accounting represents how much money the Project has raised from the community.
 */
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Get()]
#[API\Put(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\Delete(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\Patch(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\ApiFilter(filterClass: SearchFilter::class, properties: [
    'title' => 'partial',
    'status', 'owner'
])]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
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

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private User $owner;

    #[API\ApiProperty(writable: true)]
    #[ORM\Column(type: 'string', enumType: Status::class)]
    private Status $status;

    #[API\ApiProperty(writable: false)]
    #[ORM\Column(type: 'int')]
    private ?Money $amount;

    use TimestampableEntity;

    public function __construct()
    {
        $this->accounting = new Accounting();
        $this->accounting->setOwnerClass(Project::class);
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

    public function setOwner(User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}

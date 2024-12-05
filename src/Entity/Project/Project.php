<?php

namespace App\Entity\Project;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Trait\MigratedEntity;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Entity\User\User;
use App\Repository\Project\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Projects describe a community-led event that is to be discovered, developed and funded by other Users.
 */
#[API\GetCollection()]
#[API\Post(security: 'is_granted("ROLE_USER")')]
#[API\Get()]
#[API\Delete(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\Patch(security: 'is_granted("AUTH_PROJECT_EDIT")')]
#[API\ApiFilter(filterClass: SearchFilter::class, properties: [
    'title' => 'partial',
    'status' => 'exact',
    'owner' => 'exact',
])]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements AccountingOwnerInterface
{
    use MigratedEntity;
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The main title for the project.
     */
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    /**
     * Since Projects can be recipients of funding, they are assigned an Accounting when created.
     * A Project's Accounting represents how much money the Project has raised from the community.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(inversedBy: 'project', cascade: ['persist'])]
    private ?Accounting $accounting = null;

    /**
     * The User who created this Project.
     */
    #[API\ApiProperty(writable: false)]
    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * The status of this Project as it goes through it's life-cycle.
     * Projects have a start and an end, and in the meantime they go through different phases represented under this status.
     */
    #[API\ApiProperty(writable: true)]
    #[ORM\Column(type: 'string', enumType: ProjectStatus::class)]
    private ProjectStatus $status;

    public function __construct()
    {
        $accounting = new Accounting();
        $accounting->setOwner($this);

        $this->accounting = $accounting;
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

    public function setAccounting(?Accounting $accounting): static
    {
        $this->accounting = $accounting;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function setStatus(ProjectStatus $status): static
    {
        $this->status = $status;

        return $this;
    }
}

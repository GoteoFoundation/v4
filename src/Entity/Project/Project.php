<?php

namespace App\Entity\Project;

use App\Entity\Accounting\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Interface\LocalizedEntityInterface;
use App\Entity\Interface\UserOwnedInterface;
use App\Entity\Trait\LocalizedEntityTrait;
use App\Entity\Trait\MigratedEntity;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Entity\Trait\UserOwnedTrait;
use App\Entity\User\User;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Project\ProjectRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements UserOwnedInterface, AccountingOwnerInterface, LocalizedEntityInterface
{
    use LocalizedEntityTrait;
    use MigratedEntity;
    use TimestampedCreationEntity;
    use TimestampedUpdationEntity;
    use UserOwnedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The main title for the project.
     */
    #[ORM\Column(length: 255)]
    #[Gedmo\Translatable()]
    private ?string $title = null;

    /**
     * Secondary head-line for the project.
     */
    #[ORM\Column(length: 255)]
    #[Gedmo\Translatable()]
    private ?string $subtitle = null;

    /**
     * Project's territory of interest.
     */
    #[ORM\Embedded(class: ProjectTerritory::class)]
    private ?ProjectTerritory $territory;

    /**
     * The description body for the Project.
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * Since Projects can be recipients of funding, they are assigned an Accounting when created.
     * A Project's Accounting represents how much money the Project has raised from the community.
     */
    #[ORM\OneToOne(inversedBy: 'project', cascade: ['persist'])]
    private ?Accounting $accounting = null;

    /**
     * The User who created this Project.
     */
    #[ORM\ManyToOne(inversedBy: 'projects', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    /**
     * The status of this Project as it goes through it's life-cycle.
     * Projects have a start and an end, and in the meantime they go through different phases represented under this status.
     */
    #[ORM\Column(type: 'string', enumType: ProjectStatus::class)]
    private ProjectStatus $status;

    /**
     * @var Collection<int, Reward>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Reward::class)]
    private Collection $rewards;

    public function __construct()
    {
        $this->accounting = Accounting::of($this);
        $this->rewards = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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

    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    public function setSubtitle(string $subtitle): static
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getTerritory(): ?ProjectTerritory
    {
        return $this->territory;
    }

    public function setTerritory(ProjectTerritory $territory): static
    {
        $this->territory = $territory;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

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

    public function getStatus(): ProjectStatus
    {
        return $this->status;
    }

    public function setStatus(ProjectStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Reward>
     */
    public function getRewards(): Collection
    {
        return $this->rewards;
    }

    public function addReward(Reward $reward): static
    {
        if (!$this->rewards->contains($reward)) {
            $this->rewards->add($reward);
            $reward->setProject($this);
        }

        return $this;
    }

    public function removeReward(Reward $reward): static
    {
        if ($this->rewards->removeElement($reward)) {
            // set the owning side to null (unless already changed)
            if ($reward->getProject() === $this) {
                $reward->setProject(null);
            }
        }

        return $this;
    }
}

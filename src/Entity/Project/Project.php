<?php

namespace App\Entity\Project;

use App\Entity\Accounting\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Interface\UserOwnedInterface;
use App\Entity\Matchfunding\MatchSubmission;
use App\Entity\Trait\MigratedEntity;
use App\Entity\Trait\TimestampedCreationEntity;
use App\Entity\Trait\TimestampedUpdationEntity;
use App\Entity\User\User;
use App\Repository\Project\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project implements UserOwnedInterface, AccountingOwnerInterface
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

    /**
     * @var Collection<int, MatchSubmission>
     */
    #[ORM\OneToMany(mappedBy: 'project', targetEntity: MatchSubmission::class)]
    private Collection $matchSubmissions;

    public function __construct()
    {
        $this->accounting = Accounting::of($this);
        $this->rewards = new ArrayCollection();
        $this->matchSubmissions = new ArrayCollection();
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

    public function isOwnedBy(User $user): bool
    {
        return $user->getId() === $this->owner->getId();
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

    /**
     * @return Collection<int, MatchSubmission>
     */
    public function getMatchSubmissions(): Collection
    {
        return $this->matchSubmissions;
    }

    public function addMatchSubmission(MatchSubmission $matchSubmission): static
    {
        if (!$this->matchSubmissions->contains($matchSubmission)) {
            $this->matchSubmissions->add($matchSubmission);
            $matchSubmission->setProject($this);
        }

        return $this;
    }

    public function removeMatchSubmission(MatchSubmission $matchSubmission): static
    {
        if ($this->matchSubmissions->removeElement($matchSubmission)) {
            // set the owning side to null (unless already changed)
            if ($matchSubmission->getProject() === $this) {
                $matchSubmission->setProject(null);
            }
        }

        return $this;
    }
}

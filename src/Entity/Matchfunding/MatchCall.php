<?php

namespace App\Entity\Matchfunding;

use App\Entity\Accounting\Accounting;
use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\User\User;
use App\Repository\Matchfunding\MatchCallRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MatchCallRepository::class)]
class MatchCall implements AccountingOwnerInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'matchCall', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $accounting = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class)]
    private Collection $managers;

    /**
     * @var Collection<int, MatchCallSubmission>
     */
    #[ORM\OneToMany(mappedBy: 'matchCall', targetEntity: MatchCallSubmission::class)]
    private Collection $matchCallSubmissions;

    #[ORM\Column(length: 255)]
    private ?string $strategyName = null;

    public function __construct()
    {
        $this->accounting = Accounting::of($this);
        $this->managers = new ArrayCollection();
        $this->matchCallSubmissions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, User>
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(User $manager): static
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }

        return $this;
    }

    public function removeManager(User $manager): static
    {
        $this->managers->removeElement($manager);

        return $this;
    }

    /**
     * @return Collection<int, MatchCallSubmission>
     */
    public function getMatchCallSubmissions(): Collection
    {
        return $this->matchCallSubmissions;
    }

    public function addMatchCallSubmission(MatchCallSubmission $submission): static
    {
        if (!$this->matchCallSubmissions->contains($submission)) {
            $this->matchCallSubmissions->add($submission);
            $submission->setMatchCall($this);
        }

        return $this;
    }

    public function removeMatchCallSubmission(MatchCallSubmission $submission): static
    {
        if ($this->matchCallSubmissions->removeElement($submission)) {
            // set the owning side to null (unless already changed)
            if ($submission->getMatchCall() === $this) {
                $submission->setMatchCall(null);
            }
        }

        return $this;
    }

    public function getStrategyName(): ?string
    {
        return $this->strategyName;
    }

    public function setStrategyName(string $strategyName): static
    {
        $this->strategyName = $strategyName;

        return $this;
    }
}

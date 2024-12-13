<?php

namespace App\Entity\Project;

use App\Entity\Interface\UserOwnedInterface;
use App\Entity\User\User;
use App\Repository\Project\RewardClaimRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RewardClaimRepository::class)]
class RewardClaim implements UserOwnedInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'claims')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reward $reward = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReward(): ?Reward
    {
        return $this->reward;
    }

    public function setReward(?Reward $reward): static
    {
        $this->reward = $reward;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->owner->getId() === $user->getId();
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}

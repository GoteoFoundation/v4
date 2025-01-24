<?php

namespace App\Entity\Project;

use App\Entity\Interface\UserOwnedInterface;
use App\Entity\Trait\UserOwnedTrait;
use App\Entity\User\User;
use App\Mapping\Provider\EntityMapProvider;
use App\Repository\Project\RewardClaimRepository;
use AutoMapper\Attribute\MapProvider;
use Doctrine\ORM\Mapping as ORM;

#[MapProvider(EntityMapProvider::class)]
#[ORM\Entity(repositoryClass: RewardClaimRepository::class)]
class RewardClaim implements UserOwnedInterface
{
    use UserOwnedTrait;

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
}

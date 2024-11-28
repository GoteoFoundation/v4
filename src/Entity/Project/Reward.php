<?php

namespace App\Entity\Project;

use App\Entity\Money;
use App\Repository\Project\RewardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A ProjectReward is something the Project owner wishes to give in exchange for contributions to their Project.
 */
#[ORM\Entity(repositoryClass: RewardRepository::class)]
class Reward
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rewards')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * The minimal monetary sum to be able to claim this reward.
     */
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    /**
     * Rewards might be finite, i.e: has a limited amount of existing unitsTotal.
     */
    #[ORM\Column]
    private ?bool $hasUnits = null;

    /**
     * For finite rewards, the total amount of existing unitsTotal.
     */
    #[ORM\Column]
    private ?int $unitsTotal = null;

    /**
     * For finite rewards, the currently available amount of unitsTotal that can be claimed.
     */
    #[ORM\Column]
    private ?int $unitsAvailable = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        $this->project = $project;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): static
    {
        $this->money = $money;

        return $this;
    }

    public function hasUnits(): bool
    {
        return $this->hasUnits;
    }

    public function setHasUnits(bool $hasUnits): static
    {
        $this->hasUnits = $hasUnits;

        return $this;
    }

    public function getUnitsTotal(): ?int
    {
        return $this->unitsTotal;
    }

    public function setUnitsTotal(int $unitsTotal): static
    {
        $this->unitsTotal = $unitsTotal;

        return $this;
    }

    public function getUnitsAvailable(): ?int
    {
        return $this->unitsAvailable;
    }

    public function setUnitsAvailable(int $unitsAvailable): static
    {
        $this->unitsAvailable = $unitsAvailable;

        return $this;
    }
}

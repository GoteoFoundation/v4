<?php

namespace App\Entity\Accounting;

use ApiPlatform\Metadata as API;
use App\Entity\Money;
use App\Repository\Accounting\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AccountingTransactions represent a movement of money from one Accounting (origin) into another (target).\
 * \
 * When a transaction targets an Accounting it means that the Accounting receives it, this will add to that Accounting.
 * When a transaction originates from an Accounting the Accounting issues the transaction and it will deduct from it.\
 * \
 * AccountingTransactions are generated for each GatewayCharge in a GatewayCheckout once it becomes successful.
 */
#[API\Get()]
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The monetary value received at target and issued at origin.
     */
    #[Assert\Valid()]
    #[Assert\NotBlank()]
    #[API\ApiProperty(writable: false)]
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    /**
     * The Accounting from which the Transaction comes from.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $origin = null;

    /**
     * The Accounting where the Transaction goes to.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $target = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOrigin(): ?Accounting
    {
        return $this->origin;
    }

    public function setOrigin(?Accounting $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getTarget(): ?Accounting
    {
        return $this->target;
    }

    public function setTarget(?Accounting $target): static
    {
        $this->target = $target;

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\TransactionRepository;
use App\State\TransactionStateProcessor;
use App\Validator\GatewayName;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transactions represent a movement of money from one Accounting (origin) into another (target).\
 * \
 * When a Transaction targets an Accounting it means that the Accounting receives it, this will add to that Accounting.
 * When a Transaction originates from an Accounting the Accounting issues the Transaction and it will deduct from it.
 */
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[API\GetCollection()]
#[API\Post(processor: TransactionStateProcessor::class)]
#[API\Get()]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The monetary value to be received at target and issued at origin.
     */
    #[Assert\Valid()]
    #[Assert\NotBlank()]
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    /**
     * The Accounting issuing the money of this Transaction.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne(inversedBy: 'transactionsIssued')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $origin = null;

    /**
     * The Accounting receiving the money of this Transaction.
     */
    #[Assert\NotBlank()]
    #[ORM\ManyToOne(inversedBy: 'transactionsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $target = null;

    /**
     * The Gateway processing this Transaction.
     */
    #[GatewayName]
    #[Assert\NotBlank()]
    #[ORM\Column(length: 255)]
    private ?string $gateway = null;

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

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(string $gateway): static
    {
        $this->gateway = $gateway;

        return $this;
    }
}

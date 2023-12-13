<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Library\Economy\Monetizable;
use App\Repository\TransactionRepository;
use App\State\TransactionStateProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transactions represent a movement of funds inside the platform.\
 * When making relations between Transactions and Accountings you are effectively altering the platform's internal economy.
 * \
 * \
 * Transactions cannot be mutated after they are stored.\
 * If in need to reverse a Transaction generate a new Transaction reversing the to-be-cancelled one.
 */
#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[API\ApiResource()]
#[API\GetCollection()]
#[API\Post(processor: TransactionStateProcessor::class)]
#[API\Get()]
class Transaction extends Monetizable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The amount to be transacted.\
     * Expressed in the minor unit of the currency (cents, pennies, etc)
     */
    #[ORM\Column]
    #[Assert\NotBlank()]
    #[Assert\Positive()]
    protected int $amount = 0;

    /**
     * The currency of the transacted amount.\
     * 3-letter ISO 4217 currency code.
     */
    #[ORM\Column(length: 3)]
    #[Assert\NotBlank()]
    #[Assert\Currency()]
    protected string $currency = "";

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    private ?TransactionOrigin $origin = null;

    #[ORM\OneToOne(inversedBy: 'transaction', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    private ?TransactionTarget $target = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getOrigin(): ?TransactionOrigin
    {
        return $this->origin;
    }

    public function setOrigin(TransactionOrigin $origin): static
    {
        $this->origin = $origin;

        return $this;
    }

    public function getTarget(): ?TransactionTarget
    {
        return $this->target;
    }

    public function setTarget(TransactionTarget $target): static
    {
        $this->target = $target;

        return $this;
    }
}

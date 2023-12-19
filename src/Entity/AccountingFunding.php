<?php

namespace App\Entity;

use App\Library\Economy\Monetizable;
use App\Repository\AccountingFundingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Accounting Fundings represent how an Accounting allocates money for Transactions.
 * When a Transaction asks to take funds from an Accounting it will secure them from the Incomings list.\
 * Each time an Accounting takes funds from an Incoming, it generates a Funding to represent how much it took and to where.
 */
#[ORM\Entity(repositoryClass: AccountingFundingRepository::class)]
class AccountingFunding extends Monetizable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The amount spent by the Accounting from the Incoming.\
     * Expressed in the minor unit of the currency (cents, pennies, etc)
     */
    #[ORM\Column]
    private int $amount = 0;

    /**
     * 3-letter ISO 4217 currency code. Same as parent Accounting.
     */
    #[ORM\Column(length: 3)]
    private string $currency = "";

    /**
     * The Accounting Outgoing movement that spent the amount.
     */
    #[ORM\ManyToOne(inversedBy: 'financedBy')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AccountingOutgoing $outgoing = null;

    /**
     * The Accounting Incoming movement that provided the amount.
     */
    #[ORM\ManyToOne(inversedBy: 'spentOn')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AccountingIncoming $incoming = null;

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

    public function getOutgoing(): ?AccountingOutgoing
    {
        return $this->outgoing;
    }

    public function setOutgoing(?AccountingOutgoing $outgoing): static
    {
        $this->outgoing = $outgoing;

        return $this;
    }

    public function getIncoming(): ?AccountingIncoming
    {
        return $this->incoming;
    }

    public function setIncoming(?AccountingIncoming $incoming): static
    {
        $this->incoming = $incoming;

        return $this;
    }
}

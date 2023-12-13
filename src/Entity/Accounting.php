<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Library\Economy\Monetizable;
use App\Repository\AccountingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Accountings store the issued and received funds via Transactions.\
 * When making relations between Transactions and Accountings you are effectively altering the platform's internal economy.
 */
#[ORM\Entity(repositoryClass: AccountingRepository::class)]
#[API\GetCollection()]
#[API\Get()]
#[API\Put()]
#[API\Patch()]
class Accounting extends Monetizable
{
    public const CURRENCY_DEFAULT = 'EUR';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The available amount of this Accounting.\
     * Expressed in the minor unit of the currency (cents, pennies, etc)
     */
    #[ORM\Column(length: 255)]
    #[API\ApiProperty(writable: false)]
    protected int $amount = 0;

    /**
     * 3-letter ISO 4217 currency code
     */
    #[ORM\Column(length: 3)]
    #[Assert\NotBlank()]
    #[Assert\Currency()]
    protected string $currency = self::CURRENCY_DEFAULT;

    /**
     * Accounting Incoming movements state the Accounting holdings
     * @var Collection<int, AccountingIncoming>
     */
    #[ORM\OneToMany(mappedBy: 'accounting', targetEntity: AccountingIncoming::class)]
    #[API\ApiProperty(writable: false)]
    private Collection $incomings;

    /**
     * Accounting Outgoing movements state the Accounting expenses
     * @var Collection<int, AccountingOutgoing>
     */
    #[ORM\OneToMany(mappedBy: 'accounting', targetEntity: AccountingOutgoing::class)]
    #[API\ApiProperty(writable: false)]
    private Collection $outgoings;

    public function __construct()
    {
        $this->incomings = new ArrayCollection();
        $this->outgoings = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, AccountingIncoming>
     */
    public function getIncomings(): Collection
    {
        return $this->incomings;
    }

    public function addIncoming(AccountingIncoming $incomings): static
    {
        if (!$this->incomings->contains($incomings)) {
            $this->incomings->add($incomings);
            $incomings->setAccounting($this);
        }

        return $this;
    }

    public function removeIncoming(AccountingIncoming $incomings): static
    {
        if ($this->incomings->removeElement($incomings)) {
            // set the owning side to null (unless already changed)
            if ($incomings->getAccounting() === $this) {
                $incomings->setAccounting(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccountingOutgoing>
     */
    public function getOutgoings(): Collection
    {
        return $this->outgoings;
    }

    public function addOutgoing(AccountingOutgoing $outgoings): static
    {
        if (!$this->outgoings->contains($outgoings)) {
            $this->outgoings->add($outgoings);
            $outgoings->setAccounting($this);
        }

        return $this;
    }

    public function removeOutgoing(AccountingOutgoing $outgoings): static
    {
        if ($this->outgoings->removeElement($outgoings)) {
            // set the owning side to null (unless already changed)
            if ($outgoings->getAccounting() === $this) {
                $outgoings->setAccounting(null);
            }
        }

        return $this;
    }
}

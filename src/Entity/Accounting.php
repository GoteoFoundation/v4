<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Accountings are the receivers and the issuers of Transactions.
 */
#[ORM\Entity(repositoryClass: AccountingRepository::class)]
#[API\ApiResource]
#[API\Get()]
#[API\Put(security: 'is_granted("ACCOUNTING_EDIT", object)')]
#[API\Patch(security: 'is_granted("ACCOUNTING_EDIT", object)')]
class Accounting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The preferred currency for monetary operations.\
     * 3-letter ISO 4217 currency code.
     */
    #[Assert\Currency()]
    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToMany(mappedBy: 'origin', targetEntity: Transaction::class)]
    private Collection $transactionsIssued;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToMany(mappedBy: 'target', targetEntity: Transaction::class)]
    private Collection $transactionsReceived;

    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\Column(length: 255)]
    private ?string $ownerClass = null;

    public function __construct()
    {
        /**
         * TODO: This property must be loaded from App's configuration,
         * ideally a configuration that can be updated via a frontend, not env var only
         */
        $this->currency = 'EUR';

        $this->transactionsIssued = new ArrayCollection();
        $this->transactionsReceived = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactionsIssued(): Collection
    {
        return $this->transactionsIssued;
    }

    public function addTransactionsIssued(Transaction $transactionsIssued): static
    {
        if (!$this->transactionsIssued->contains($transactionsIssued)) {
            $this->transactionsIssued->add($transactionsIssued);
            $transactionsIssued->setOrigin($this);
        }

        return $this;
    }

    public function removeTransactionsIssued(Transaction $transactionsIssued): static
    {
        if ($this->transactionsIssued->removeElement($transactionsIssued)) {
            // set the owning side to null (unless already changed)
            if ($transactionsIssued->getOrigin() === $this) {
                $transactionsIssued->setOrigin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactionsReceived(): Collection
    {
        return $this->transactionsReceived;
    }

    public function addTransactionsReceived(Transaction $transactionsReceived): static
    {
        if (!$this->transactionsReceived->contains($transactionsReceived)) {
            $this->transactionsReceived->add($transactionsReceived);
            $transactionsReceived->setTarget($this);
        }

        return $this;
    }

    public function removeTransactionsReceived(Transaction $transactionsReceived): static
    {
        if ($this->transactionsReceived->removeElement($transactionsReceived)) {
            // set the owning side to null (unless already changed)
            if ($transactionsReceived->getTarget() === $this) {
                $transactionsReceived->setTarget(null);
            }
        }

        return $this;
    }

    public function getOwnerClass(): ?string
    {
        return $this->ownerClass;
    }

    public function setOwnerClass(string $ownerClass): static
    {
        $this->ownerClass = $ownerClass;

        return $this;
    }
}

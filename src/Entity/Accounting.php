<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Accountings are the receivers and the issuers of Transactions.
 */
#[ORM\Entity(repositoryClass: AccountingRepository::class)]
#[API\ApiResource]
#[API\Get()]
class Accounting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(mappedBy: 'target', targetEntity: Transaction::class)]
    private Collection $transactionsReceived;

    #[ORM\OneToMany(mappedBy: 'origin', targetEntity: Transaction::class)]
    private Collection $transactionsIssued;

    public function __construct()
    {
        $this->transactionsReceived = new ArrayCollection();
        $this->transactionsIssued = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
}

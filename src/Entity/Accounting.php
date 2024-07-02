<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountingRepository;
use App\Service\ApiResourceNormalizer;
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

    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\Column(length: 255)]
    private ?string $ownerClass = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?Project $project = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?Tipjar $tipjar = null;

    #[ORM\OneToMany(mappedBy: 'origin', targetEntity: AccountingTransaction::class)]
    private Collection $transactionsIssued;

    #[ORM\OneToMany(mappedBy: 'target', targetEntity: AccountingTransaction::class)]
    private Collection $transactionsReceived;

    public function __construct()
    {
        /**
         * TO-DO: This property must be loaded from App's configuration,
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

    public function getOwnerClass(): ?string
    {
        return $this->ownerClass;
    }

    public function setOwnerClass(string $ownerClass): static
    {
        // ensure ownership does not change
        if (
            $this->ownerClass !== null
            && $this->ownerClass !== $ownerClass
        ) {
            throw new \Exception('Are you trying to commit fraud? Cannot change ownership of an Accounting.');
        }

        $this->ownerClass = $ownerClass;

        return $this;
    }

    public function getOwnerResource(): string
    {
        return ApiResourceNormalizer::toResource($this->ownerClass);
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        // set the owning side of the relation if necessary
        if ($user->getAccounting() !== $this) {
            $user->setAccounting($this);
        }

        // set the owner class
        $this->setOwnerClass($user::class);

        $this->user = $user;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): static
    {
        // set the owning side of the relation if necessary
        if ($project->getAccounting() !== $this) {
            $project->setAccounting($this);
        }

        // set the owner class
        $this->setOwnerClass($project::class);

        $this->project = $project;

        return $this;
    }

    public function getTipjar(): ?Tipjar
    {
        return $this->tipjar;
    }

    public function setTipjar(Tipjar $tipjar): static
    {
        // set the owning side of the relation if necessary
        if ($tipjar->getAccounting() !== $this) {
            $tipjar->setAccounting($this);
        }

        // set the owner class
        $this->setOwnerClass($tipjar::class);

        $this->tipjar = $tipjar;

        return $this;
    }

    public function getTransactions(): Collection
    {
        $transactions = [
            ...$this->getTransactionsIssued()->toArray(),
            ...$this->getTransactionsReceived()->toArray()
        ];

        \usort($transactions, function ($a, $b) {
            return $a->getId() - $b->getId();
        });

        return new ArrayCollection($transactions);
    }

    /**
     * @return Collection<int, AccountingTransaction>
     */
    public function getTransactionsIssued(): Collection
    {
        return $this->transactionsIssued;
    }

    public function addTransactionsIssued(AccountingTransaction $transaction): static
    {
        if (!$this->transactionsIssued->contains($transaction)) {
            $this->transactionsIssued->add($transaction);
            $transaction->setOrigin($this);
        }

        return $this;
    }

    public function removeTransactionsIssued(AccountingTransaction $transaction): static
    {
        if ($this->transactionsIssued->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getOrigin() === $this) {
                $transaction->setOrigin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccountingTransaction>
     */
    public function getTransactionsReceived(): Collection
    {
        return $this->transactionsReceived;
    }

    public function addTransactionsReceived(AccountingTransaction $transaction): static
    {
        if (!$this->transactionsReceived->contains($transaction)) {
            $this->transactionsReceived->add($transaction);
            $transaction->setTarget($this);
        }

        return $this;
    }

    public function removeTransactionsReceived(AccountingTransaction $transaction): static
    {
        if ($this->transactionsReceived->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getTarget() === $this) {
                $transaction->setTarget(null);
            }
        }

        return $this;
    }
}

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
#[API\Get()]
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

    /**
     * Transactions which originated from this Accounting.
     *
     * @var Collection<int, AccountingTransaction>
     */
    #[ORM\OneToMany(mappedBy: 'origin', targetEntity: AccountingTransaction::class)]
    private Collection $transactionsOutgoing;

    /**
     * Transactions which targeted this Accounting.
     *
     * @var Collection<int, AccountingTransaction>
     */
    #[ORM\OneToMany(mappedBy: 'target', targetEntity: AccountingTransaction::class)]
    private Collection $transactionsIncoming;

    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\Column(length: 255)]
    private ?string $ownerClass = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?Project $project = null;

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?Tipjar $tipjar = null;

    public function __construct()
    {
        /*
         * TO-DO: This property must be loaded from App's configuration,
         * ideally a configuration that can be updated via a frontend, not env var only
         */
        $this->currency = 'EUR';
        $this->transactionsOutgoing = new ArrayCollection();
        $this->transactionsIncoming = new ArrayCollection();
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
     * @return Collection<int, AccountingTransaction>
     */
    public function getTransactionsOutgoing(): Collection
    {
        return $this->transactionsOutgoing;
    }

    public function addTransactionsOutgoing(AccountingTransaction $transactionsOutgoing): static
    {
        if (!$this->transactionsOutgoing->contains($transactionsOutgoing)) {
            $this->transactionsOutgoing->add($transactionsOutgoing);
            $transactionsOutgoing->setOrigin($this);
        }

        return $this;
    }

    public function removeTransactionsOutgoing(AccountingTransaction $transactionsOutgoing): static
    {
        if ($this->transactionsOutgoing->removeElement($transactionsOutgoing)) {
            // set the owning side to null (unless already changed)
            if ($transactionsOutgoing->getOrigin() === $this) {
                $transactionsOutgoing->setOrigin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, AccountingTransaction>
     */
    public function getTransactionsIncoming(): Collection
    {
        return $this->transactionsIncoming;
    }

    public function addTransactionsIncoming(AccountingTransaction $transactionsIncoming): static
    {
        if (!$this->transactionsIncoming->contains($transactionsIncoming)) {
            $this->transactionsIncoming->add($transactionsIncoming);
            $transactionsIncoming->setTarget($this);
        }

        return $this;
    }

    public function removeTransactionsIncoming(AccountingTransaction $transactionsIncoming): static
    {
        if ($this->transactionsIncoming->removeElement($transactionsIncoming)) {
            // set the owning side to null (unless already changed)
            if ($transactionsIncoming->getTarget() === $this) {
                $transactionsIncoming->setTarget(null);
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
}

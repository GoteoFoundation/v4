<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\Accounting\Transaction;
use App\Gateway\Wallet\StatementDirection;
use App\Repository\WalletStatementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource()]
#[ORM\Entity(repositoryClass: WalletStatementRepository::class)]
class WalletStatement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transaction $transaction = null;

    #[ORM\Column(enumType: StatementDirection::class)]
    private ?StatementDirection $direction = null;

    #[ORM\Embedded(class: Money::class)]
    private ?Money $balance = null;

    /**
     * The Statements from which the money in the Transaction was drawed out of.
     *
     * @var Collection<int, WalletStatement>
     */
    #[ORM\ManyToMany(targetEntity: WalletStatement::class, mappedBy: 'financesTo')]
    private Collection $financedBy;

    /**
     * The Statements that drawed money out of this Statement for their Transactions.\
     * Inverse of `financedBy`.
     *
     * @var Collection<int, WalletStatement>
     */
    #[ORM\ManyToMany(targetEntity: WalletStatement::class, inversedBy: 'financedBy')]
    private Collection $financesTo;

    public function __construct()
    {
        $this->financedBy = new ArrayCollection();
        $this->financesTo = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getDirection(): ?StatementDirection
    {
        return $this->direction;
    }

    public function setDirection(StatementDirection $direction): static
    {
        $this->direction = $direction;

        return $this;
    }

    public function getBalance(): ?Money
    {
        return $this->balance;
    }

    public function setBalance(Money $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return Collection<int, WalletStatement>
     */
    public function getFinancedBy(): Collection
    {
        return $this->financedBy;
    }

    public function addFinancedBy(WalletStatement $financedBy): static
    {
        if (!$this->financedBy->contains($financedBy)) {
            $this->financedBy->add($financedBy);
            $financedBy->addFinanceTo($this);
        }

        return $this;
    }

    public function removeFinancedBy(WalletStatement $financedBy): static
    {
        if ($this->financedBy->removeElement($financedBy)) {
            $financedBy->removeFinanceTo($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, WalletStatement>
     */
    public function getFinancesTo(): Collection
    {
        return $this->financesTo;
    }

    public function addFinanceTo(WalletStatement $finance): static
    {
        if (!$this->financesTo->contains($finance)) {
            $this->financesTo->add($finance);
        }

        return $this;
    }

    public function removeFinanceTo(WalletStatement $finance): static
    {
        $this->financesTo->removeElement($finance);

        return $this;
    }
}

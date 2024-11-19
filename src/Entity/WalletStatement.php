<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Entity\Accounting\Transaction;
use App\Gateway\Wallet\StatementDirection;
use App\Repository\WalletStatementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * WalletStatements are used by the Wallet Gateway to track available balances and money movements.\
 * \
 * Statements are relative to a Transaction, which either:\
 * \
 * a. Targets an User's Accounting, for which an incoming Statement will be created and which will hold the money until it is spent.\
 * b. Originates from an User's Accounting via the Wallet Gateway, which will create an outgoing Statement with money financed from previous incoming statements.
 */
#[API\ApiResource()]
#[ORM\Entity(repositoryClass: WalletStatementRepository::class)]
class WalletStatement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Transaction $transaction = null;

    /**
     * An `incoming` direction Statement means the Transaction saved money into the wallet.\
     * An `outgoing` direction Statement means the Transaction spent money from the wallet.
     */
    #[ORM\Column(enumType: StatementDirection::class)]
    private ?StatementDirection $direction = null;

    /**
     * For incoming Statements, the balance tells the available money.\
     * i.e: money not used yet by outgoing Statements.
     */
    #[ORM\Embedded(class: Money::class)]
    private ?Money $balance = null;

    /**
     * A collection of Financements for outgoing Statements that were made using money from this Statement.
     *
     * Only not empty for incoming Statements.
     *
     * @var Collection<int, WalletFinancement>
     */
    #[ORM\OneToMany(mappedBy: 'origin', targetEntity: WalletFinancement::class, cascade: ['persist'])]
    private Collection $financesTo;

    /**
     * A collection of Financements from incoming Statements that provided money for this Statement.
     *
     * Only not empty for outgoing Statements.
     *
     * @var Collection<int, WalletFinancement>
     */
    #[ORM\OneToMany(mappedBy: 'target', targetEntity: WalletFinancement::class, cascade: ['persist'])]
    private Collection $financedBy;

    public function __construct()
    {
        $this->financesTo = new ArrayCollection();
        $this->financedBy = new ArrayCollection();
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

    public function hasDirection(StatementDirection $direction): bool
    {
        return $this->direction === $direction;
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
     * @return Collection<int, WalletFinancement>
     */
    public function getFinancesTo(): Collection
    {
        return $this->financesTo;
    }

    public function addFinancesTo(WalletFinancement $financesTo): static
    {
        if (!$this->financesTo->contains($financesTo)) {
            $this->financesTo->add($financesTo);
            $financesTo->setOrigin($this);
        }

        return $this;
    }

    public function removeFinancesTo(WalletFinancement $financesTo): static
    {
        if ($this->financesTo->removeElement($financesTo)) {
            // set the owning side to null (unless already changed)
            if ($financesTo->getOrigin() === $this) {
                $financesTo->setOrigin(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, WalletFinancement>
     */
    public function getFinancedBy(): Collection
    {
        return $this->financedBy;
    }

    public function addFinancedBy(WalletFinancement $financedBy): static
    {
        if (!$this->financedBy->contains($financedBy)) {
            $this->financedBy->add($financedBy);
            $financedBy->setTarget($this);
        }

        return $this;
    }

    public function removeFinancedBy(WalletFinancement $financedBy): static
    {
        if ($this->financedBy->removeElement($financedBy)) {
            // set the owning side to null (unless already changed)
            if ($financedBy->getTarget() === $this) {
                $financedBy->setTarget(null);
            }
        }

        return $this;
    }
}

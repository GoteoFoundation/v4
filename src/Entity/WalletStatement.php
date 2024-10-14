<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
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
    private ?AccountingTransaction $transaction = null;

    #[ORM\Column(enumType: WalletStatementDirection::class)]
    private ?WalletStatementDirection $direction = null;

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

    public function getTransaction(): ?AccountingTransaction
    {
        return $this->transaction;
    }

    public function setTransaction(AccountingTransaction $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getDirection(): ?WalletStatementDirection
    {
        return $this->direction;
    }

    public function setDirection(WalletStatementDirection $direction): static
    {
        $this->direction = $direction;

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

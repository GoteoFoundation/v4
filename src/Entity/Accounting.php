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
    #[ORM\OneToMany(mappedBy: 'accounting', targetEntity: AccountingStatement::class)]
    private Collection $statements;

    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\Column]
    private ?int $ownerId = null;

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
        $this->statements = new ArrayCollection();
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
     * @return Collection<int, AccountingStatement>
     */
    public function getStatements(): Collection
    {
        return $this->statements;
    }

    public function addStatement(AccountingStatement $statement): static
    {
        if (!$this->statements->contains($statement)) {
            $this->statements->add($statement);
            $statement->setAccounting($this);
        }

        return $this;
    }

    public function removeStatement(AccountingStatement $statement): static
    {
        if ($this->statements->removeElement($statement)) {
            // set the owning side to null (unless already changed)
            if ($statement->getAccounting() === $this) {
                $statement->setAccounting(null);
            }
        }

        return $this;
    }

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    public function setOwnerId(int $ownerId): static
    {
        $this->ownerId = $ownerId;

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

    /**
     * The type of the owner resource.
     */
    public function getOwnerResource(): string
    {
        $classPieces = explode('\\', $this->getOwnerClass());

        return end($classPieces);
    }

     /**
     * The ID of the recorded resource.
     */
    public function getOwnerResourceId(): int
    {
        return $this->getOwnerId();
    }
}

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

    #[API\ApiProperty(writable: false)]
    #[ORM\OneToMany(mappedBy: 'accounting', targetEntity: AccountingStatement::class)]
    private Collection $statements;

    #[API\ApiProperty(writable: false, readable: false)]
    #[ORM\Column(length: 255)]
    private ?string $ownerClass = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist', 'remove'])]
    private ?Project $project = null;

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

    public function getOwnerClass(): ?string
    {
        return $this->ownerClass;
    }

    public function setOwnerClass(string $ownerClass): static
    {
        // ensure ownership does not change
        if (
            $this->ownerClass !== null &&
            $this->ownerClass !== $ownerClass
        ) {
            throw new \Exception("Are you trying to commit fraud? Cannot change ownership of an Accounting.");
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
}

<?php

namespace App\Entity\Accounting;

use App\Entity\Interface\AccountingOwnerInterface;
use App\Entity\Project;
use App\Entity\Tipjar;
use App\Entity\User;
use App\Repository\Accounting\AccountingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Accountings are the receivers and the issuers of Transactions.
 */
#[ORM\Entity(repositoryClass: AccountingRepository::class)]
class Accounting
{
    public const OWNER_CHANGE_NOT_ALLOWED = 'Are you trying to commit fraud? Cannot change ownership of an Accounting.';

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

    #[ORM\Column(length: 255)]
    private ?string $owner = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist'])]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist'])]
    private ?Project $project = null;

    #[ORM\OneToOne(mappedBy: 'accounting', cascade: ['persist'])]
    private ?Tipjar $tipjar = null;

    public function __construct()
    {
        /*
         * TO-DO: This property must be loaded from App's configuration,
         * ideally a configuration that can be updated via a frontend, not env var only
         */
        $this->currency = 'EUR';
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

    public function getOwner(): ?AccountingOwnerInterface
    {
        $owner = $this->owner;

        return $this->$owner;
    }

    public function setOwner(AccountingOwnerInterface $owner): static
    {
        if ($owner instanceof User) {
            $this->owner = 'user';

            return $this->setUser($owner);
        }

        if ($owner instanceof Project) {
            $this->owner = 'project';

            return $this->setProject($owner);
        }

        if ($owner instanceof Tipjar) {
            $this->owner = 'tipjar';

            return $this->setTipjar($owner);
        }

        throw new \Exception(sprintf('%s is not a recognized AccountingOwnerInterface', $owner::class));
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setAccounting(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getAccounting() !== $this) {
            $user->setAccounting($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): static
    {
        // unset the owning side of the relation if necessary
        if ($project === null && $this->project !== null) {
            $this->project->setAccounting(null);
        }

        // set the owning side of the relation if necessary
        if ($project !== null && $project->getAccounting() !== $this) {
            $project->setAccounting($this);
        }

        $this->project = $project;

        return $this;
    }

    public function getTipjar(): ?Tipjar
    {
        return $this->tipjar;
    }

    public function setTipjar(?Tipjar $tipjar): static
    {
        // unset the owning side of the relation if necessary
        if ($tipjar === null && $this->tipjar !== null) {
            $this->tipjar->setAccounting(null);
        }

        // set the owning side of the relation if necessary
        if ($tipjar !== null && $tipjar->getAccounting() !== $this) {
            $tipjar->setAccounting($this);
        }

        $this->tipjar = $tipjar;

        return $this;
    }
}

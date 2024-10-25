<?php

namespace App\Entity;

use App\Entity\Interface\AccountingOwnerInterface;
use App\Repository\AccountingRepository;
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

    #[ORM\Column]
    private ?int $ownerId = null;

    #[ORM\Column(length: 255)]
    private ?string $ownerClass = null;

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

    public function getOwnerId(): ?int
    {
        return $this->ownerId;
    }

    private function setOwnerId(?int $ownerId): static
    {
        // ensure ownership does not change
        if (
            $this->ownerId !== null
            && $this->ownerId !== $ownerId
        ) {
            throw new \Exception(self::OWNER_CHANGE_NOT_ALLOWED);
        }

        $this->ownerId = $ownerId;

        return $this;
    }

    public function getOwnerClass(): ?string
    {
        return $this->ownerClass;
    }

    private function setOwnerClass(string $ownerClass): static
    {
        // ensure ownership does not change
        if (
            $this->ownerClass !== null
            && $this->ownerClass !== $ownerClass
        ) {
            throw new \Exception(self::OWNER_CHANGE_NOT_ALLOWED);
        }

        $this->ownerClass = $ownerClass;

        return $this;
    }

    public function setOwner(AccountingOwnerInterface $owner): static
    {
        $this->setOwnerId($owner->getId());
        $this->setOwnerClass($owner::class);

        return $this;
    }
}

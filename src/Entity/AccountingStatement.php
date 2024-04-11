<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountingStatementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * An AccountingStatement represents a change in the balance of an Accounting.\
 * \
 * When money is received, an statement holds it as available.
 * When money is issued, an statement draws available money from previous statements.\
 * \
 * The sum of available money in the statements is the available money at that Accounting.
 */
#[API\ApiResource]
#[ORM\Entity(repositoryClass: AccountingStatementRepository::class)]
class AccountingStatement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The money held in this statement.
     */
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    /**
     * The direction of this statement.\
     * `incoming` means the transaction was received and the money is available.\
     * `outgoing` means the transaction was issued and the money is an expenditure.
     */
    #[ORM\Column()]
    private ?AccountingStatementDirection $direction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMoney(): ?Money
    {
        return $this->money;
    }

    public function setMoney(Money $money): static
    {
        $this->money = $money;

        return $this;
    }

    public function getDirection(): ?AccountingStatementDirection
    {
        return $this->direction;
    }

    public function setDirection(AccountingStatementDirection $direction): static
    {
        $this->direction = $direction;

        return $this;
    }
}

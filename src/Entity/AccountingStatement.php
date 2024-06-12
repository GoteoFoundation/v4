<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountingStatementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * An AccountingStatement represents a change in the balance of an Accounting.\
 * \
 * When money is received, an Statement holds it as available.
 * When money is issued, an Statement draws available money from previous statements.\
 * \
 * The sum of available money in the statements is the available money at that Accounting.
 */
#[API\GetCollection(
    uriTemplate: '/accountings/{id}/statements',
    uriVariables: [
        'id' => new API\Link(fromClass: Accounting::class, toProperty: 'accounting'),
    ],
)]
#[API\Get(
    uriTemplate: '/accountings/{id}/statements/{statementId}',
    uriVariables: [
        'id' => new API\Link(fromClass: Accounting::class, toProperty: 'accounting'),
        'statementId' => new API\Link(fromClass: AccountingStatement::class),
    ],
)]
#[ORM\Entity(repositoryClass: AccountingStatementRepository::class)]
class AccountingStatement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[API\ApiProperty(readable: false)]
    #[ORM\ManyToOne(inversedBy: 'statements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Accounting $accounting = null;

    /**
     * The money held in this Statement.
     */
    #[ORM\Embedded(class: Money::class)]
    private ?Money $money = null;

    /**
     * The direction of this Statement.\
     * `incoming` means the transaction was received and the money is available.\
     * `outgoing` means the transaction was issued and the money is an expenditure.
     */
    #[ORM\Column()]
    private ?AccountingStatementDirection $direction = null;

    /**
     * The Transaction represented in this Statement.
     */
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?AccountingTransaction $transaction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAccounting(): ?Accounting
    {
        return $this->accounting;
    }

    public function setAccounting(?Accounting $accounting): static
    {
        $this->accounting = $accounting;

        return $this;
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

    public function getTransaction(): ?AccountingTransaction
    {
        return $this->transaction;
    }

    public function setTransaction(AccountingTransaction $transaction): static
    {
        $this->transaction = $transaction;

        return $this;
    }
}

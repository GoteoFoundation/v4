<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\TransactionTargetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionTargetRepository::class)]
class TransactionTarget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[API\ApiProperty(readable: false)]
    private ?int $id = null;

    /**
     * The Accounting receiving the Transaction amount.
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank()]
    private ?Accounting $accounting = null;

    /**
     * The Accounting Incoming movement generated by this Transaction.
     */
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[API\ApiProperty(writable: false)]
    private ?AccountingIncoming $incoming = null;

    #[ORM\OneToOne(mappedBy: 'target', cascade: ['persist', 'remove'])]
    #[API\ApiProperty(writable: false, readable: false)]
    private ?Transaction $transaction = null;

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

    public function getIncoming(): ?AccountingIncoming
    {
        return $this->incoming;
    }

    public function setIncoming(AccountingIncoming $incoming): static
    {
        $this->incoming = $incoming;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): static
    {
        // set the owning side of the relation if necessary
        if ($transaction->getTarget() !== $this) {
            $transaction->setTarget($this);
        }

        $this->transaction = $transaction;

        return $this;
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Accountings are the senders and receivers of Transactions.
 */
#[ORM\Entity(repositoryClass: AccountingRepository::class)]
#[API\ApiResource]
#[API\Get()]
class Accounting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}

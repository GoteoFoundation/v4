<?php

namespace App\Entity;

use ApiPlatform\Metadata as API;
use App\Repository\AccountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Accounts are the senders and receivers of Transactions.
 */
#[ORM\Entity(repositoryClass: AccountRepository::class)]
#[API\ApiResource]
#[API\Get()]
class Account
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

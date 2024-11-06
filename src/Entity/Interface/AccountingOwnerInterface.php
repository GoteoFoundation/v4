<?php

namespace App\Entity\Interface;

use App\Entity\Accounting\Accounting;

interface AccountingOwnerInterface
{
    public function getId(): ?int;

    public function getAccounting(): ?Accounting;

    public function setAccounting(Accounting $accounting): static;
}

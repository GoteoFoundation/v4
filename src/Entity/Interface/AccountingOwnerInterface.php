<?php

namespace App\Entity\Interface;

use App\Entity\Accounting;

interface AccountingOwnerInterface extends ApiResource
{
    public function getId(): ?int;

    public function getAccounting(): ?Accounting;

    public function setAccounting(Accounting $accounting): static;
}

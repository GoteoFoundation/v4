<?php

namespace App\ApiResource\Accounting;

use App\Entity\User\User;
use App\Entity\Project\Project;
use App\Entity\Tipjar;

class AccountingOwner
{
    /**
     * Full-body of the owner data.
     * @var User|Project|Tipjar
     */
    public object $data;

    /**
     * The schema type of the owner.
     */
    public string $schema;
}

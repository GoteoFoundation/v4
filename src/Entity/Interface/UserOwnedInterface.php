<?php

namespace App\Entity\Interface;

use App\Entity\User\User;

interface UserOwnedInterface
{
    public function getOwner(): ?User;

    public function isOwnedBy(User $user): bool;
}

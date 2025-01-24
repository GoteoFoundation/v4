<?php

namespace App\Security\Voter;

use App\ApiResource\User\UserApiResource;
use App\Entity\Interface\UserOwnedInterface;
use App\Entity\User\User;

trait UserOwnedVoterTrait
{
    /**
     * Determines if the given User is the owner of the resource.
     *
     * @param object $subject A resource that might or might not be owned by the User
     * @param ?User  $user    The User to check ownership against
     *
     * @return bool `false` if ownership could not be guaranteed
     */
    public function isOwnerOf(object $subject, ?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        if ($subject instanceof UserOwnedInterface) {
            return $subject->isOwnedBy($user);
        }

        if (!\property_exists($subject, 'owner')) {
            return false;
        }

        if ($subject->owner instanceof UserApiResource) {
            return $subject->owner->id === $user->getId();
        }

        return false;
    }
}

<?php

namespace App\Security\Voter;

use App\Entity\Interface\UserOwnedInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserOwnedVoter extends Voter
{
    public const OWNED = 'USER_OWNED';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::OWNED])) {
            return false;
        }

        return $subject instanceof UserOwnedInterface;
    }

    /**
     * @param User|UserOwnedInterface $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $subject->isOwnedBy($user);
    }
}

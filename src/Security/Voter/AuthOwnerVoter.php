<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthOwnerVoter extends Voter
{
    public const OWNER = 'AUTH_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::OWNER])
            && $subject !== null
            && method_exists($subject, 'isOwnedBy');
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        return $subject->isOwnedBy($user);

        return false;
    }
}

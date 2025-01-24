<?php

namespace App\Security\Voter;

use App\ApiResource\Accounting\AccountingApiResource;
use App\Entity\User\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountingVoter extends Voter
{
    use UserOwnedVoterTrait;

    public const EDIT = 'ACCOUNTING_EDIT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT])
            && $subject instanceof AccountingApiResource;
    }

    /**
     * @param AccountingApiResource $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $user->hasRoles(['ROLE_ADMIN'])
                    || $this->isOwnerOf($subject, $user);
                break;
        }

        return false;
    }
}

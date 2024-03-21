<?php

namespace App\Library\Benzina\Pump;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UsersPump implements PumpInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(mixed $data): bool
    {
        if (!\is_array($data) || !\array_key_exists(0, $data)) {
            return false;
        }

        if (!\array_key_exists('email', $data[0]) && !\array_key_exists('password', $data[0])) {
            return false;
        }

        return true;
    }

    public function process(mixed $data): void
    {
        foreach ($data as $key => $userData) {

            $user = new User;
            $user->setUsername($this->normalizeUsername($userData['id']));
            $user->setPassword($userData['password'] ?? "");
            $user->setEmail($userData['email']);
            $user->setName($userData['name']);
            $user->setActive(false);
            $user->setConfirmed(false);
            $user->setMigrated(true);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    private function normalizeUsername(string $username): ?string
    {
        // Only lowercase a-z, numbers and underscore in usernames
        $username = \preg_replace('/[^a-z0-9_]/', '_', \strtolower($username));

        // Min length 4
        $username = \str_pad($username, 4, '_');

        // Max length 30
        $username = \substr($username, 0, 30);

        if (strlen(str_replace('_', '', $username)) < 1) {
            return null;
        }

        return $username;
    }
}

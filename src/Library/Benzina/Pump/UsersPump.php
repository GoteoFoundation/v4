<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\User;
use App\Library\Benzina\Pump\Trait\ArrayPumpTrait;
use App\Library\Benzina\Pump\Trait\DoctrinePumpTrait;
use Doctrine\ORM\EntityManagerInterface;

class UsersPump extends AbstractPump implements PumpInterface
{
    use ArrayPumpTrait;
    use DoctrinePumpTrait;

    public const USER_KEYS = [
        'id',
        'name',
        'location',
        'email',
        'password',
        'gender',
        'birthyear',
        'entity_type',
        'legal_entity',
        'origin_register',
        'about',
        'keywords',
        'active',
        'avatar',
        'contribution',
        'twitter',
        'facebook',
        'instagram',
        'identica',
        'linkedin',
        'amount',
        'num_patron',
        'num_patron_active',
        'worth',
        'created',
        'modified',
        'token',
        'rememberme',
        'hide',
        'confirmed',
        'lang',
        'node',
        'num_invested',
        'num_owned',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function supports(mixed $batch): bool
    {
        if (!\is_array($batch) || !\is_array($batch[0])) {
            return false;
        }

        return $this->hasAllKeys($batch[0], self::USER_KEYS);
    }

    public function pump(mixed $batch): void
    {
        $batch = $this->skipPumped($batch, 'id', User::class, 'migratedId');

        foreach ($batch as $key => $record) {
            $user = new User();
            $user->setAccounting(new Accounting());
            $user->setUsername($this->getUsername($record));
            $user->setPassword($record['password'] ?? '');
            $user->setEmail($record['email']);
            $user->setEmailConfirmed(false);
            $user->setName($record['name']);
            $user->setActive(false);
            $user->setMigrated(true);
            $user->setMigratedId($record['id']);

            $accounting = new Accounting();
            $accounting->setUser($user);

            $this->entityManager->persist($user);
            $this->entityManager->persist($accounting);
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

    private function getUsername(array $record): string
    {
        $username = $this->normalizeUsername($record['id']);

        if (!$username) {
            $username = $this->normalizeUsername($record['email']);
        }

        return $username;
    }
}

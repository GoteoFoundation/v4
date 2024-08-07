<?php

namespace App\Library\Benzina\Pump;

use App\Entity\Accounting;
use App\Entity\User;
use App\Library\Benzina\Pump\Trait\ArrayPumpTrait;
use App\Library\Benzina\Pump\Trait\ProgressivePumpTrait;
use Doctrine\ORM\EntityManagerInterface;

class UsersPump extends AbstractPump implements PumpInterface
{
    use ArrayPumpTrait;
    use ProgressivePumpTrait;

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
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(mixed $data): bool
    {
        if (!\is_array($data) || !\is_array($data[0])) {
            return false;
        }

        return $this->hasAllKeys($data[0], self::USER_KEYS);
    }

    public function process(mixed $data): void
    {
        $pumped = $this->getPumped(User::class, $data, ['migratedReference' => 'id']);

        foreach ($data as $key => $record) {
            if ($this->isPumped($record, $pumped, ['migratedReference' => 'id'])) {
                continue;
            }

            $user = new User();
            $user->setAccounting(new Accounting());
            $user->setUsername($this->getUsername($record));
            $user->setPassword($record['password'] ?? '');
            $user->setEmail($record['email']);
            $user->setEmailConfirmed(false);
            $user->setName($record['name']);
            $user->setActive(false);
            $user->setMigrated(true);
            $user->setMigratedReference($record['id']);

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

    private function getUsername(array $data): string
    {
        $username = $this->normalizeUsername($data['id']);

        if (!$username) {
            $username = $this->normalizeUsername($data['email']);
        }

        return $username;
    }
}

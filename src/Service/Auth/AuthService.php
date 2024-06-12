<?php

namespace App\Service\Auth;

use App\Entity\User;
use App\Entity\UserToken;

class AuthService
{
    private const TOKEN_HASH_ALGO = 'sha256';

    private array $config;

    public function __construct(
        private string $appSecret
    ) {
    }

    /**
     * @return array{CORS_ALLOW_ORIGIN: string, SESSION_LIFETIME: int}
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function generateUserToken(User $user, AuthTokenType $type): UserToken
    {
        $token = new UserToken();

        $token->setOwner($user);
        $token->setToken(sprintf('%s%s', $type->value, hash(
            self::TOKEN_HASH_ALGO,
            join('', [
                microtime(true),
                $this->appSecret,
                random_bytes(32),
                $user->getUserIdentifier(),
            ])
        )));

        return $token;
    }
}

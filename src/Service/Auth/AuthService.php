<?php

namespace App\Service\Auth;

use App\Entity\AccessToken;
use App\Entity\User;

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

    public function generateAccessToken(User $user, AccessTokenType $type): AccessToken
    {
        $token = new AccessToken;

        $token->setOwnedBy($user);
        $token->setToken(sprintf('%s_%s', $type->value, hash(
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

<?php

namespace App\Security\Hasher;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

/**
 * Ported from Goteo v3 src/Goteo/Library/Password.php
 * @see Goteo v3
 */
class LegacyPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return sha1($plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return true;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        if (!$this->isSHA1($plainPassword)) {
            // For compatibility, all passwords will be pre-encoded with a SHA-1 algorithm
            $comparePassword = sha1($plainPassword);
        }

        if ($this->isOldBcrypt($hashedPassword)) {
            // For compatibility with the github version
            return $hashedPassword === crypt($plainPassword, $hashedPassword);
        }

        // Old database passwords are encoded in plain SHA-1
        if ($this->isSHA1($hashedPassword)) {
            return $comparePassword === $hashedPassword;
        }

        return password_verify($comparePassword, $hashedPassword);
    }

    private function isSHA1(string $str): bool
    {
        return (bool) preg_match('/^[0-9a-f]{40}$/i', $str);
    }

    private function isOldBcrypt(string $str): bool
    {
        return strpos($str, '$1$') === 0;
    }
}

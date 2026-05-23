<?php

declare(strict_types=1);

namespace App\Core\Security;

use RuntimeException;

final class CsrfGuard
{
    private const TOKEN_KEY    = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('Session must be started before using CsrfGuard.');
        }

        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = self::generate();
        }

        return $_SESSION[self::TOKEN_KEY];
    }

    public static function validate(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $stored = $_SESSION[self::TOKEN_KEY] ?? '';

        // hash_equals защищает от timing-атак
        return hash_equals($stored, $token);
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[self::TOKEN_KEY] = self::generate();
        }
    }

    private static function generate(): string
    {
        return bin2hex(random_bytes(self::TOKEN_LENGTH));
    }
}
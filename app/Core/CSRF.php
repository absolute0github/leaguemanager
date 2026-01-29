<?php

namespace App\Core;

class CSRF
{
    private static Session $session;

    public static function initialize(): void
    {
        self::$session = Session::getInstance();
    }

    /**
     * Generate a CSRF token
     */
    public static function generate(): string
    {
        if (!self::$session->has('csrf_token')) {
            $token = bin2hex(random_bytes((int)$_ENV['CSRF_TOKEN_LENGTH'] / 2));
            self::$session->set('csrf_token', $token);
        }

        return self::$session->get('csrf_token');
    }

    /**
     * Verify CSRF token
     */
    public static function verify(string $token): bool
    {
        $sessionToken = self::$session->get('csrf_token');

        if (!$sessionToken || !hash_equals($sessionToken, $token)) {
            return false;
        }

        // Regenerate token after verification
        self::$session->delete('csrf_token');
        return true;
    }

    /**
     * Get HTML input field with token
     */
    public static function getField(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

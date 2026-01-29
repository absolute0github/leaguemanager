<?php

namespace App\Core;

class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        // Configure session settings before starting
        if (session_status() === PHP_SESSION_NONE) {
            // Set cookie parameters with SameSite attribute for modern browsers
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => ($_ENV['SESSION_COOKIE_SECURE'] ?? 'false') === 'true',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            ini_set('session.use_strict_mode', 1);
            session_start();
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set a session value
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if a key exists in session
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Delete a session value
     */
    public function delete(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Clear all session data
     */
    public function flush(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    /**
     * Regenerate session ID (after login)
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        return session_id();
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}

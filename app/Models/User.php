<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Authenticate user with username and password
     */
    public function authenticate(string $username, string $password): ?array
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return null;
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            return null;
        }

        // Check if account is locked out
        if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
            return null;
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            // Increment failed login attempts
            $this->incrementFailedAttempts($user['id']);
            return null;
        }

        // Reset failed attempts on successful login
        $this->resetFailedAttempts($user['id']);

        // Update last login
        $this->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        return $user;
    }

    /**
     * Hash a password
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, [
            'cost' => (int)$_ENV['PASSWORD_HASH_COST']
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     */
    public function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, [
            'cost' => (int)$_ENV['PASSWORD_HASH_COST']
        ]);
    }

    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts(int $userId): void
    {
        $user = $this->find($userId);

        if (!$user) {
            return;
        }

        $attempts = $user['failed_login_attempts'] + 1;
        $maxAttempts = (int)$_ENV['LOGIN_MAX_ATTEMPTS'];
        $lockoutDuration = (int)$_ENV['LOCKOUT_DURATION'];

        if ($attempts >= $maxAttempts) {
            // Lock account
            $lockoutUntil = date('Y-m-d H:i:s', time() + $lockoutDuration);
            $this->update($userId, [
                'failed_login_attempts' => $attempts,
                'lockout_until' => $lockoutUntil
            ]);
        } else {
            $this->update($userId, ['failed_login_attempts' => $attempts]);
        }
    }

    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts(int $userId): void
    {
        $this->update($userId, [
            'failed_login_attempts' => 0,
            'lockout_until' => null
        ]);
    }

    /**
     * Check if account is locked out
     */
    public function isLockedOut(int $userId): bool
    {
        $user = $this->find($userId);

        if (!$user || !$user['lockout_until']) {
            return false;
        }

        return strtotime($user['lockout_until']) > time();
    }

    /**
     * Unlock account
     */
    public function unlock(int $userId): bool
    {
        return $this->update($userId, [
            'failed_login_attempts' => 0,
            'lockout_until' => null
        ]);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role): array
    {
        return $this->findAllBy('role', $role);
    }

    /**
     * Get active users
     */
    public function getActive(): array
    {
        return $this->findAllBy('status', 'active');
    }

    /**
     * Create user with hashed password
     */
    public function createUser(array $data): int|bool
    {
        if (isset($data['password'])) {
            $data['password_hash'] = $this->hashPassword($data['password']);
            unset($data['password']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'pending';

        return $this->create($data);
    }

    /**
     * Change user password
     */
    public function changePassword(int $userId, string $newPassword): bool
    {
        $hash = $this->hashPassword($newPassword);
        return $this->update($userId, [
            'password_hash' => $hash,
            'password_reset_token' => null,
            'password_reset_expires' => null
        ]);
    }

    /**
     * Generate password reset token
     */
    public function generatePasswordResetToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $this->update($userId, [
            'password_reset_token' => $token,
            'password_reset_expires' => $expires
        ]);

        return $token;
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken(int $userId, string $token): bool
    {
        $user = $this->find($userId);

        if (!$user || $user['password_reset_token'] !== $token) {
            return false;
        }

        if (!$user['password_reset_expires'] || strtotime($user['password_reset_expires']) < time()) {
            return false;
        }

        return true;
    }

    /**
     * Generate email verification token (6-digit code)
     */
    public function generateEmailVerificationToken(int $userId): string
    {
        // Generate a 6-digit verification code
        $token = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $this->update($userId, ['email_verification_token' => $token]);
        return $token;
    }

    /**
     * Verify email token
     */
    public function verifyEmailToken(int $userId, string $token): bool
    {
        $user = $this->find($userId);

        if (!$user || $user['email_verification_token'] !== $token) {
            return false;
        }

        // Mark email as verified
        $this->update($userId, [
            'email_verified' => true,
            'email_verification_token' => null,
            'status' => 'active'
        ]);

        return true;
    }
}

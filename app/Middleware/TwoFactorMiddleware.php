<?php

namespace App\Middleware;

use App\Core\Session;

class TwoFactorMiddleware
{
    private Session $session;

    public function __construct()
    {
        $this->session = Session::getInstance();
    }

    /**
     * Check if 2FA is verified for the user
     * Returns true if 2FA is verified or not required
     */
    public function verify(): bool
    {
        // Only enforce for superuser and admin roles
        $role = $this->session->get('role');

        if (!in_array($role, ['superuser', 'admin'])) {
            return true;
        }

        // Check if 2FA verification flag is set
        return (bool)$this->session->get('two_factor_verified', false);
    }

    /**
     * Check if verification is required
     */
    public function isRequired(): bool
    {
        $role = $this->session->get('role');
        return in_array($role, ['superuser', 'admin']);
    }

    /**
     * Mark 2FA as verified
     */
    public function markVerified(): void
    {
        $this->session->set('two_factor_verified', true);
        $this->session->set('two_factor_verified_at', time());
    }

    /**
     * Check if 2FA verification has expired (2 hour timeout)
     */
    public function isVerificationExpired(): bool
    {
        $verifiedAt = $this->session->get('two_factor_verified_at');

        if (!$verifiedAt) {
            return true;
        }

        $timeout = (int)$_ENV['SESSION_TIMEOUT'];
        return (time() - $verifiedAt) > $timeout;
    }

    /**
     * Invalidate 2FA verification
     */
    public function invalidate(): void
    {
        $this->session->delete('two_factor_verified');
        $this->session->delete('two_factor_verified_at');
    }

    /**
     * Get pending user ID (user who hasn't completed 2FA yet)
     */
    public function getPendingUserId(): ?int
    {
        return $this->session->get('pending_user_id');
    }

    /**
     * Set pending user ID (user in 2FA verification flow)
     */
    public function setPendingUserId(int $userId): void
    {
        $this->session->set('pending_user_id', $userId);
    }

    /**
     * Clear pending user ID
     */
    public function clearPendingUserId(): void
    {
        $this->session->delete('pending_user_id');
    }
}

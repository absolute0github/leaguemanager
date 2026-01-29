<?php

namespace App\Models;

use App\Core\Model;

class TwoFactorAuth extends Model
{
    protected string $table = 'two_factor_auth';

    /**
     * Get 2FA record for a user
     */
    public function getByUserId(int $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    /**
     * Check if user has 2FA enabled
     */
    public function isEnabled(int $userId): bool
    {
        $record = $this->getByUserId($userId);
        return $record && (bool)$record['enabled'];
    }

    /**
     * Enable 2FA for a user
     */
    public function enable(int $userId, string $secret, array $backupCodes): bool
    {
        $record = $this->getByUserId($userId);

        $data = [
            'secret' => $secret,
            'enabled' => true,
            'backup_codes' => json_encode($backupCodes),
            'last_used' => null
        ];

        if ($record) {
            return $this->update($record['id'], $data);
        } else {
            $data['user_id'] = $userId;
            return (bool)$this->create($data);
        }
    }

    /**
     * Disable 2FA for a user
     */
    public function disable(int $userId): bool
    {
        $record = $this->getByUserId($userId);

        if (!$record) {
            return false;
        }

        return $this->update($record['id'], [
            'enabled' => false,
            'secret' => null,
            'backup_codes' => null
        ]);
    }

    /**
     * Get backup codes for a user
     */
    public function getBackupCodes(int $userId): array
    {
        $record = $this->getByUserId($userId);

        if (!$record || !$record['backup_codes']) {
            return [];
        }

        return json_decode($record['backup_codes'], true) ?? [];
    }

    /**
     * Remove a used backup code
     */
    public function removeBackupCode(int $userId, string $usedCode): bool
    {
        $record = $this->getByUserId($userId);

        if (!$record) {
            return false;
        }

        $codes = $this->getBackupCodes($userId);

        // Remove the code (this is the hashed version)
        $codes = array_filter($codes, function($code) use ($usedCode) {
            return $code !== $usedCode;
        });

        return $this->update($record['id'], [
            'backup_codes' => json_encode(array_values($codes))
        ]);
    }

    /**
     * Update last used time
     */
    public function updateLastUsed(int $userId): bool
    {
        $record = $this->getByUserId($userId);

        if (!$record) {
            return false;
        }

        return $this->update($record['id'], [
            'last_used' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get secret for a user
     */
    public function getSecret(int $userId): ?string
    {
        $record = $this->getByUserId($userId);
        return $record ? $record['secret'] : null;
    }
}

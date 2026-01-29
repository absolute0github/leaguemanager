<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use OTPHP\TOTP;

class TwoFactorService
{
    /**
     * Generate a new TOTP secret
     */
    public static function generateSecret(): string
    {
        $totp = TOTP::create();
        return $totp->getSecret();
    }

    /**
     * Create QR code for authenticator app
     */
    public static function generateQrCode(string $secret, string $username, string $issuer = 'IVL Baseball League'): string
    {
        $totp = TOTP::createFromSecret($secret);
        $totp->setLabel($username);
        $totp->setIssuer($issuer);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCode = $writer->writeString($totp->getProvisioningUri());

        // Strip XML declaration and trim whitespace for clean HTML embedding
        $qrCode = preg_replace('/<\?xml[^?]*\?>/', '', $qrCode);
        $qrCode = trim($qrCode);

        return $qrCode;
    }

    /**
     * Verify a TOTP code
     */
    public static function verifyCode(string $secret, string $code, int $timeWindow = 1): bool
    {
        try {
            $totp = TOTP::createFromSecret($secret);
            return $totp->verify($code, null, $timeWindow);
        } catch (\Exception $e) {
            error_log("2FA verification error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate 10 backup codes
     */
    public static function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 10; $i++) {
            $codes[] = self::generateBackupCode();
        }
        return $codes;
    }

    /**
     * Generate a single backup code
     */
    private static function generateBackupCode(): string
    {
        // Format: XXXX-XXXX-XXXX (12 random characters with dashes)
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';

        for ($i = 0; $i < 12; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
            if (($i + 1) % 4 === 0 && $i < 11) {
                $code .= '-';
            }
        }

        return $code;
    }

    /**
     * Hash a backup code for storage
     */
    public static function hashBackupCode(string $code): string
    {
        return hash('sha256', $code);
    }

    /**
     * Verify a backup code
     */
    public static function verifyBackupCode(string $code, array $hashedCodes): bool
    {
        $codeHash = self::hashBackupCode($code);
        return in_array($codeHash, $hashedCodes, true);
    }

    /**
     * Generate and hash backup codes
     */
    public static function generateAndHashBackupCodes(): array
    {
        $codes = self::generateBackupCodes();
        $hashed = [];

        foreach ($codes as $code) {
            $hashed[] = self::hashBackupCode($code);
        }

        return [
            'codes' => $codes,      // Show user once
            'hashed' => $hashed     // Store in database
        ];
    }

    /**
     * Remove used backup code from list
     */
    public static function removeUsedBackupCode(array $hashedCodes, string $usedCode): array
    {
        $usedHash = self::hashBackupCode($usedCode);

        return array_filter($hashedCodes, function($hash) use ($usedHash) {
            return $hash !== $usedHash;
        });
    }

    /**
     * Get TOTP for current time (for testing)
     */
    public static function getCurrentCode(string $secret): string
    {
        try {
            $totp = TOTP::createFromSecret($secret);
            return $totp->now();
        } catch (\Exception $e) {
            error_log("Error getting current TOTP code: " . $e->getMessage());
            return '';
        }
    }
}

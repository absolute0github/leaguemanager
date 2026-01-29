<?php

/**
 * Clear old email verification tokens
 * Run this after updating to the new 6-digit verification code system
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $_ENV[$key] = $value;
    }
}

use App\Core\Database;

try {
    $db = Database::getInstance();

    // Clear all old verification tokens (they'll be regenerated as 6-digit codes)
    $result = $db->execute(
        "UPDATE users SET email_verification_token = NULL WHERE email_verified = 0"
    );

    if ($result) {
        echo "✓ Cleared old verification tokens\n";
        echo "Users with pending verifications will receive new 6-digit codes on next login attempt\n";
    } else {
        echo "✗ Failed to clear tokens\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

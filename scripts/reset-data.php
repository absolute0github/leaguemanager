<?php

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\Database;

// Load environment variables
loadEnv(BASE_PATH . '/.env');

$db = Database::getInstance();

echo "Resetting player and parent data...\n";

// Delete all player and parent records (but keep superuser)
$db->execute('DELETE FROM parents WHERE 1=1');
$db->execute('DELETE FROM players WHERE 1=1');

echo "✓ Cleared players and parents\n\n";

function loadEnv(string $envFile): void
{
    if (!file_exists($envFile)) {
        die(".env file not found: $envFile\n");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        if (strpos($line, '=') !== false) {
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
}

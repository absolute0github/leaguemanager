<?php

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\Database;

// Load environment variables
loadEnv(BASE_PATH . '/.env');

$db = Database::getInstance();

// Get player count
$result = $db->fetchOne('SELECT COUNT(*) as count FROM players');
echo "Total players in database: " . ($result['count'] ?? 0) . "\n\n";

// Get first 5 players
$players = $db->fetchAll('SELECT id, first_name, last_name, email, age_group FROM players LIMIT 10');
echo "First 10 players:\n";
foreach ($players as $player) {
    echo "- " . $player['first_name'] . " " . $player['last_name'] . " (" . $player['email'] . ") - " . $player['age_group'] . "\n";
}

// Get parents count
$result = $db->fetchOne('SELECT COUNT(*) as count FROM parents');
echo "\nTotal parents in database: " . ($result['count'] ?? 0) . "\n";

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

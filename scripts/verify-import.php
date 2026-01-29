<?php

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

use App\Core\Database;

// Load environment variables
loadEnv(BASE_PATH . '/.env');

$db = Database::getInstance();

echo "========================================\n";
echo "Data Import Verification\n";
echo "========================================\n\n";

// Get player count
$result = $db->fetchOne('SELECT COUNT(*) as count FROM players');
echo "Total Players: " . ($result['count'] ?? 0) . "\n\n";

// Get distribution by status
$result = $db->fetchAll('SELECT registration_status, COUNT(*) as count FROM players GROUP BY registration_status');
echo "Players by Status:\n";
foreach ($result as $row) {
    echo "  " . $row['registration_status'] . ": " . $row['count'] . "\n";
}

// Get distribution by age group
echo "\nPlayers by Age Group:\n";
$result = $db->fetchAll('SELECT age_group, COUNT(*) as count FROM players WHERE age_group IS NOT NULL GROUP BY age_group ORDER BY age_group');
foreach ($result as $row) {
    echo "  " . $row['age_group'] . ": " . $row['count'] . "\n";
}

// Get parent count
echo "\nTotal Parents: ";
$result = $db->fetchOne('SELECT COUNT(*) as count FROM parents');
echo ($result['count'] ?? 0) . "\n";

// Get payment records
echo "Total Payments: ";
$result = $db->fetchOne('SELECT COUNT(*) as count FROM player_payments');
echo ($result['count'] ?? 0) . "\n";

// Get payment plan info
echo "\nPayment Plans:\n";
$result = $db->fetchAll('SELECT name, age_group, COUNT(*) as count FROM payment_plans GROUP BY name, age_group LIMIT 10');
foreach ($result as $row) {
    echo "  " . $row['name'] . " (" . $row['age_group'] . "): " . $row['count'] . " records\n";
}

echo "\n========================================\n";
echo "Verification Complete\n";
echo "========================================\n";

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

<?php

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

use App\Services\CSVImportService;

// Load environment variables first
loadEnv(BASE_PATH . '/.env');

$importService = new CSVImportService();

$csvFile = BASE_PATH . '/player-tryout-data.csv';
$rows = $importService->readCsv($csvFile);

if (!empty($rows)) {
    // Show first row structure
    $firstRow = $rows[0];
    echo "CSV Headers and First Row:\n";
    echo "================================\n\n";

    foreach ($firstRow as $key => $value) {
        echo "$key: " . substr($value, 0, 50) . "\n";
    }

    echo "\n================================\n";
    echo "Total rows: " . count($rows) . "\n";
    echo "\nChecking email addresses:\n";

    $withEmail = 0;
    $withoutEmail = 0;
    foreach ($rows as $row) {
        if (!empty(trim($row['Player Email Address'] ?? ''))) {
            $withEmail++;
        } else {
            $withoutEmail++;
        }
    }

    echo "With email: $withEmail\n";
    echo "Without email: $withoutEmail\n";
}

function loadEnv(string $envFile): void
{
    if (!file_exists($envFile)) {
        die(".env file not found: $envFile\n");
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$key] = $value;
        }
    }
}

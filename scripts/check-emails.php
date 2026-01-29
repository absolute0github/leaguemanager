<?php

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

use App\Services\CSVImportService;

// Load environment variables
loadEnv(BASE_PATH . '/.env');

$importService = new CSVImportService();

$csvFile = BASE_PATH . '/player-tryout-data.csv';
$rows = $importService->readCsv($csvFile);

echo "Analyzing emails in tryout data:\n\n";

$withEmail = 0;
$withoutEmail = 0;
$emptyOrWhitespace = 0;

$skipReasons = [];

foreach ($rows as $index => $row) {
    $email = trim($row['Player Email Address'] ?? '');

    if (empty($email)) {
        $withoutEmail++;
        if (empty($skipReasons['empty'])) $skipReasons['empty'] = [];
        $skipReasons['empty'][] = $index;
    } elseif (strlen($email) === 0) {
        $emptyOrWhitespace++;
    } else {
        $withEmail++;
    }

    // Show first few
    if ($index < 10 || ($index > 195 && $index < 210) || ($index > 235 && $index < 245)) {
        echo "[Row $index] Email: '" . $email . "' (" . strlen($email) . " chars), Name: " . ($row['Player Name'] ?? 'N/A') . "\n";
    }
}

echo "\nSummary:\n";
echo "With email: $withEmail\n";
echo "Without email: $withoutEmail\n";
echo "Empty/Whitespace: $emptyOrWhitespace\n";

if (!empty($skipReasons['empty'])) {
    echo "\nRows with empty emails:\n";
    echo "First 10: " . implode(', ', array_slice($skipReasons['empty'], 0, 10)) . "\n";
}

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

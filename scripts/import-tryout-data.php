<?php

/**
 * Import Tryout Data from CSV
 *
 * Usage: php scripts/import-tryout-data.php
 *
 * Imports player-tryout-data.csv into the database
 * - Creates player records
 * - Creates parent records
 * - Links parents to players
 * - Logs all errors and warnings
 */

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

use App\Services\CSVImportService;

// Load environment variables (minimal bootstrap for CLI)
loadEnv(BASE_PATH . '/.env');

$importService = new CSVImportService();

echo "========================================\n";
echo "IVL Baseball - Tryout Data Import\n";
echo "========================================\n\n";

// Read CSV file
$csvFile = __DIR__ . '/../player-tryout-data.csv';
echo "Reading CSV file: $csvFile\n";

$rows = $importService->readCsv($csvFile);
if (empty($rows)) {
    echo "ERROR: No data read from CSV file\n";
    exit(1);
}

echo "Found " . count($rows) . " records in CSV\n";

// Debug: show first row keys and sample values
if (!empty($rows)) {
    echo "First row keys: " . implode(', ', array_keys($rows[0])) . "\n\n";
    echo "Sample values from row 0:\n";
    echo "  Player Email Address: '" . ($rows[0]['Player Email Address'] ?? 'N/A') . "'\n";
    echo "  Player Name: '" . ($rows[0]['Player Name'] ?? 'N/A') . "'\n";
    echo "  Select Age Group Team: '" . ($rows[0]['Select Age Group Team'] ?? 'N/A') . "'\n";
    echo "\n";
}

// Process each row
$processed = 0;
foreach ($rows as $index => $row) {
    $processed++;

    // Parse player data (use correct column names from players table)
    $playerData = [
        'email' => trim($row['Player Email Address'] ?? ''),
        'first_name' => extractFirstName($row['Player Name'] ?? ''),
        'last_name' => extractLastName($row['Player Name'] ?? ''),
        'phone' => $importService->normalizePhone($row['Player Phone/Mobile'] ?? ''),
        'birthdate' => $importService->parseDate($row['Birthdate'] ?? ''),
        'street_address' => trim($row['Address - Street Address'] ?? ''),
        'city' => trim($row['Address - City'] ?? ''),
        'state' => trim($row['Address - State/Province'] ?? ''),
        'zip_code' => trim($row['Address - ZIP / Postal Code'] ?? ''),
        'age_group' => $importService->normalizeAgeGroup($row['Select Age Group Team'] ?? ''),
        'shirt_size' => trim($row['Select Shirt Size'] ?? ''),
        'primary_position' => trim($row['Primary Position'] ?? ''),
        'secondary_position' => trim($row['Secondary Position'] ?? ''),
        'school_name' => trim($row['What School will you be attending in the fall?'] ?? ''),
        'grade_level' => trim($row['What grade will you be entering in the Fall'] ?? ''),
        'registration_source' => 'tryout_form',
        'registration_status' => 'tryout',
    ];

    // Validate required fields
    if (empty($playerData['email'])) {
        $importService->incrementSkipped();
        if ($index < 5) { // Only show first few
            echo "[$processed] SKIPPED (no email): " . substr($row['Player Name'] ?? '', 0, 30) . "\n";
        }
        continue;
    }

    // Debug: show first few rows being processed
    if ($index < 5) {
        echo "[$processed] Processing: " . $playerData['first_name'] . " " . $playerData['last_name'] . " (" . $playerData['email'] . ") - " . $playerData['age_group'] . "\n";
    }

    // Check for existing player
    $existingPlayer = $importService->playerExistsByEmail($playerData['email']);
    if ($existingPlayer) {
        $importService->incrementDuplicate();
        echo "[$processed] DUPLICATE: {$playerData['first_name']} {$playerData['last_name']} ({$playerData['email']}) - already exists\n";
        continue;
    }

    // Create player
    $playerId = $importService->createOrUpdatePlayer($playerData);
    if (!$playerId) {
        $importService->incrementSkipped();
        if ($index < 5) {
            echo "[$processed] SKIPPED (create failed)\n";
        }
        continue;
    }

    // Create parent 1
    $parent1Data = [
        'full_name' => trim($row['Parent/Guardian #1 Name'] ?? ''),
        'phone' => $importService->normalizePhone($row['Parent/Guardian #1 Phone'] ?? ''),
        'email' => trim($row['Parent/Guardian #1 Email'] ?? ''),
        'coaching_interest' => ($row['Parent/Guardian #1 Interest in Coaching?'] ?? '') === 'Yes' ? 1 : 0,
        'baseball_level_played' => trim($row['Level of Baseball played #1:'] ?? ''),
        'coaching_experience_years' => !empty($row['Coaching Experience #1 (Years)']) ? (int)$row['Coaching Experience #1 (Years)'] : 0,
        'coaching_history' => trim($row['Parent/Guardian #1 History of coaching experience'] ?? ''),
    ];

    if (!empty($parent1Data['full_name'])) {
        $importService->createOrUpdateParent($playerId, 1, $parent1Data);
    }

    // Create parent 2
    $parent2Data = [
        'full_name' => trim($row['Parent/Guardian #2 Name'] ?? ''),
        'phone' => $importService->normalizePhone($row['Parent/Guardian #2 Phone'] ?? ''),
        'email' => trim($row['Parent/Guardian #2 Email'] ?? ''),
        'coaching_interest' => ($row['Parent/Guardian #2 Interest in Coaching?'] ?? '') === 'Yes' ? 1 : 0,
        'baseball_level_played' => trim($row['Level of Baseball played #2:'] ?? ''),
        'coaching_experience_years' => !empty($row['Coaching Experience #2 (Years)']) ? (int)$row['Coaching Experience #2 (Years)'] : 0,
        'coaching_history' => trim($row['Parent/Guardian #2 History of coaching experience'] ?? ''),
    ];

    if (!empty($parent2Data['full_name'])) {
        $importService->createOrUpdateParent($playerId, 2, $parent2Data);
    }

    $importService->incrementImported();
    echo "[$processed] IMPORTED: {$playerData['first_name']} {$playerData['last_name']} ({$playerData['email']}) - {$playerData['age_group']}\n";
}

// Show summary
echo "\n========================================\n";
echo "Import Summary\n";
echo "========================================\n";

$stats = $importService->getStats();
echo "Imported:   " . $stats['imported'] . "\n";
echo "Duplicates: " . $stats['duplicates'] . "\n";
echo "Skipped:    " . $stats['skipped'] . "\n";
echo "Errors:     " . $stats['errors'] . "\n";
echo "Warnings:   " . $stats['warnings'] . "\n";

// Show errors if any
if (!empty($importService->getErrors())) {
    echo "\nErrors:\n";
    foreach ($importService->getErrors() as $error) {
        echo "  - $error\n";
    }
}

// Show warnings if any
if (!empty($importService->getWarnings())) {
    echo "\nWarnings:\n";
    foreach ($importService->getWarnings() as $warning) {
        echo "  - $warning\n";
    }
}

echo "\n";

/**
 * Helper functions
 */

function loadEnv(string $envFile): void
{
    if (!file_exists($envFile)) {
        die(".env file not found: $envFile");
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

function extractFirstName(string $fullName): string
{
    $parts = explode(' ', trim($fullName));
    return $parts[0] ?? '';
}

function extractLastName(string $fullName): string
{
    $parts = explode(' ', trim($fullName));
    return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
}

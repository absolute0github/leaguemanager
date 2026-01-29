<?php

/**
 * Import Commitment Data from CSV
 *
 * Usage: php scripts/import-commitment-data.php
 *
 * Imports player-committment-data.csv into the database
 * - Creates/updates player records with commitment status
 * - Creates parent records
 * - Records payment information (subscriptions/installments)
 * - Links players to payment plans
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
echo "IVL Baseball - Commitment Data Import\n";
echo "========================================\n\n";

// Read CSV file
$csvFile = __DIR__ . '/../player-committment-data.csv';
echo "Reading CSV file: $csvFile\n";

$rows = $importService->readCsv($csvFile);
if (empty($rows)) {
    echo "ERROR: No data read from CSV file\n";
    exit(1);
}

echo "Found " . count($rows) . " records in CSV\n\n";

// Process each row
$processed = 0;
foreach ($rows as $index => $row) {
    $processed++;

    // Parse player data (use correct column names from players table)
    $playerData = [
        'email' => trim($row["Player's Email Address"] ?? ''),
        'first_name' => extractFirstName($row["Players Name"] ?? ''),
        'last_name' => extractLastName($row["Players Name"] ?? ''),
        'phone' => $importService->normalizePhone($row["Player's Phone/Mobile"] ?? ''),
        'birthdate' => $importService->parseDate($row["Player's Birthdate"] ?? ''),
        'street_address' => trim($row['Address - Street Address'] ?? ''),
        'city' => trim($row['Address - City'] ?? ''),
        'state' => trim($row['Address - State/Province'] ?? ''),
        'zip_code' => trim($row['Address - ZIP / Postal Code'] ?? ''),
        'age_group' => $importService->normalizeAgeGroup($row['Select Club Team'] ?? ''),
        'registration_source' => 'commitment_form',
        'registration_status' => 'committed',
    ];

    // Validate required fields
    if (empty($playerData['email'])) {
        $importService->incrementSkipped();
        continue;
    }

    // Check for existing player
    $existingPlayer = $importService->playerExistsByEmail($playerData['email']);

    if ($existingPlayer) {
        // Update existing player with commitment status
        $playerId = $existingPlayer['id'];
        $importService->incrementDuplicate();

        // Update commitment status
        $importService->db->execute(
            'UPDATE players SET registration_status = ? WHERE id = ?',
            ['committed', $playerId]
        );

        echo "[$processed] UPDATED: {$playerData['first_name']} {$playerData['last_name']} ({$playerData['email']}) - status: committed\n";
    } else {
        // Create new player
        $playerId = $importService->createOrUpdatePlayer($playerData);
        if (!$playerId) {
            $importService->incrementSkipped();
            continue;
        }

        $importService->incrementImported();
        echo "[$processed] IMPORTED: {$playerData['first_name']} {$playerData['last_name']} ({$playerData['email']}) - {$playerData['age_group']}\n";
    }

    // Create parent 1
    $parent1Data = [
        'full_name' => trim($row['Parent #1 Full Name'] ?? ''),
        'phone' => $importService->normalizePhone($row['Parent #1 Phone/Mobile'] ?? ''),
        'email' => trim($row['Parent #1 Email Address'] ?? ''),
    ];

    if (!empty($parent1Data['full_name'])) {
        $importService->createOrUpdateParent($playerId, 1, $parent1Data);
    }

    // Create parent 2
    $parent2Data = [
        'full_name' => trim($row['Parent #2 Full Name'] ?? ''),
        'phone' => $importService->normalizePhone($row['Parent #2 Phone/Mobile'] ?? ''),
        'email' => trim($row['Parent #2 Email Address'] ?? ''),
    ];

    if (!empty($parent2Data['full_name'])) {
        $importService->createOrUpdateParent($playerId, 2, $parent2Data);
    }

    // Record payment information if available
    $paymentAmount = $row['Credit / Debit Card - Amount'] ?? '';
    $transactionId = $row['Credit / Debit Card - Transaction ID'] ?? '';
    $paymentStatus = $row['Credit / Debit Card - Status'] ?? '';
    $paymentPlanName = $row['Credit / Debit Card - Product / Plan Name'] ?? '';
    $subscriptionId = $row['Credit / Debit Card - Manage'] ?? '';

    if (!empty($paymentAmount) && !empty($transactionId)) {
        // Determine payment type from plan name
        $paymentType = 'subscription';
        if (stripos($paymentPlanName, 'Installment') !== false) {
            $paymentType = 'subscription_installment';
        }

        // Get or create payment plan
        $paymentPlanId = getOrCreatePaymentPlan($importService, $playerData['age_group'], $paymentPlanName);

        // Record payment
        $importService->db->execute(
            'INSERT INTO player_payments (player_id, payment_plan_id, payment_type, amount, currency, transaction_id, payment_status, payment_method, subscription_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $playerId,
                $paymentPlanId,
                $paymentType,
                $paymentAmount,
                'USD',
                $transactionId,
                $paymentStatus,
                'credit_card',
                $subscriptionId,
            ]
        );
    }
}

// Show summary
echo "\n========================================\n";
echo "Import Summary\n";
echo "========================================\n";

$stats = $importService->getStats();
echo "Imported:   " . $stats['imported'] . "\n";
echo "Updated:    " . $stats['duplicates'] . "\n";
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

function getOrCreatePaymentPlan($importService, $ageGroup, $planName): int
{
    // Check if plan exists
    $existing = $importService->db->fetchOne(
        'SELECT id FROM payment_plans WHERE name = ? AND age_group = ?',
        [$planName, $ageGroup]
    );

    if ($existing) {
        return $existing['id'];
    }

    // Determine plan type
    $planType = 'full';
    if (stripos($planName, 'Installment') !== false) {
        $planType = 'installment';
    }

    // Create new plan
    $importService->db->execute(
        'INSERT INTO payment_plans (name, age_group, plan_type) VALUES (?, ?, ?)',
        [$planName, $ageGroup, $planType]
    );

    return $importService->db->lastInsertId();
}

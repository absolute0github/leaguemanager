<?php
/**
 * Run Tryouts Module Migrations
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
    $pdo = $db->getConnection();

    echo "Running Tryouts Module Migrations...\n\n";

    // Migration 001
    echo "Running 001_add_waitlist_support.sql...\n";
    $sql001 = file_get_contents(__DIR__ . '/../app/Modules/tryouts/migrations/001_add_waitlist_support.sql');
    $pdo->exec($sql001);
    echo "✓ Migration 001 completed successfully\n\n";

    // Migration 002
    echo "Running 002_add_indexes.sql...\n";
    $sql002 = file_get_contents(__DIR__ . '/../app/Modules/tryouts/migrations/002_add_indexes.sql');
    $pdo->exec($sql002);
    echo "✓ Migration 002 completed successfully\n\n";

    echo "All migrations completed successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

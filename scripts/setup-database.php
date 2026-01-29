<?php

/**
 * Setup Database
 *
 * Usage: php scripts/setup-database.php
 *
 * Creates the database and imports the schema
 */

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Load environment variables
loadEnv(BASE_PATH . '/.env');

echo "========================================\n";
echo "IVL Baseball - Database Setup\n";
echo "========================================\n\n";

// Get database config
$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$database = $_ENV['DB_DATABASE'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'] ?? '';

echo "Connecting to MySQL at $host:$port...\n";

// Connect without database selection
try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "✓ Connected to MySQL\n\n";
} catch (PDOException $e) {
    die("✗ Failed to connect to MySQL: " . $e->getMessage() . "\n");
}

// Create database
echo "Creating database '$database'...\n";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database created or already exists\n\n";
} catch (PDOException $e) {
    die("✗ Failed to create database: " . $e->getMessage() . "\n");
}

// Select database
echo "Selecting database...\n";
try {
    $pdo->exec("USE `$database`");
    echo "✓ Database selected\n\n";
} catch (PDOException $e) {
    die("✗ Failed to select database: " . $e->getMessage() . "\n");
}

// Read schema file
$schemaFile = BASE_PATH . '/database/schema.sql';
echo "Reading schema from: $schemaFile\n";

if (!file_exists($schemaFile)) {
    die("✗ Schema file not found: $schemaFile\n");
}

$schema = file_get_contents($schemaFile);
if ($schema === false) {
    die("✗ Failed to read schema file\n");
}

echo "✓ Schema file read\n\n";

// Import schema
echo "Importing schema...\n";

try {
    // Split by semicolons but be careful with them in comments
    $lines = explode("\n", $schema);
    $statement = '';
    $inStatement = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        // Skip empty lines and comments
        if (empty($trimmed) || str_starts_with($trimmed, '--')) {
            continue;
        }

        $statement .= $line . "\n";

        // Check if statement ends with semicolon
        if (str_ends_with(trim($line), ';')) {
            // Remove trailing semicolon
            $statement = rtrim($statement, ";\n");

            if (!empty(trim($statement))) {
                try {
                    $pdo->exec($statement);
                } catch (PDOException $e) {
                    // If table already exists, that's OK
                    if (strpos($e->getMessage(), 'already exists') === false &&
                        strpos($e->getMessage(), 'Duplicate') === false) {
                        throw $e;
                    }
                }
            }

            $statement = '';
        }
    }

    echo "✓ Schema imported successfully\n\n";
} catch (PDOException $e) {
    echo "✗ Error importing schema: " . $e->getMessage() . "\n";
    echo "Last statement:\n" . substr($statement, 0, 200) . "...\n";
    die();
}

// Seed superuser
echo "Creating superuser...\n";

$superuserUsername = 'superuser';
$superuserEmail = 'jason@absolute0.net';
$superuserPassword = 'puppy-monkey-baby';
$passwordHash = password_hash($superuserPassword, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // Check if superuser already exists
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$superuserEmail]);

    if ($stmt->fetch()) {
        echo "✓ Superuser already exists\n\n";
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role, status, email_verified, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );

        $stmt->execute([
            $superuserUsername,
            $superuserEmail,
            $passwordHash,
            'superuser',
            'active',
            true,
        ]);

        echo "✓ Superuser created\n";
        echo "  Username: $superuserUsername\n";
        echo "  Email: $superuserEmail\n";
        echo "  Password: $superuserPassword\n\n";
    }
} catch (PDOException $e) {
    echo "✗ Failed to create superuser: " . $e->getMessage() . "\n";
    die();
}

echo "========================================\n";
echo "Database setup complete!\n";
echo "========================================\n";

/**
 * Helper function to load environment variables
 */
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

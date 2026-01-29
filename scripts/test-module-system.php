<?php
/**
 * Test Module System
 *
 * This script tests the module system functionality
 * Run: php scripts/test-module-system.php
 */

// Set up paths
define('BASE_PATH', dirname(__DIR__));

// Autoload
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
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

use App\Core\Database;
use App\Modules\ModuleManager;

echo "=== Module System Test ===\n\n";

// Test 1: Database connection
echo "1. Testing database connection...\n";
try {
    $db = Database::getInstance();
    echo "   [OK] Database connected\n";
} catch (Exception $e) {
    echo "   [FAIL] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check modules table exists
echo "\n2. Checking modules table...\n";
try {
    $result = $db->fetchAll("SHOW TABLES LIKE 'modules'");
    if (count($result) > 0) {
        echo "   [OK] modules table exists\n";
    } else {
        echo "   [WARN] modules table does not exist, creating...\n";
        $db->execute("
            CREATE TABLE IF NOT EXISTS modules (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL UNIQUE,
                version VARCHAR(20) NOT NULL,
                enabled BOOLEAN DEFAULT FALSE,
                settings JSON NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        echo "   [OK] modules table created\n";
    }
} catch (Exception $e) {
    echo "   [FAIL] Error checking/creating modules table: " . $e->getMessage() . "\n";
}

// Test 3: Module discovery
echo "\n3. Testing module discovery...\n";
try {
    $manager = ModuleManager::getInstance();
    $modules = $manager->discoverModules();
    echo "   [OK] Found " . count($modules) . " module(s)\n";
    foreach ($modules as $name => $config) {
        echo "       - {$name}: {$config['name']} v{$config['version']}\n";
    }
} catch (Exception $e) {
    echo "   [FAIL] Module discovery failed: " . $e->getMessage() . "\n";
}

// Test 4: Directly install attendance module with SQL
echo "\n4. Directly installing attendance module...\n";
try {
    // Check if already exists
    $existing = $db->fetchOne("SELECT id FROM modules WHERE name = ?", ['attendance']);
    if ($existing) {
        echo "   [INFO] Module already in database, updating...\n";
        $db->execute("UPDATE modules SET enabled = 1 WHERE name = ?", ['attendance']);
    } else {
        echo "   [INFO] Inserting new module record...\n";
        $db->execute(
            "INSERT INTO modules (name, version, enabled, settings)
             VALUES (?, ?, 1, '{}')",
            ['attendance', '1.0.0']
        );
    }
    echo "   [OK] Module record inserted/updated\n";

    // Verify
    $check = $db->fetchOne("SELECT * FROM modules WHERE name = ?", ['attendance']);
    if ($check) {
        echo "   [OK] Verified: ID={$check['id']}, enabled={$check['enabled']}\n";
    } else {
        echo "   [WARN] Could not verify - record not found\n";
    }
} catch (Exception $e) {
    echo "   [FAIL] Module install failed: " . $e->getMessage() . "\n";
}

// Test 5: Run module migration directly
echo "\n5. Running attendance module migration...\n";
$migrationFile = BASE_PATH . '/app/Modules/attendance/migrations/001_create_attendance_table.sql';
if (file_exists($migrationFile)) {
    $sql = file_get_contents($migrationFile);
    echo "   [INFO] Migration file found, executing...\n";

    // Split by semicolon to handle multiple statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->execute($statement);
                echo "   [OK] Executed: " . substr($statement, 0, 60) . "...\n";
            } catch (Exception $e) {
                // Table might already exist
                if (strpos($e->getMessage(), 'already exists') !== false) {
                    echo "   [INFO] Table already exists\n";
                } else {
                    echo "   [WARN] " . $e->getMessage() . "\n";
                }
            }
        }
    }
} else {
    echo "   [FAIL] Migration file not found: $migrationFile\n";
}

// Test 6: Verify attendance tables
echo "\n6. Checking attendance module tables...\n";
try {
    $tables = $db->fetchAll("SHOW TABLES LIKE 'mod_attendance%'");
    echo "   [OK] Found " . count($tables) . " attendance table(s)\n";
    foreach ($tables as $table) {
        $tableName = array_values($table)[0];
        echo "       - {$tableName}\n";
    }
} catch (Exception $e) {
    echo "   [FAIL] Error checking tables: " . $e->getMessage() . "\n";
}

// Test 7: Load enabled modules
echo "\n7. Testing module loading...\n";
try {
    // Get fresh manager instance
    $manager = ModuleManager::getInstance();
    $manager->loadEnabledModules();
    $loaded = $manager->getLoadedModules();
    echo "   [OK] Loaded " . count($loaded) . " module(s)\n";
    foreach ($loaded as $name => $config) {
        echo "       - {$name}\n";
    }
} catch (Exception $e) {
    echo "   [FAIL] Module loading failed: " . $e->getMessage() . "\n";
}

// Test 8: Verify all modules status
echo "\n8. Current module status...\n";
try {
    $allModules = $manager->getAllModules();
    foreach ($allModules as $name => $module) {
        $status = $module['enabled'] ? 'ENABLED' : 'DISABLED';
        $installed = $module['installed'] ? 'Installed' : 'Not installed';
        echo "   - {$module['name']}: [{$status}] ({$installed})\n";
    }
} catch (Exception $e) {
    echo "   [FAIL] Error checking module status: " . $e->getMessage() . "\n";
}

// Final DB check
echo "\n9. Final database verification...\n";
$modules = $db->fetchAll("SELECT * FROM modules");
echo "   Modules in database: " . count($modules) . "\n";
foreach ($modules as $m) {
    echo "   - ID: {$m['id']}, Name: {$m['name']}, Enabled: {$m['enabled']}\n";
}

echo "\n=== Test Complete ===\n";
echo "\nTo test the module in the browser:\n";
echo "1. Log in as a coach at http://leaguemanager.cw.local/login\n";
echo "2. Go to the coach dashboard at http://leaguemanager.cw.local/coach/dashboard\n";
echo "3. You should see the Attendance widget and sidebar link\n";
echo "4. Click on Attendance to access the attendance tracking features\n";
echo "\nTo manage modules:\n";
echo "- Log in as superuser\n";
echo "- Go to http://leaguemanager.cw.local/admin/modules\n";

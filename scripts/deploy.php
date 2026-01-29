<?php
/**
 * Deployment & Setup Script
 *
 * Usage:
 *   php scripts/deploy.php setup    - Initial setup
 *   php scripts/deploy.php check    - Pre-deployment checks
 *   php scripts/deploy.php clear    - Clear caches
 *   php scripts/deploy.php migrate  - Run database migrations
 */

define('BASE_PATH', dirname(__DIR__));

// Colors for terminal output
class Output
{
    public static function success($msg) { echo "\033[32m[OK] $msg\033[0m\n"; }
    public static function error($msg) { echo "\033[31m[ERROR] $msg\033[0m\n"; }
    public static function warning($msg) { echo "\033[33m[WARN] $msg\033[0m\n"; }
    public static function info($msg) { echo "\033[36m[INFO] $msg\033[0m\n"; }
    public static function header($msg) { echo "\n\033[1;34m=== $msg ===\033[0m\n\n"; }
}

$command = $argv[1] ?? 'help';

switch ($command) {
    case 'setup':
        runSetup();
        break;
    case 'check':
        runChecks();
        break;
    case 'clear':
        clearCaches();
        break;
    case 'migrate':
        runMigrations();
        break;
    default:
        showHelp();
}

function showHelp(): void
{
    echo <<<HELP

IVL Baseball League - Deployment Script

Usage: php scripts/deploy.php <command>

Commands:
  setup     Initial application setup
  check     Run pre-deployment checks
  clear     Clear all caches
  migrate   Run database migrations
  help      Show this help message

HELP;
}

function runSetup(): void
{
    Output::header("Initial Setup");

    // Check PHP version
    Output::info("Checking PHP version...");
    if (version_compare(PHP_VERSION, '8.1.0', '<')) {
        Output::error("PHP 8.1+ required. Current: " . PHP_VERSION);
        exit(1);
    }
    Output::success("PHP " . PHP_VERSION);

    // Check extensions
    $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'curl', 'gd'];
    Output::info("Checking PHP extensions...");
    foreach ($requiredExtensions as $ext) {
        if (!extension_loaded($ext)) {
            Output::error("Missing extension: $ext");
        } else {
            Output::success("Extension: $ext");
        }
    }

    // Create directories
    Output::info("Creating directories...");
    $dirs = [
        BASE_PATH . '/storage/logs',
        BASE_PATH . '/storage/cache',
        BASE_PATH . '/storage/uploads',
        BASE_PATH . '/storage/temp',
    ];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            Output::success("Created: $dir");
        } else {
            Output::success("Exists: $dir");
        }
    }

    // Check .env file
    Output::info("Checking environment file...");
    if (!file_exists(BASE_PATH . '/.env')) {
        if (file_exists(BASE_PATH . '/.env.example')) {
            copy(BASE_PATH . '/.env.example', BASE_PATH . '/.env');
            Output::warning("Created .env from .env.example - PLEASE UPDATE IT");
        } else {
            Output::error(".env file not found!");
        }
    } else {
        Output::success(".env file exists");
    }

    // Check composer dependencies
    Output::info("Checking Composer dependencies...");
    if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
        Output::warning("Vendor directory not found. Run: composer install");
    } else {
        Output::success("Composer dependencies installed");
    }

    // Set file permissions
    Output::info("Setting file permissions...");
    @chmod(BASE_PATH . '/storage', 0755);
    @chmod(BASE_PATH . '/storage/logs', 0755);
    @chmod(BASE_PATH . '/storage/cache', 0755);
    @chmod(BASE_PATH . '/storage/uploads', 0755);
    Output::success("Permissions set");

    Output::header("Setup Complete");
    echo "Next steps:\n";
    echo "1. Update .env with your database credentials\n";
    echo "2. Run: php scripts/deploy.php migrate\n";
    echo "3. Run: php scripts/setup-database.php (if needed)\n\n";
}

function runChecks(): void
{
    Output::header("Pre-Deployment Checks");
    $issues = 0;

    // Load environment
    if (file_exists(BASE_PATH . '/.env')) {
        $env = parse_ini_file(BASE_PATH . '/.env');
        foreach ($env as $k => $v) {
            $_ENV[$k] = $v;
        }
    }

    // Check APP_ENV
    Output::info("Checking environment settings...");
    if (($_ENV['APP_ENV'] ?? 'development') === 'development') {
        Output::warning("APP_ENV is 'development' - set to 'production' for deployment");
        $issues++;
    } else {
        Output::success("APP_ENV is production");
    }

    // Check APP_DEBUG
    if (($_ENV['APP_DEBUG'] ?? 'true') === 'true') {
        Output::warning("APP_DEBUG is enabled - disable for production");
        $issues++;
    } else {
        Output::success("APP_DEBUG is disabled");
    }

    // Check database connection
    Output::info("Testing database connection...");
    try {
        require_once BASE_PATH . '/vendor/autoload.php';
        $db = App\Core\Database::getInstance();
        Output::success("Database connected");
    } catch (Exception $e) {
        Output::error("Database connection failed: " . $e->getMessage());
        $issues++;
    }

    // Check writable directories
    Output::info("Checking writable directories...");
    $writableDirs = ['storage/logs', 'storage/cache', 'storage/uploads'];
    foreach ($writableDirs as $dir) {
        $path = BASE_PATH . '/' . $dir;
        if (!is_writable($path)) {
            Output::error("Not writable: $dir");
            $issues++;
        } else {
            Output::success("Writable: $dir");
        }
    }

    // Check sensitive files
    Output::info("Checking sensitive file access...");
    $sensitiveFiles = ['.env', 'composer.json', 'composer.lock'];
    foreach ($sensitiveFiles as $file) {
        $path = BASE_PATH . '/' . $file;
        if (file_exists($path)) {
            // In a real deployment, you'd check HTTP access
            Output::success("$file exists (ensure not web-accessible)");
        }
    }

    // Check error pages
    Output::info("Checking error pages...");
    $errorPages = ['404.php', '500.php', '403.php'];
    foreach ($errorPages as $page) {
        $path = BASE_PATH . '/app/Views/errors/' . $page;
        if (file_exists($path)) {
            Output::success("Error page: $page");
        } else {
            Output::warning("Missing error page: $page");
        }
    }

    Output::header("Check Complete");
    if ($issues > 0) {
        echo "Found $issues issue(s) to address before deployment.\n\n";
    } else {
        echo "All checks passed! Ready for deployment.\n\n";
    }
}

function clearCaches(): void
{
    Output::header("Clearing Caches");

    // Clear cache directory
    $cacheDir = BASE_PATH . '/storage/cache';
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        Output::success("Cache cleared");
    }

    // Clear temp directory
    $tempDir = BASE_PATH . '/storage/temp';
    if (is_dir($tempDir)) {
        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        Output::success("Temp files cleared");
    }

    // Clear old log files (keep last 7 days)
    Output::info("Cleaning old logs...");
    $logsDir = BASE_PATH . '/storage/logs';
    if (is_dir($logsDir)) {
        $cutoff = time() - (7 * 24 * 60 * 60);
        $files = glob($logsDir . '/*.log.*');
        $deleted = 0;
        foreach ($files as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }
        Output::success("Cleaned $deleted old log file(s)");
    }

    Output::header("Cache Clear Complete");
}

function runMigrations(): void
{
    Output::header("Running Migrations");

    // Load environment
    if (file_exists(BASE_PATH . '/.env')) {
        $env = parse_ini_file(BASE_PATH . '/.env');
        foreach ($env as $k => $v) {
            $_ENV[$k] = $v;
        }
    }

    require_once BASE_PATH . '/vendor/autoload.php';

    try {
        $db = App\Core\Database::getInstance();
        Output::success("Database connected");

        // Run schema if needed
        $schemaFile = BASE_PATH . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            Output::info("Schema file found. Run manually if needed:");
            Output::info("mysql -u USER -p DATABASE < database/schema.sql");
        }

        // Check for module migrations
        $modulesDir = BASE_PATH . '/app/Modules';
        if (is_dir($modulesDir)) {
            $modules = glob($modulesDir . '/*', GLOB_ONLYDIR);
            foreach ($modules as $moduleDir) {
                $moduleName = basename($moduleDir);
                $migrationsDir = $moduleDir . '/migrations';

                if (is_dir($migrationsDir)) {
                    Output::info("Found migrations for module: $moduleName");
                    $files = glob($migrationsDir . '/*.sql');
                    sort($files);

                    foreach ($files as $file) {
                        $sql = file_get_contents($file);
                        $statements = array_filter(array_map('trim', explode(';', $sql)));

                        foreach ($statements as $statement) {
                            if (!empty($statement)) {
                                try {
                                    $db->execute($statement);
                                } catch (Exception $e) {
                                    // Table might already exist
                                    if (strpos($e->getMessage(), 'already exists') === false) {
                                        Output::warning(basename($file) . ": " . $e->getMessage());
                                    }
                                }
                            }
                        }
                        Output::success("Executed: " . basename($file));
                    }
                }
            }
        }

        Output::header("Migrations Complete");
    } catch (Exception $e) {
        Output::error("Migration failed: " . $e->getMessage());
        exit(1);
    }
}

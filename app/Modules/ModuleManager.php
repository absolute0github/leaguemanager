<?php

namespace App\Modules;

use App\Core\Database;

/**
 * ModuleManager - Handles module discovery, loading, and lifecycle
 *
 * Modules are self-contained plugins that extend the application.
 * Each module lives in app/Modules/{module-name}/ and contains:
 * - module.json: Metadata, version, hooks, routes
 * - Controllers, Models, Views as needed
 * - migrations/: Database migrations for the module
 */
class ModuleManager
{
    private static ?ModuleManager $instance = null;
    private Database $db;
    private string $modulesPath;
    private array $loadedModules = [];
    private array $hooks = [];
    private array $registeredRoutes = [];

    private function __construct()
    {
        $this->db = Database::getInstance();
        $this->modulesPath = dirname(__DIR__) . '/Modules';
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): ModuleManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Discover all available modules in the modules directory
     */
    public function discoverModules(): array
    {
        $modules = [];
        $dirs = glob($this->modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $moduleName = basename($dir);
            $configFile = $dir . '/module.json';

            if (file_exists($configFile)) {
                $config = json_decode(file_get_contents($configFile), true);
                if ($config) {
                    $config['path'] = $dir;
                    $config['directory'] = $moduleName;
                    $modules[$moduleName] = $config;
                }
            }
        }

        return $modules;
    }

    /**
     * Get all modules with their database status
     */
    public function getAllModules(): array
    {
        $discovered = $this->discoverModules();
        $dbModules = $this->getModulesFromDatabase();

        $result = [];
        foreach ($discovered as $name => $config) {
            $dbRecord = $dbModules[$name] ?? null;
            $result[$name] = [
                'name' => $config['name'] ?? $name,
                'directory' => $name,
                'version' => $config['version'] ?? '1.0.0',
                'description' => $config['description'] ?? '',
                'author' => $config['author'] ?? 'Unknown',
                'enabled' => $dbRecord['enabled'] ?? false,
                'installed' => $dbRecord !== null,
                'db_id' => $dbRecord['id'] ?? null,
                'config' => $config,
            ];
        }

        return $result;
    }

    /**
     * Get modules from database
     */
    private function getModulesFromDatabase(): array
    {
        try {
            $rows = $this->db->fetchAll("SELECT * FROM modules");
            $result = [];
            foreach ($rows as $row) {
                $result[$row['name']] = $row;
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get enabled modules from database
     */
    public function getEnabledModules(): array
    {
        try {
            return $this->db->fetchAll("SELECT * FROM modules WHERE enabled = 1");
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Install a module (add to database)
     */
    public function installModule(string $moduleName): bool
    {
        $modules = $this->discoverModules();
        if (!isset($modules[$moduleName])) {
            return false;
        }

        $config = $modules[$moduleName];

        try {
            // Check if already installed
            $existing = $this->db->fetchOne(
                "SELECT id FROM modules WHERE name = ?",
                [$moduleName]
            );

            if ($existing) {
                return true; // Already installed
            }

            // Insert module record
            $this->db->execute(
                "INSERT INTO modules (name, version, enabled, settings)
                 VALUES (?, ?, 0, '{}')",
                [$moduleName, $config['version'] ?? '1.0.0']
            );

            // Run migrations if any
            $this->runMigrations($moduleName);

            return true;
        } catch (\Exception $e) {
            error_log("Failed to install module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable a module
     */
    public function enableModule(string $moduleName): bool
    {
        try {
            // Install first if not installed
            $this->installModule($moduleName);

            $this->db->execute(
                "UPDATE modules SET enabled = 1 WHERE name = ?",
                [$moduleName]
            );

            return true;
        } catch (\Exception $e) {
            error_log("Failed to enable module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Disable a module
     */
    public function disableModule(string $moduleName): bool
    {
        try {
            $this->db->execute(
                "UPDATE modules SET enabled = 0 WHERE name = ?",
                [$moduleName]
            );

            return true;
        } catch (\Exception $e) {
            error_log("Failed to disable module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall a module (remove from database, keep files)
     */
    public function uninstallModule(string $moduleName): bool
    {
        try {
            $this->db->execute("DELETE FROM modules WHERE name = ?", [$moduleName]);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to uninstall module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Run module migrations
     */
    private function runMigrations(string $moduleName): void
    {
        $modules = $this->discoverModules();
        if (!isset($modules[$moduleName])) {
            return;
        }

        $migrationsPath = $modules[$moduleName]['path'] . '/migrations';
        if (!is_dir($migrationsPath)) {
            return;
        }

        $files = glob($migrationsPath . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $sql = file_get_contents($file);
            if (!empty(trim($sql))) {
                try {
                    $this->db->execute($sql);
                } catch (\Exception $e) {
                    error_log("Migration failed for {$moduleName}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Load all enabled modules
     */
    public function loadEnabledModules(): void
    {
        $enabledModules = $this->getEnabledModules();
        $discoveredModules = $this->discoverModules();

        foreach ($enabledModules as $dbModule) {
            $moduleName = $dbModule['name'];
            if (isset($discoveredModules[$moduleName])) {
                $this->loadModule($moduleName, $discoveredModules[$moduleName]);
            }
        }
    }

    /**
     * Load a single module
     */
    private function loadModule(string $moduleName, array $config): void
    {
        if (isset($this->loadedModules[$moduleName])) {
            return; // Already loaded
        }

        // Register hooks from module.json
        if (!empty($config['hooks'])) {
            foreach ($config['hooks'] as $hookName => $handler) {
                $this->registerHook($hookName, $moduleName, $handler);
            }
        }

        // Load routes
        $routesFile = $config['path'] . '/routes.php';
        if (file_exists($routesFile)) {
            $this->registeredRoutes[$moduleName] = $routesFile;
        }

        $this->loadedModules[$moduleName] = $config;
    }

    /**
     * Register a hook handler
     */
    public function registerHook(string $hookName, string $moduleName, string $handler): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }

        $this->hooks[$hookName][] = [
            'module' => $moduleName,
            'handler' => $handler,
        ];
    }

    /**
     * Execute a hook - calls all registered handlers
     */
    public function executeHook(string $hookName, array $context = []): array
    {
        $results = [];

        if (!isset($this->hooks[$hookName])) {
            return $results;
        }

        foreach ($this->hooks[$hookName] as $hookInfo) {
            try {
                $result = $this->callHandler($hookInfo['module'], $hookInfo['handler'], $context);
                if ($result !== null) {
                    $results[$hookInfo['module']] = $result;
                }
            } catch (\Exception $e) {
                error_log("Hook {$hookName} failed for module {$hookInfo['module']}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Call a module handler (Controller@method format)
     */
    private function callHandler(string $moduleName, string $handler, array $context): mixed
    {
        if (!isset($this->loadedModules[$moduleName])) {
            return null;
        }

        $config = $this->loadedModules[$moduleName];

        // Parse handler: "ControllerName@methodName"
        $parts = explode('@', $handler);
        if (count($parts) !== 2) {
            return null;
        }

        [$controllerName, $methodName] = $parts;

        // Build controller class name
        $controllerClass = "App\\Modules\\{$moduleName}\\Controllers\\{$controllerName}";

        // Try to load controller file
        $controllerFile = $config['path'] . '/Controllers/' . $controllerName . '.php';
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
        }

        if (!class_exists($controllerClass)) {
            return null;
        }

        $controller = new $controllerClass();
        if (!method_exists($controller, $methodName)) {
            return null;
        }

        return $controller->$methodName($context);
    }

    /**
     * Get routes for all loaded modules
     */
    public function getModuleRoutes(): array
    {
        return $this->registeredRoutes;
    }

    /**
     * Register module routes with the router
     */
    public function registerRoutes($router): void
    {
        foreach ($this->registeredRoutes as $moduleName => $routesFile) {
            $moduleRoutes = require $routesFile;
            if (is_callable($moduleRoutes)) {
                $moduleRoutes($router, $moduleName);
            }
        }
    }

    /**
     * Get loaded modules
     */
    public function getLoadedModules(): array
    {
        return $this->loadedModules;
    }

    /**
     * Check if a module is enabled
     */
    public function isModuleEnabled(string $moduleName): bool
    {
        return isset($this->loadedModules[$moduleName]);
    }

    /**
     * Get module settings
     */
    public function getModuleSettings(string $moduleName): array
    {
        try {
            $module = $this->db->fetchOne(
                "SELECT settings FROM modules WHERE name = ?",
                [$moduleName]
            );

            if ($module && !empty($module['settings'])) {
                return json_decode($module['settings'], true) ?? [];
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return [];
    }

    /**
     * Save module settings
     */
    public function saveModuleSettings(string $moduleName, array $settings): bool
    {
        try {
            $this->db->execute(
                "UPDATE modules SET settings = ?, updated_at = NOW() WHERE name = ?",
                [json_encode($settings), $moduleName]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get module path
     */
    public function getModulePath(string $moduleName): ?string
    {
        if (isset($this->loadedModules[$moduleName])) {
            return $this->loadedModules[$moduleName]['path'];
        }

        $modules = $this->discoverModules();
        return $modules[$moduleName]['path'] ?? null;
    }
}

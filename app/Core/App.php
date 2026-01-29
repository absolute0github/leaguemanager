<?php

namespace App\Core;

use App\Modules\ModuleManager;

class App
{
    private Router $router;
    private ModuleManager $moduleManager;
    private Logger $logger;

    public function __construct()
    {
        // Load environment variables
        $this->loadEnv();

        // Register error handler
        ErrorHandler::register();

        // Initialize logger
        $this->logger = Logger::getInstance();

        // Initialize core services
        Session::getInstance();
        CSRF::initialize();

        // Initialize router
        $this->router = new Router();

        // Initialize module manager
        $this->moduleManager = ModuleManager::getInstance();
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnv(): void
    {
        $envFile = __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            die(".env file not found");
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

    /**
     * Register routes
     */
    public function registerRoutes(callable $callback): void
    {
        // Register core application routes
        $callback($this->router);

        // Load enabled modules and register their routes
        $this->moduleManager->loadEnabledModules();
        $this->moduleManager->registerRoutes($this->router);
    }

    /**
     * Run the application
     */
    public function run(): void
    {
        // Log access (can be disabled in production if too verbose)
        if (($_ENV['LOG_ACCESS'] ?? 'true') === 'true') {
            $this->logger->access();
        }

        $this->router->dispatch();
    }

    /**
     * Get module manager instance
     */
    public function getModuleManager(): ModuleManager
    {
        return $this->moduleManager;
    }

    /**
     * Get router instance
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}

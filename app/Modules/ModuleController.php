<?php

namespace App\Modules;

use App\Core\Controller;

/**
 * Base controller for module controllers
 * Extends the core Controller with module-specific functionality
 */
abstract class ModuleController extends Controller
{
    protected string $moduleName;
    protected string $modulePath;
    protected ModuleManager $moduleManager;

    public function __construct(string $moduleName)
    {
        parent::__construct();
        $this->moduleName = $moduleName;
        $this->moduleManager = ModuleManager::getInstance();
        $this->modulePath = $this->moduleManager->getModulePath($moduleName);
    }

    /**
     * Render a view from this module
     */
    protected function moduleView(string $viewName, array $data = []): void
    {
        $viewFile = $this->modulePath . '/Views/' . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("Module view not found: {$viewFile}");
        }

        // Add module info to data
        $data['_module'] = $this->moduleName;
        $data['_modulePath'] = $this->modulePath;

        extract($data);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        echo $content;
    }

    /**
     * Render a view within a layout
     */
    protected function moduleViewWithLayout(string $viewName, string $layout, array $data = []): void
    {
        $viewFile = $this->modulePath . '/Views/' . str_replace('.', '/', $viewName) . '.php';

        if (!file_exists($viewFile)) {
            throw new \Exception("Module view not found: {$viewFile}");
        }

        $data['_module'] = $this->moduleName;
        $data['_modulePath'] = $this->modulePath;

        extract($data);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $data['content'] = $content;
        $this->view($layout, $data);
    }

    /**
     * Get module settings
     */
    protected function getSettings(): array
    {
        return $this->moduleManager->getModuleSettings($this->moduleName);
    }

    /**
     * Save module settings
     */
    protected function saveSettings(array $settings): bool
    {
        return $this->moduleManager->saveModuleSettings($this->moduleName, $settings);
    }

    /**
     * Get a specific setting value
     */
    protected function getSetting(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();
        return $settings[$key] ?? $default;
    }

    /**
     * Get the module's database table prefix
     */
    protected function getTablePrefix(): string
    {
        return 'mod_' . str_replace('-', '_', $this->moduleName) . '_';
    }
}

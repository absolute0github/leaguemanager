<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $currentUrl = '';
    private string $currentMethod = '';

    public function __construct()
    {
        $this->currentUrl = $this->parseUrl();
        $this->currentMethod = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Register a GET route
     */
    public function get(string $route, string $controller, string $method): void
    {
        $this->addRoute('GET', $route, $controller, $method);
    }

    /**
     * Register a POST route
     */
    public function post(string $route, string $controller, string $method): void
    {
        $this->addRoute('POST', $route, $controller, $method);
    }

    /**
     * Register a PUT route
     */
    public function put(string $route, string $controller, string $method): void
    {
        $this->addRoute('PUT', $route, $controller, $method);
    }

    /**
     * Register a DELETE route
     */
    public function delete(string $route, string $controller, string $method): void
    {
        $this->addRoute('DELETE', $route, $controller, $method);
    }

    /**
     * Add a route to the router
     */
    private function addRoute(string $method, string $route, string $controller, string $action): void
    {
        $route = '/' . ltrim($route, '/');
        $this->routes[$method][$route] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Parse the URL from the request
     */
    private function parseUrl(): string
    {
        if (isset($_GET['url'])) {
            return '/' . rtrim($_GET['url'], '/');
        }
        return '/';
    }

    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch(): void
    {
        // Check if route exists
        if (!isset($this->routes[$this->currentMethod][$this->currentUrl])) {
            // Try to find a matching route with parameters
            $route = $this->findRouteWithParams();
            if ($route === null) {
                $this->notFound();
                return;
            }
        } else {
            $route = $this->routes[$this->currentMethod][$this->currentUrl];
        }

        $controllerName = $route['controller'];
        $method = $route['action'];

        // Determine controller class - support both regular controllers and module controllers
        if (str_starts_with($controllerName, 'Modules\\') || str_starts_with($controllerName, 'App\\')) {
            // Module controller or fully qualified - add App\ prefix if needed
            $controllerClass = str_starts_with($controllerName, 'App\\')
                ? $controllerName
                : 'App\\' . $controllerName;
        } else {
            // Regular controller
            $controllerClass = 'App\\Controllers\\' . $controllerName;
        }

        if (!class_exists($controllerClass)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            $this->notFound();
            return;
        }

        call_user_func([$controller, $method]);
    }

    /**
     * Try to find a route with parameters
     */
    private function findRouteWithParams(): ?array
    {
        if (!isset($this->routes[$this->currentMethod])) {
            return null;
        }

        foreach ($this->routes[$this->currentMethod] as $route => $handler) {
            if ($this->matchRoute($route, $this->currentUrl)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * Match a route pattern with the current URL
     */
    private function matchRoute(string $pattern, string $url): bool
    {
        $pattern = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';
        return (bool) preg_match($pattern, $url);
    }

    /**
     * Handle 404 Not Found
     */
    private function notFound(): void
    {
        ErrorHandler::notFound();
    }
}

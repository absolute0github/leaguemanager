<?php

namespace App\Core;

class Controller
{
    protected Database $db;
    protected Session $session;
    protected Validator $validator;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = Session::getInstance();
        $this->validator = new Validator();
    }

    /**
     * Load a view file
     */
    protected function view(string $viewPath, array $data = []): void
    {
        // Convert view path to file path
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: $viewFile");
        }

        // Extract data into variables
        extract($data);

        // Include the view file
        include $viewFile;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get a POST parameter
     */
    protected function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get a GET parameter
     */
    protected function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn(): bool
    {
        return $this->session->has('user_id');
    }

    /**
     * Get current logged-in user
     */
    protected function getUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $this->session->get('user_id'),
            'username' => $this->session->get('username'),
            'email' => $this->session->get('email'),
            'role' => $this->session->get('role')
        ];
    }

    /**
     * Check if user has a specific role
     */
    protected function hasRole(string $role): bool
    {
        return $this->session->get('role') === $role;
    }

    /**
     * Check if user has any of the specified roles
     */
    protected function hasAnyRole(array $roles): bool
    {
        return in_array($this->session->get('role'), $roles);
    }

    /**
     * Require authentication
     */
    protected function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
        }
    }

    /**
     * Require a specific role
     */
    protected function requireRole(string $role): void
    {
        $this->requireAuth();

        if (!$this->hasRole($role)) {
            http_response_code(403);
            die("Access Denied");
        }
    }

    /**
     * Require any of the specified roles
     */
    protected function requireAnyRole(array $roles): void
    {
        $this->requireAuth();

        if (!$this->hasAnyRole($roles)) {
            http_response_code(403);
            die("Access Denied");
        }
    }

    /**
     * Add error message to session
     */
    protected function addError(string $message): void
    {
        $errors = $this->session->get('errors', []);
        $errors[] = $message;
        $this->session->set('errors', $errors);
    }

    /**
     * Add success message to session
     */
    protected function addSuccess(string $message): void
    {
        $messages = $this->session->get('success', []);
        $messages[] = $message;
        $this->session->set('success', $messages);
    }

    /**
     * Get and clear error messages
     */
    protected function getErrors(): array
    {
        $errors = $this->session->get('errors', []);
        $this->session->delete('errors');
        return $errors;
    }

    /**
     * Get and clear success messages
     */
    protected function getSuccess(): array
    {
        $messages = $this->session->get('success', []);
        $this->session->delete('success');
        return $messages;
    }

    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->session->set('csrf_token', $token);
        return $token;
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrfToken(string $token): bool
    {
        $sessionToken = $this->session->get('csrf_token');
        return $sessionToken && hash_equals($sessionToken, $token);
    }
}

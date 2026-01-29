<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\RegistrationService;
use App\Models\Player;

class RegistrationController extends Controller
{
    private RegistrationService $registrationService;
    private Player $playerModel;

    public function __construct()
    {
        parent::__construct();
        $this->registrationService = new RegistrationService();
        $this->playerModel = new Player();
    }

    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $email = $this->get('email', '');
        $existingPlayer = null;

        // If email provided, do lookup
        if (!empty($email)) {
            $existingPlayer = $this->registrationService->findPlayerByEmail($email);
        }

        $this->view('auth.register', [
            'csrfToken' => $this->generateCsrfToken(),
            'email' => $email,
            'existingPlayer' => $existingPlayer,
        ]);
    }

    /**
     * Handle registration form submission
     */
    public function handleRegister(): void
    {
        // Verify CSRF token
        $csrfToken = $this->post('csrf_token', '');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/register');
            return;
        }

        // Get form data (email becomes username)
        $data = [
            'email' => $this->post('email', ''),
            'password' => $this->post('password', ''),
            'password_confirm' => $this->post('password_confirm', ''),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ];

        // Register user
        $result = $this->registrationService->registerUser($data);

        if (!$result['success']) {
            $this->addError($result['message']);
            $this->redirect('/register');
        }

        // Registration successful - all registrations require admin approval
        $this->addSuccess('Registration successful! An administrator will review your account. You will receive an email once it is approved.');
        $this->redirect('/login');
    }

    /**
     * Show pending registrations for admin (placeholder - full implementation in Phase 6)
     */
    public function pendingRegistrations(): void
    {
        $this->requireAuth();

        $user = $this->getUser();
        if (!in_array($user['role'], ['admin', 'superuser'])) {
            $this->redirect('/dashboard');
        }

        $limit = 25;
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $limit;

        $pending = $this->registrationService->getPendingRegistrations($limit, $offset);
        $totalCount = $this->registrationService->getPendingRegistrationCount();
        $totalPages = ceil($totalCount / $limit);

        $this->adminView('admin.pending-registrations', [
            'user' => $user,
            'pending' => $pending,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Render a view within the admin layout
     */
    protected function adminView(string $viewPath, array $data = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: $viewFile");
        }

        extract($data);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $data['content'] = $content;
        extract($data);

        include __DIR__ . '/../Views/layouts/admin.php';
    }

    /**
     * Approve a pending registration (admin)
     */
    public function approveRegistration(): void
    {
        $this->requireAuth();

        $user = $this->getUser();
        if (!in_array($user['role'], ['admin', 'superuser'])) {
            $this->addError('Unauthorized');
            $this->redirect('/dashboard');
        }

        // Verify CSRF token
        $csrfToken = $this->post('csrf_token', '');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/pending-registrations');
        }

        $userId = (int)$this->post('user_id', 0);
        if ($userId === 0) {
            $this->addError('Invalid user ID');
            $this->redirect('/admin/pending-registrations');
        }

        if ($this->registrationService->approveRegistration($userId)) {
            $this->addSuccess('Registration approved');
        } else {
            $this->addError('Failed to approve registration');
        }

        $this->redirect('/admin/pending-registrations');
    }

    /**
     * Reject a pending registration (admin)
     */
    public function rejectRegistration(): void
    {
        $this->requireAuth();

        $user = $this->getUser();
        if (!in_array($user['role'], ['admin', 'superuser'])) {
            $this->addError('Unauthorized');
            $this->redirect('/dashboard');
        }

        // Verify CSRF
        $csrfToken = $this->post('csrf_token', '');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/pending-registrations');
        }

        $userId = (int)$this->post('user_id', 0);
        $reason = $this->post('reason', '');

        if ($userId === 0) {
            $this->addError('Invalid user ID');
            $this->redirect('/admin/pending-registrations');
        }

        if ($this->registrationService->rejectRegistration($userId, $reason)) {
            $this->addSuccess('Registration rejected');
        } else {
            $this->addError('Failed to reject registration');
        }

        $this->redirect('/admin/pending-registrations');
    }
}

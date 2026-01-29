<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\TwoFactorAuth;
use App\Services\TwoFactorService;
use App\Services\EmailService;
use App\Middleware\TwoFactorMiddleware;

class AuthController extends Controller
{
    private User $userModel;
    private TwoFactorAuth $twoFactorModel;
    private TwoFactorMiddleware $twoFactorMiddleware;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->twoFactorModel = new TwoFactorAuth();
        $this->twoFactorMiddleware = new TwoFactorMiddleware();
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        // Get flash messages (this clears them from session)
        $errors = $this->getErrors();
        $success = $this->getSuccess();
        $csrfToken = $this->generateCsrfToken();

        $this->view('auth.login', [
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * Handle login form submission
     */
    public function handleLogin(): void
    {
        // Verify CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/login');
        }

        // Validate input
        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');

        if (empty($username)) {
            $this->addError('Username is required');
            $this->redirect('/login');
        }

        if (empty($password)) {
            $this->addError('Password is required');
            $this->redirect('/login');
        }

        // Attempt authentication
        $user = $this->userModel->authenticate($username, $password);

        if (!$user) {
            $this->addError('Invalid username or password');
            $this->redirect('/login');
        }

        // Check if account is locked out
        if ($this->userModel->isLockedOut($user['id'])) {
            $this->addError('Account is locked. Please try again later');
            $this->redirect('/login');
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            // Store pending user for email verification
            $this->session->regenerate();
            $this->session->set('pending_user_id', $user['id']);
            $this->redirect('/verify-email');
        }

        // Create session
        $this->session->regenerate();
        $this->session->set('user_id', $user['id']);
        $this->session->set('username', $user['username']);
        $this->session->set('email', $user['email']);
        $this->session->set('role', $user['role']);

        // Check if 2FA is enabled for admins/superusers
        if ($user['role'] === 'superuser' || $user['role'] === 'admin') {
            if ($this->twoFactorModel->isEnabled($user['id'])) {
                // Store pending user and redirect to 2FA verification
                $this->twoFactorMiddleware->setPendingUserId($user['id']);
                $this->redirect('/auth/2fa-verify');
            }
            // If 2FA is not enabled, allow login but they can set it up later
        }

        $this->addSuccess('Welcome, ' . $user['username'] . '!');
        $this->redirect('/dashboard');
    }

    /**
     * Show 2FA setup page
     */
    public function show2faSetup(): void
    {
        $this->requireAuth();

        $user = $this->getUser();

        // Only allow setup for admins and superusers
        if (!in_array($user['role'], ['superuser', 'admin'])) {
            $this->redirect('/dashboard');
        }

        // Check if 2FA is already enabled
        if ($this->twoFactorModel->isEnabled($user['id'])) {
            $this->addSuccess('2FA is already enabled');
            $this->redirect('/dashboard');
        }

        // Generate secret and QR code
        $secret = TwoFactorService::generateSecret();
        $qrCode = TwoFactorService::generateQrCode($secret, $user['username']);

        // Store secret in session for verification
        $this->session->set('pending_2fa_secret', $secret);

        $this->view('auth.2fa-setup', [
            'user' => $user,
            'qrCode' => $qrCode,
            'secret' => $secret,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Verify and enable 2FA
     */
    public function verify2faSetup(): void
    {
        $this->requireAuth();

        $user = $this->getUser();

        // Verify CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/auth/2fa-setup');
        }

        // Get the code from form
        $code = trim($this->post('code', ''));

        if (empty($code)) {
            $this->addError('Please enter the 6-digit code from your authenticator');
            $this->redirect('/auth/2fa-setup');
        }

        // Get secret from session
        $secret = $this->session->get('pending_2fa_secret');

        if (!$secret) {
            $this->addError('2FA setup expired. Please try again');
            $this->redirect('/auth/2fa-setup');
        }

        // Verify the code
        if (!TwoFactorService::verifyCode($secret, $code)) {
            $this->addError('Invalid code. Please try again');
            $this->redirect('/auth/2fa-setup');
        }

        // Generate and hash backup codes
        $backupData = TwoFactorService::generateAndHashBackupCodes();

        // Enable 2FA
        $this->twoFactorModel->enable($user['id'], $secret, $backupData['hashed']);

        // Clear session data
        $this->session->delete('pending_2fa_secret');

        $this->addSuccess('2FA has been enabled successfully');

        $this->view('auth.2fa-backup-codes', [
            'user' => $user,
            'backupCodes' => $backupData['codes']
        ]);
    }

    /**
     * Show 2FA verification page
     */
    public function show2faVerify(): void
    {
        // Check if user is pending 2FA verification
        if (!$this->isLoggedIn() && !$this->session->has('pending_user_id')) {
            $this->redirect('/login');
        }

        $csrfToken = $this->generateCsrfToken();

        $this->view('auth.2fa-verify', [
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * Handle 2FA verification
     */
    public function handle2faVerify(): void
    {
        // Check if user has pending 2FA
        $userId = $this->isLoggedIn() ? $this->session->get('user_id') : $this->session->get('pending_user_id');

        if (!$userId) {
            $this->redirect('/login');
        }

        // Verify CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/auth/2fa-verify');
        }

        $code = trim($this->post('code', ''));

        if (empty($code)) {
            $this->addError('Please enter the 6-digit code or a backup code');
            $this->redirect('/auth/2fa-verify');
        }

        $secret = $this->twoFactorModel->getSecret($userId);

        if (!$secret) {
            $this->addError('2FA is not configured');
            $this->redirect('/login');
        }

        // Try to verify TOTP code first
        if (TwoFactorService::verifyCode($secret, $code)) {
            $this->twoFactorModel->updateLastUsed($userId);
            $this->twoFactorMiddleware->markVerified();

            // Clear pending user if this was post-login 2FA
            if ($this->session->has('pending_user_id')) {
                $this->session->delete('pending_user_id');
                // Session should already have user_id set from login
            }

            $this->addSuccess('2FA verification successful');
            $this->redirect('/dashboard');
            return;
        }

        // Try backup code (remove spaces and dashes, then hash)
        $formattedCode = str_replace([' ', '-'], '', $code);
        $backupCodes = $this->twoFactorModel->getBackupCodes($userId);

        if (TwoFactorService::verifyBackupCode($formattedCode, $backupCodes)) {
            // Remove used backup code
            $this->twoFactorModel->removeBackupCode($userId, TwoFactorService::hashBackupCode($formattedCode));
            $this->twoFactorMiddleware->markVerified();

            // Clear pending user if this was post-login 2FA
            if ($this->session->has('pending_user_id')) {
                $this->session->delete('pending_user_id');
            }

            $this->addSuccess('2FA verified using backup code');
            $this->redirect('/dashboard');
            return;
        }

        $this->addError('Invalid code');
        $this->redirect('/auth/2fa-verify');
    }

    /**
     * Show email verification page
     */
    public function showVerifyEmail(): void
    {
        $pendingUserId = $this->session->get('pending_user_id');

        if (!$pendingUserId) {
            $this->redirect('/login');
        }

        $user = $this->userModel->find($pendingUserId);

        if (!$user || $user['email_verified']) {
            $this->redirect('/login');
        }

        // Check if token is in URL (from email link) - auto-verify
        $urlToken = trim($this->get('token', ''));
        if (!empty($urlToken)) {
            // Call handleVerifyEmail to process the token
            $this->handleVerifyEmail();
            return;
        }

        // Send verification email if not already sent in this session
        if (!$this->session->get('verify_email_sent')) {
            if (!$user['email_verification_token']) {
                $token = $this->userModel->generateEmailVerificationToken($pendingUserId);
            } else {
                $token = $user['email_verification_token'];
            }

            EmailService::sendEmailVerification($user['email'], $user['username'], $token);
            $this->session->set('verify_email_sent', true);
        }

        // Get flash messages
        $errors = $this->getErrors();
        $success = $this->getSuccess();

        $this->view('auth.verify-email', [
            'user' => $user,
            'errors' => $errors,
            'success' => $success,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Handle email verification
     */
    public function handleVerifyEmail(): void
    {
        $pendingUserId = $this->session->get('pending_user_id');

        if (!$pendingUserId) {
            $this->redirect('/login');
        }

        // Check for token in URL (from email link) or form submission
        $token = trim($this->get('token', ''));

        if (empty($token)) {
            // Token not in URL, check form submission
            // Verify CSRF token for form submissions
            $csrfToken = $this->post('csrf_token');
            if (!$this->verifyCsrfToken($csrfToken)) {
                $this->addError('CSRF token validation failed');
                $this->redirect('/verify-email');
            }

            $token = trim($this->post('token', ''));

            if (empty($token)) {
                $this->addError('Please enter your verification code');
                $this->redirect('/verify-email');
            }
        }

        // Verify token
        if ($this->userModel->verifyEmailToken($pendingUserId, $token)) {
            $user = $this->userModel->find($pendingUserId);

            // Clear pending user and email sent flag
            $this->session->delete('pending_user_id');
            $this->session->delete('verify_email_sent');

            // Create session
            $this->session->regenerate();
            $this->session->set('user_id', $user['id']);
            $this->session->set('username', $user['username']);
            $this->session->set('email', $user['email']);
            $this->session->set('role', $user['role']);

            // Check if 2FA is needed
            if ($user['role'] === 'superuser' || $user['role'] === 'admin') {
                if ($this->twoFactorModel->isEnabled($user['id'])) {
                    $this->twoFactorMiddleware->setPendingUserId($user['id']);
                    $this->redirect('/auth/2fa-verify');
                }
                // If 2FA is not enabled, allow access - they can set it up later
            }

            $this->addSuccess('Email verified successfully');
            $this->redirect('/dashboard');
            return;
        }

        $this->addError('Invalid or expired verification code');
        $this->redirect('/verify-email');
    }

    /**
     * Show password reset request form
     */
    public function showForgotPassword(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $this->view('auth.forgot-password', [
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Handle password reset request
     */
    public function handleForgotPassword(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/forgot-password');
        }

        $email = trim($this->post('email', ''));

        if (empty($email)) {
            $this->addError('Email is required');
            $this->redirect('/forgot-password');
        }

        $user = $this->userModel->findByEmail($email);

        if ($user) {
            $token = $this->userModel->generatePasswordResetToken($user['id']);

            // Send password reset email
            EmailService::sendPasswordReset($user['email'], $user['username'], $token);
        }

        // Always show success message for security (don't reveal if email exists)
        $this->addSuccess('If an account with that email exists, a password reset link has been sent');
        $this->redirect('/login');
    }

    /**
     * Show password reset form
     */
    public function showResetPassword(): void
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $token = $this->get('token', '');

        if (empty($token)) {
            $this->addError('Invalid reset link');
            $this->redirect('/login');
        }

        $this->view('auth.reset-password', [
            'token' => $token,
            'csrfToken' => $this->generateCsrfToken()
        ]);
    }

    /**
     * Handle password reset
     */
    public function handleResetPassword(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/login');
        }

        $token = trim($this->post('token', ''));
        $password = $this->post('password', '');
        $passwordConfirm = $this->post('password_confirm', '');

        if (empty($token)) {
            $this->addError('Invalid reset token');
            $this->redirect('/login');
        }

        if (empty($password)) {
            $this->addError('Password is required');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            $this->addError('Passwords do not match');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        // Validate password complexity
        if (!$this->validatePasswordComplexity($password)) {
            $this->addError('Password must be at least 8 characters with uppercase, lowercase, number, and special character');
            $this->redirect('/reset-password?token=' . urlencode($token));
        }

        // Find user with token
        $users = $this->userModel->all();
        $userFound = false;

        foreach ($users as $user) {
            if ($user['password_reset_token'] === $token &&
                $this->userModel->verifyPasswordResetToken($user['id'], $token)) {

                $this->userModel->changePassword($user['id'], $password);
                $userFound = true;
                break;
            }
        }

        if (!$userFound) {
            $this->addError('Invalid or expired reset token');
            $this->redirect('/login');
        }

        $this->addSuccess('Password has been reset. Please login with your new password');
        $this->redirect('/login');
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        $this->session->flush();
        $this->addSuccess('You have been logged out');
        $this->redirect('/login');
    }

    /**
     * Validate password complexity
     */
    private function validatePasswordComplexity(string $password): bool
    {
        // At least 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // At least one uppercase letter
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // At least one lowercase letter
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // At least one number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // At least one special character
        if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'"\\|,.<>\/?]/', $password)) {
            return false;
        }

        return true;
    }
}

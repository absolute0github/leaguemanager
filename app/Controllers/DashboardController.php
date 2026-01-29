<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\TwoFactorMiddleware;
use App\Models\TwoFactorAuth;

class DashboardController extends Controller
{
    private TwoFactorMiddleware $twoFactorMiddleware;
    private TwoFactorAuth $twoFactorModel;

    public function __construct()
    {
        parent::__construct();
        $this->twoFactorMiddleware = new TwoFactorMiddleware();
        $this->twoFactorModel = new TwoFactorAuth();
    }

    /**
     * Show dashboard based on user role
     */
    public function index(): void
    {
        // Require authentication
        $this->requireAuth();

        $user = $this->getUser();

        // Enforce 2FA for admins and superusers ONLY if they have enabled it
        if (in_array($user['role'], ['superuser', 'admin'])) {
            // Only require verification if 2FA is actually enabled
            if ($this->twoFactorModel->isEnabled($user['id'])) {
                if (!$this->twoFactorMiddleware->verify()) {
                    $this->redirect('/auth/2fa-verify');
                }
            }
        }

        $role = $user['role'];

        // Route to role-specific dashboard
        match ($role) {
            'superuser', 'admin' => $this->redirect('/admin/dashboard'),
            'coach' => $this->redirect('/coach/dashboard'),
            'player' => $this->showPlayerDashboard(),
            default => $this->redirect('/login')
        };
    }

    /**
     * Show player dashboard
     */
    private function showPlayerDashboard(): void
    {
        $user = $this->getUser();

        // Get players linked to this user
        $players = $this->db->fetchAll(
            'SELECT * FROM players WHERE user_id = ? ORDER BY first_name, last_name',
            [$user['id']]
        );

        $this->view('dashboard.player', [
            'user' => $user,
            'players' => $players,
            'errors' => $this->getErrors(),
            'success' => $this->getSuccess()
        ]);
    }
}

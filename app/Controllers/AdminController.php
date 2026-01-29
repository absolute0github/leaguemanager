<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AdminService;

class AdminController extends Controller
{
    private AdminService $adminService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();

        $currentUser = $this->getUser();
        if (!in_array($currentUser['role'] ?? '', ['admin', 'superuser'])) {
            $this->addError('Access denied');
            $this->redirect('/dashboard');
        }

        $this->adminService = new AdminService();
    }

    /**
     * Render a view within the admin layout
     */
    protected function adminView(string $viewPath, array $data = []): void
    {
        // Capture the view content
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: $viewFile");
        }

        // Extract data for the view
        extract($data);

        // Capture the view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Add content to data for the layout
        $data['content'] = $content;

        // Render the admin layout with the content
        $this->view('layouts.admin', $data);
    }

    /**
     * ============================================
     * ADMIN DASHBOARD
     * ============================================
     */

    /**
     * Show admin dashboard
     */
    public function dashboard(): void
    {
        $stats = $this->adminService->getDashboardStats();

        $this->adminView('admin.dashboard', [
            'user' => $this->getUser(),
            'stats' => $stats,
        ]);
    }

    /**
     * ============================================
     * USER MANAGEMENT
     * ============================================
     */

    /**
     * List all users with filters
     */
    public function listUsers(): void
    {
        $limit = 25;
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $limit;

        $filters = [
            'role' => $this->get('role', ''),
            'status' => $this->get('status', ''),
            'search' => $this->get('search', ''),
        ];

        $users = $this->adminService->getUsers($filters, $limit, $offset);
        $totalCount = $this->adminService->getUserCount($filters);
        $totalPages = ceil($totalCount / $limit);

        $this->adminView('admin.users', [
            'user' => $this->getUser(),
            'users' => $users,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
        ]);
    }

    /**
     * Show user details
     */
    public function viewUser(): void
    {
        $userId = (int)$this->get('id', 0);
        if ($userId === 0) {
            $this->redirect('/admin/users');
        }

        $user = $this->adminService->getUser($userId);
        if (!$user) {
            $this->addError('User not found');
            $this->redirect('/admin/users');
        }

        $this->adminView('admin.view-user', [
            'user' => $this->getUser(),
            'targetUser' => $user,
        ]);
    }

    /**
     * Show create user form
     */
    public function createUserForm(): void
    {
        $this->adminView('admin.create-user', [
            'user' => $this->getUser(),
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle user creation
     */
    public function createUser(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/users');
        }

        $data = [
            'username' => $this->post('username', ''),
            'email' => $this->post('email', ''),
            'password' => $this->post('password', ''),
            'role' => $this->post('role', 'player'),
            'status' => $this->post('status', 'active'),
            'email_verified' => (bool)$this->post('email_verified'),
        ];

        // Validate
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            $this->addError('Username, email, and password are required');
            $this->redirect('/admin/users');
        }

        $result = $this->adminService->createUser($data);
        if (!$result) {
            $this->addError('Failed to create user (duplicate email or username)');
            $this->redirect('/admin/users');
        }

        $this->addSuccess('User created successfully');
        $this->redirect('/admin/users/view?id=' . $result);
    }

    /**
     * Show edit user form
     */
    public function editUserForm(): void
    {
        $userId = (int)$this->get('id', 0);
        if ($userId === 0) {
            $this->redirect('/admin/users');
        }

        $user = $this->adminService->getUser($userId);
        if (!$user) {
            $this->addError('User not found');
            $this->redirect('/admin/users');
        }

        $this->adminView('admin.edit-user', [
            'user' => $this->getUser(),
            'targetUser' => $user,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle user update
     */
    public function updateUser(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/users');
        }

        $userId = (int)$this->post('user_id', 0);
        if ($userId === 0) {
            $this->redirect('/admin/users');
        }

        $data = [];
        if (!empty($this->post('email'))) {
            $data['email'] = $this->post('email');
        }
        if (!empty($this->post('role'))) {
            $data['role'] = $this->post('role');
        }
        if (!empty($this->post('status'))) {
            $data['status'] = $this->post('status');
        }
        if (!empty($this->post('password'))) {
            $data['password'] = $this->post('password');
        }
        if (isset($_POST['email_verified'])) {
            $data['email_verified'] = true;
        }

        if ($this->adminService->updateUser($userId, $data)) {
            $this->addSuccess('User updated successfully');
        } else {
            $this->addError('Failed to update user');
        }

        $this->redirect('/admin/users/view?id=' . $userId);
    }

    /**
     * ============================================
     * PLAYER MANAGEMENT
     * ============================================
     */

    /**
     * List all players with filters
     */
    public function listPlayers(): void
    {
        $limit = 25;
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $limit;

        $filters = [
            'age_group' => $this->get('age_group', ''),
            'status' => $this->get('status', ''),
            'search' => $this->get('search', ''),
        ];

        $players = $this->adminService->getPlayers($filters, $limit, $offset);
        $totalCount = $this->adminService->getPlayerCount($filters);
        $totalPages = ceil($totalCount / $limit);

        $stats = [
            'ageGroups' => $this->adminService->getAgeGroupStats(),
            'statuses' => $this->adminService->getRegistrationStatusStats(),
        ];

        $this->adminView('admin.players', [
            'user' => $this->getUser(),
            'players' => $players,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'stats' => $stats,
        ]);
    }

    /**
     * Show player details
     */
    public function viewPlayer(): void
    {
        $playerId = (int)$this->get('id', 0);
        if ($playerId === 0) {
            $this->redirect('/admin/players');
        }

        $player = $this->adminService->getPlayer($playerId);
        if (!$player) {
            $this->addError('Player not found');
            $this->redirect('/admin/players');
        }

        $this->adminView('admin.view-player', [
            'user' => $this->getUser(),
            'player' => $player,
        ]);
    }

    /**
     * Show edit player form
     */
    public function editPlayerForm(): void
    {
        $playerId = (int)$this->get('id', 0);
        if ($playerId === 0) {
            $this->redirect('/admin/players');
        }

        $player = $this->adminService->getPlayer($playerId);
        if (!$player) {
            $this->addError('Player not found');
            $this->redirect('/admin/players');
        }

        $this->adminView('admin.edit-player', [
            'user' => $this->getUser(),
            'player' => $player,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle player update
     */
    public function updatePlayer(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/players');
        }

        $playerId = (int)$this->post('player_id', 0);
        if ($playerId === 0) {
            $this->redirect('/admin/players');
        }

        $data = [
            'first_name' => $this->post('first_name', ''),
            'last_name' => $this->post('last_name', ''),
            'phone' => $this->post('phone', ''),
            'birthdate' => $this->post('birthdate', ''),
            'street_address' => $this->post('street_address', ''),
            'city' => $this->post('city', ''),
            'state' => $this->post('state', ''),
            'zip_code' => $this->post('zip_code', ''),
            'age_group' => $this->post('age_group', ''),
            'shirt_size' => $this->post('shirt_size', ''),
            'primary_position' => $this->post('primary_position', ''),
            'secondary_position' => $this->post('secondary_position', ''),
            'school_name' => $this->post('school_name', ''),
            'grade_level' => $this->post('grade_level', ''),
            'registration_status' => $this->post('registration_status', ''),
        ];

        if ($this->adminService->updatePlayer($playerId, $data)) {
            $this->addSuccess('Player updated successfully');
        } else {
            $this->addError('Failed to update player');
        }

        $this->redirect('/admin/players/view?id=' . $playerId);
    }

    /**
     * ============================================
     * TEAM MANAGEMENT
     * ============================================
     */

    /**
     * List all teams
     */
    public function listTeams(): void
    {
        $limit = 25;
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $limit;

        $filters = [
            'league_id' => $this->get('league_id', ''),
            'age_group' => $this->get('age_group', ''),
            'search' => $this->get('search', ''),
        ];

        $teams = $this->adminService->getTeams($filters, $limit, $offset);
        $totalCount = $this->adminService->getTeamCount($filters);
        $totalPages = ceil($totalCount / $limit);
        $leagues = $this->adminService->getLeagues(100, 0);

        $this->adminView('admin.teams', [
            'user' => $this->getUser(),
            'teams' => $teams,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'leagues' => $leagues,
        ]);
    }

    /**
     * Show team details with roster
     */
    public function viewTeam(): void
    {
        $teamId = (int)$this->get('id', 0);
        if ($teamId === 0) {
            $this->redirect('/admin/teams');
        }

        $team = $this->adminService->getTeam($teamId);
        if (!$team) {
            $this->addError('Team not found');
            $this->redirect('/admin/teams');
        }

        $this->adminView('admin.view-team', [
            'user' => $this->getUser(),
            'team' => $team,
        ]);
    }

    /**
     * Show create team form
     */
    public function createTeamForm(): void
    {
        $leagues = $this->adminService->getLeagues(100, 0);

        $this->adminView('admin.create-team', [
            'user' => $this->getUser(),
            'leagues' => $leagues,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle team creation
     */
    public function createTeam(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/teams');
        }

        $data = [
            'league_id' => $this->post('league_id', ''),
            'name' => $this->post('name', ''),
            'age_group' => $this->post('age_group', ''),
            'max_players' => $this->post('max_players', 15),
            'status' => $this->post('status', 'active'),
        ];

        if (empty($data['name'])) {
            $this->addError('Team name is required');
            $this->redirect('/admin/teams');
        }

        $result = $this->adminService->createTeam($data);
        if (!$result) {
            $this->addError('Failed to create team');
            $this->redirect('/admin/teams');
        }

        $this->addSuccess('Team created successfully');
        $this->redirect('/admin/teams/view?id=' . $result);
    }

    /**
     * Show edit team form
     */
    public function editTeamForm(): void
    {
        $teamId = (int)$this->get('id', 0);
        if ($teamId === 0) {
            $this->redirect('/admin/teams');
        }

        $team = $this->adminService->getTeam($teamId);
        if (!$team) {
            $this->addError('Team not found');
            $this->redirect('/admin/teams');
        }

        $leagues = $this->adminService->getLeagues(100, 0);

        $this->adminView('admin.edit-team', [
            'user' => $this->getUser(),
            'team' => $team,
            'leagues' => $leagues,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle team update
     */
    public function updateTeam(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/teams');
        }

        $teamId = (int)$this->post('team_id', 0);
        if ($teamId === 0) {
            $this->redirect('/admin/teams');
        }

        $data = [
            'name' => $this->post('name', ''),
            'age_group' => $this->post('age_group', ''),
            'max_players' => $this->post('max_players', 15),
            'status' => $this->post('status', 'active'),
            'league_id' => $this->post('league_id', ''),
        ];

        if ($this->adminService->updateTeam($teamId, $data)) {
            $this->addSuccess('Team updated successfully');
        } else {
            $this->addError('Failed to update team');
        }

        $this->redirect('/admin/teams/view?id=' . $teamId);
    }

    /**
     * ============================================
     * COACH MANAGEMENT
     * ============================================
     */

    /**
     * List all coaches
     */
    public function listCoaches(): void
    {
        $limit = 25;
        $page = max(1, (int)$this->get('page', 1));
        $offset = ($page - 1) * $limit;

        $filters = [
            'team_id' => $this->get('team_id', ''),
            'coach_type' => $this->get('coach_type', ''),
        ];

        $coaches = $this->adminService->getCoaches($filters, $limit, $offset);
        $totalCount = $this->adminService->getCoachCount($filters);
        $totalPages = ceil($totalCount / $limit);
        $teams = $this->adminService->getTeams([], 100, 0);

        $this->adminView('admin.coaches', [
            'user' => $this->getUser(),
            'coaches' => $coaches,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'teams' => $teams,
        ]);
    }

    /**
     * Show tryouts (placeholder for Phase 7+)
     */
    public function listTryouts(): void
    {
        $this->adminView('admin.tryouts', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * ============================================
     * MODULE MANAGEMENT
     * ============================================
     */

    /**
     * List all modules
     */
    public function listModules(): void
    {
        $moduleManager = \App\Modules\ModuleManager::getInstance();
        $modules = $moduleManager->getAllModules();

        $this->adminView('admin.modules', [
            'user' => $this->getUser(),
            'modules' => $modules,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Enable a module
     */
    public function enableModule(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/modules');
        }

        $moduleName = $this->post('module_name', '');
        if (empty($moduleName)) {
            $this->addError('Invalid module');
            $this->redirect('/admin/modules');
        }

        $moduleManager = \App\Modules\ModuleManager::getInstance();
        if ($moduleManager->enableModule($moduleName)) {
            $this->addSuccess("Module '{$moduleName}' enabled successfully");
        } else {
            $this->addError("Failed to enable module '{$moduleName}'");
        }

        $this->redirect('/admin/modules');
    }

    /**
     * Disable a module
     */
    public function disableModule(): void
    {
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/admin/modules');
        }

        $moduleName = $this->post('module_name', '');
        if (empty($moduleName)) {
            $this->addError('Invalid module');
            $this->redirect('/admin/modules');
        }

        $moduleManager = \App\Modules\ModuleManager::getInstance();
        if ($moduleManager->disableModule($moduleName)) {
            $this->addSuccess("Module '{$moduleName}' disabled successfully");
        } else {
            $this->addError("Failed to disable module '{$moduleName}'");
        }

        $this->redirect('/admin/modules');
    }
}

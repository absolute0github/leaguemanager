<?php

// Enable error reporting in development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Autoload classes using Composer
require_once BASE_PATH . '/vendor/autoload.php';

// Import necessary classes
use App\Core\App;

// Create application instance
$app = new App();

// Register routes
$app->registerRoutes(function($router) {
    // Auth routes - Login
    $router->get('/login', 'AuthController', 'showLogin');
    $router->post('/login', 'AuthController', 'handleLogin');
    $router->get('/logout', 'AuthController', 'logout');

    // Auth routes - 2FA
    $router->get('/auth/2fa-setup', 'AuthController', 'show2faSetup');
    $router->post('/auth/2fa-verify-setup', 'AuthController', 'verify2faSetup');
    $router->get('/auth/2fa-verify', 'AuthController', 'show2faVerify');
    $router->post('/auth/2fa-verify', 'AuthController', 'handle2faVerify');

    // Auth routes - Email Verification
    $router->get('/verify-email', 'AuthController', 'showVerifyEmail');
    $router->post('/verify-email', 'AuthController', 'handleVerifyEmail');

    // Auth routes - Password Reset
    $router->get('/forgot-password', 'AuthController', 'showForgotPassword');
    $router->post('/forgot-password', 'AuthController', 'handleForgotPassword');
    $router->get('/reset-password', 'AuthController', 'showResetPassword');
    $router->post('/reset-password', 'AuthController', 'handleResetPassword');

    // Dashboard routes
    $router->get('/dashboard', 'DashboardController', 'index');
    $router->get('/', 'DashboardController', 'index');

    // Admin routes
    // Dashboard
    $router->get('/admin/dashboard', 'AdminController', 'dashboard');

    // User management
    $router->get('/admin/users', 'AdminController', 'listUsers');
    $router->get('/admin/users/create', 'AdminController', 'createUserForm');
    $router->post('/admin/users/create', 'AdminController', 'createUser');
    $router->get('/admin/users/view', 'AdminController', 'viewUser');
    $router->get('/admin/users/edit', 'AdminController', 'editUserForm');
    $router->post('/admin/users/update', 'AdminController', 'updateUser');

    // Player management
    $router->get('/admin/players', 'AdminController', 'listPlayers');
    $router->get('/admin/players/view', 'AdminController', 'viewPlayer');
    $router->get('/admin/players/edit', 'AdminController', 'editPlayerForm');
    $router->post('/admin/players/update', 'AdminController', 'updatePlayer');

    // Team management
    $router->get('/admin/teams', 'AdminController', 'listTeams');
    $router->get('/admin/teams/create', 'AdminController', 'createTeamForm');
    $router->post('/admin/teams/create', 'AdminController', 'createTeam');
    $router->get('/admin/teams/view', 'AdminController', 'viewTeam');
    $router->get('/admin/teams/edit', 'AdminController', 'editTeamForm');
    $router->post('/admin/teams/update', 'AdminController', 'updateTeam');

    // Coach management
    $router->get('/admin/coaches', 'AdminController', 'listCoaches');

    // Tryouts (placeholder for Phase 7+)
    $router->get('/admin/tryouts', 'AdminController', 'listTryouts');

    // Module management
    $router->get('/admin/modules', 'AdminController', 'listModules');
    $router->post('/admin/modules/enable', 'AdminController', 'enableModule');
    $router->post('/admin/modules/disable', 'AdminController', 'disableModule');

    // Registration approval
    $router->get('/admin/pending-registrations', 'RegistrationController', 'pendingRegistrations');
    $router->post('/admin/registrations/approve', 'RegistrationController', 'approveRegistration');
    $router->post('/admin/registrations/reject', 'RegistrationController', 'rejectRegistration');

    // Coach routes
    $router->get('/coach/dashboard', 'CoachController', 'dashboard');
    $router->get('/coach/team', 'CoachController', 'viewTeam');
    $router->get('/coach/roster', 'CoachController', 'viewRoster');
    $router->get('/coach/player', 'CoachController', 'viewPlayer');
    $router->get('/coach/message', 'CoachController', 'showMessageForm');
    $router->post('/coach/send-message', 'CoachController', 'sendMessage');
    $router->post('/coach/update-jersey', 'CoachController', 'updateJerseyNumber');
    $router->post('/coach/update-status', 'CoachController', 'updatePlayerStatus');
    $router->get('/coach/export', 'CoachController', 'exportContacts');

    // Player routes
    $router->get('/player/players', 'PlayerController', 'myPlayers');
    $router->get('/player/add', 'PlayerController', 'addPlayerForm');
    $router->post('/player/add', 'PlayerController', 'addPlayer');
    $router->get('/player/profile', 'PlayerController', 'viewProfile');
    $router->get('/player/edit', 'PlayerController', 'editPlayerForm');
    $router->post('/player/update', 'PlayerController', 'updatePlayer');
    $router->get('/player/tryouts', 'PlayerController', 'listTryouts');

    // Public register route
    $router->get('/register', 'RegistrationController', 'showRegister');
    $router->post('/register', 'RegistrationController', 'handleRegister');
});

// Run the application
$app->run();

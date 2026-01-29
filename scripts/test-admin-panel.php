<?php

/**
 * Phase 6 Admin Panel Integration Tests
 * Tests all admin panel components and workflows
 */

// Set base path
define('BASE_PATH', dirname(__DIR__));

// Color codes for terminal output
const PASS = "\033[92m"; // Green
const FAIL = "\033[91m"; // Red
const INFO = "\033[94m"; // Blue
const RESET = "\033[0m";

$tests = [
    'File Structure' => [],
    'PHP Syntax' => [],
    'Routes' => [],
    'Controller Methods' => [],
    'Service Methods' => [],
    'View Files' => [],
];

$passed = 0;
$failed = 0;

// Helper function to report test result
function test($category, $name, $condition) {
    global $tests, $passed, $failed;

    if ($condition) {
        $tests[$category][$name] = 'PASS';
        $passed++;
        echo PASS . "✓ PASS" . RESET . " - $name\n";
    } else {
        $tests[$category][$name] = 'FAIL';
        $failed++;
        echo FAIL . "✗ FAIL" . RESET . " - $name\n";
    }
}

echo "\n" . INFO . "================================================" . RESET . "\n";
echo INFO . "Phase 6 Admin Panel Integration Tests" . RESET . "\n";
echo INFO . "================================================" . RESET . "\n\n";

// ============================================
// 1. FILE STRUCTURE TESTS
// ============================================
echo INFO . "[1. FILE STRUCTURE TESTS]" . RESET . "\n";

$adminViews = [
    'app/Views/admin/dashboard.php',
    'app/Views/admin/users.php',
    'app/Views/admin/create-user.php',
    'app/Views/admin/edit-user.php',
    'app/Views/admin/view-user.php',
    'app/Views/admin/players.php',
    'app/Views/admin/view-player.php',
    'app/Views/admin/edit-player.php',
    'app/Views/admin/teams.php',
    'app/Views/admin/create-team.php',
    'app/Views/admin/edit-team.php',
    'app/Views/admin/view-team.php',
    'app/Views/admin/coaches.php',
    'app/Views/admin/pending-registrations.php',
    'app/Views/layouts/admin.php',
    'app/Views/partials/admin-sidebar.php',
];

foreach ($adminViews as $file) {
    $path = BASE_PATH . '/' . $file;
    test('File Structure', $file, file_exists($path));
}

$adminFiles = [
    'app/Controllers/AdminController.php',
    'app/Services/AdminService.php',
];

foreach ($adminFiles as $file) {
    $path = BASE_PATH . '/' . $file;
    test('File Structure', $file, file_exists($path));
}

// ============================================
// 2. PHP SYNTAX TESTS
// ============================================
echo "\n" . INFO . "[2. PHP SYNTAX TESTS]" . RESET . "\n";

$phpFiles = array_merge($adminFiles, $adminViews);

foreach ($phpFiles as $file) {
    $path = BASE_PATH . '/' . $file;
    $output = [];
    $return = 0;
    exec("php -l " . escapeshellarg($path) . " 2>&1", $output, $return);
    test('PHP Syntax', basename($file), $return === 0);
}

// ============================================
// 3. ROUTES TESTS
// ============================================
echo "\n" . INFO . "[3. ROUTES TESTS]" . RESET . "\n";

$indexPath = BASE_PATH . '/public_html/index.php';
$indexContent = file_get_contents($indexPath);

$routes = [
    "'/admin/dashboard'" => "Admin dashboard route",
    "'/admin/users'" => "List users route",
    "'/admin/users/create'" => "Create user form route",
    "'/admin/users/view'" => "View user route",
    "'/admin/users/edit'" => "Edit user form route",
    "'/admin/players'" => "List players route",
    "'/admin/players/view'" => "View player route",
    "'/admin/teams'" => "List teams route",
    "'/admin/teams/create'" => "Create team form route",
    "'/admin/teams/view'" => "View team route",
    "'/admin/coaches'" => "List coaches route",
    "'/admin/pending-registrations'" => "Pending registrations route",
];

foreach ($routes as $routePath => $description) {
    test('Routes', $description, strpos($indexContent, $routePath) !== false);
}

// ============================================
// 4. CONTROLLER METHODS TESTS
// ============================================
echo "\n" . INFO . "[4. CONTROLLER METHODS TESTS]" . RESET . "\n";

$controllerPath = BASE_PATH . '/app/Controllers/AdminController.php';
$controllerContent = file_get_contents($controllerPath);

$controllerMethods = [
    'dashboard' => 'Dashboard method',
    'listUsers' => 'List users method',
    'createUserForm' => 'Create user form method',
    'createUser' => 'Create user handler',
    'viewUser' => 'View user method',
    'editUserForm' => 'Edit user form method',
    'updateUser' => 'Update user handler',
    'listPlayers' => 'List players method',
    'viewPlayer' => 'View player method',
    'editPlayerForm' => 'Edit player form method',
    'updatePlayer' => 'Update player handler',
    'listTeams' => 'List teams method',
    'createTeamForm' => 'Create team form method',
    'createTeam' => 'Create team handler',
    'viewTeam' => 'View team method',
    'editTeamForm' => 'Edit team form method',
    'updateTeam' => 'Update team handler',
    'listCoaches' => 'List coaches method',
];

foreach ($controllerMethods as $method => $description) {
    test('Controller Methods', $description, preg_match("/public function $method\(/", $controllerContent) === 1);
}

// ============================================
// 5. SERVICE METHODS TESTS
// ============================================
echo "\n" . INFO . "[5. SERVICE METHODS TESTS]" . RESET . "\n";

$servicePath = BASE_PATH . '/app/Services/AdminService.php';
$serviceContent = file_get_contents($servicePath);

$serviceMethods = [
    'getUsers' => 'Get users with filters',
    'getUserCount' => 'Get user count',
    'getUser' => 'Get single user',
    'createUser' => 'Create user',
    'updateUser' => 'Update user',
    'getPlayers' => 'Get players with filters',
    'getPlayerCount' => 'Get player count',
    'getPlayer' => 'Get single player',
    'updatePlayer' => 'Update player',
    'getAgeGroupStats' => 'Get age group statistics',
    'getRegistrationStatusStats' => 'Get registration status stats',
    'getTeams' => 'Get teams with filters',
    'getTeamCount' => 'Get team count',
    'getTeam' => 'Get single team',
    'createTeam' => 'Create team',
    'updateTeam' => 'Update team',
    'getCoaches' => 'Get coaches with filters',
    'getCoachCount' => 'Get coach count',
    'getLeagues' => 'Get leagues',
    'getDashboardStats' => 'Get dashboard statistics',
];

foreach ($serviceMethods as $method => $description) {
    test('Service Methods', $description, preg_match("/public function $method\(/", $serviceContent) === 1);
}

// ============================================
// 6. VIEW FILES CONTENT TESTS
// ============================================
echo "\n" . INFO . "[6. VIEW FILES CONTENT TESTS]" . RESET . "\n";

$viewChecks = [
    'app/Views/admin/dashboard.php' => ['statistics cards', 'quick actions', 'pending alerts'],
    'app/Views/admin/users.php' => ['filter', 'pagination', 'role', 'status'],
    'app/Views/admin/create-user.php' => ['form', 'password', 'role selector'],
    'app/Views/admin/edit-user.php' => ['csrf_token', 'email', 'role'],
    'app/Views/admin/view-user.php' => ['failed_login_attempts', 'lockout', 'user details'],
    'app/Views/admin/players.php' => ['age_group', 'registration_status', 'statistics'],
    'app/Views/admin/view-player.php' => ['parent', 'guardian', 'contact information'],
    'app/Views/admin/edit-player.php' => ['position', 'school_name', 'birthdate'],
    'app/Views/admin/teams.php' => ['league', 'age_group', 'max_players'],
    'app/Views/admin/create-team.php' => ['form', 'league selector', 'max players'],
    'app/Views/admin/edit-team.php' => ['progress bar', 'roster status', 'coaches'],
    'app/Views/admin/view-team.php' => ['roster', 'coaches', 'player links'],
    'app/Views/admin/coaches.php' => ['coach_type', 'team_id', 'status badges'],
    'app/Views/layouts/admin.php' => ['sidebar', 'topbar', 'flash messages'],
    'app/Views/partials/admin-sidebar.php' => ['nav-link', 'collapse', 'Dashboard'],
];

foreach ($viewChecks as $file => $keywords) {
    $path = BASE_PATH . '/' . $file;
    $content = file_get_contents($path);

    $hasKeywords = true;
    foreach ($keywords as $keyword) {
        if (stripos($content, $keyword) === false) {
            $hasKeywords = false;
            break;
        }
    }

    test('View Files', basename($file) . ' has required elements', $hasKeywords);
}

// ============================================
// 7. SECURITY CHECKS
// ============================================
echo "\n" . INFO . "[7. SECURITY CHECKS]" . RESET . "\n";

// Check CSRF token usage in forms
$csrfCount = substr_count($controllerContent, 'requireAuth()');
test('Security Checks', 'Admin controller requires authentication', $csrfCount >= 1);

// Check for CSRF token in views
$userCreateView = file_get_contents(BASE_PATH . '/app/Views/admin/create-user.php');
test('Security Checks', 'Create user form has CSRF token', strpos($userCreateView, 'csrf_token') !== false);

$userEditView = file_get_contents(BASE_PATH . '/app/Views/admin/edit-user.php');
test('Security Checks', 'Edit user form has CSRF token', strpos($userEditView, 'csrf_token') !== false);

// Check for prepared statement usage
test('Security Checks', 'Service uses prepared statements', substr_count($serviceContent, '?') > 10);

// Check for HTML escaping
test('Security Checks', 'Views use htmlspecialchars for output', substr_count($userCreateView, 'htmlspecialchars') > 0);

// ============================================
// 8. FORM VALIDATION CHECKS
// ============================================
echo "\n" . INFO . "[8. FORM VALIDATION CHECKS]" . RESET . "\n";

$userCreateView = file_get_contents(BASE_PATH . '/app/Views/admin/create-user.php');
test('Form Validation', 'User creation form has password validation', strpos($userCreateView, 'minlength') !== false);
test('Form Validation', 'User creation form has role selector', strpos($userCreateView, 'name="role"') !== false);
test('Form Validation', 'User creation form has email field', strpos($userCreateView, 'type="email"') !== false);

$playerEditView = file_get_contents(BASE_PATH . '/app/Views/admin/edit-player.php');
test('Form Validation', 'Player edit has birthdate field', strpos($playerEditView, 'type="date"') !== false);
test('Form Validation', 'Player edit has position selectors', strpos($playerEditView, 'name="primary_position"') !== false);

$teamCreateView = file_get_contents(BASE_PATH . '/app/Views/admin/create-team.php');
test('Form Validation', 'Team creation has league selector', strpos($teamCreateView, 'name="league_id"') !== false);
test('Form Validation', 'Team creation has max players input', strpos($teamCreateView, 'name="max_players"') !== false);

// ============================================
// 9. RESPONSIVE DESIGN CHECKS
// ============================================
echo "\n" . INFO . "[9. RESPONSIVE DESIGN CHECKS]" . RESET . "\n";

$layoutView = file_get_contents(BASE_PATH . '/app/Views/layouts/admin.php');
test('Responsive Design', 'Layout uses Bootstrap 5', strpos($layoutView, 'bootstrap') !== false);
test('Responsive Design', 'Layout has mobile menu toggle', strpos($layoutView, 'sidebarToggle') !== false);

$sidebarView = file_get_contents(BASE_PATH . '/app/Views/partials/admin-sidebar.php');
test('Responsive Design', 'Sidebar is mobile responsive', strpos($sidebarView, 'media') !== false || strpos($sidebarView, 'responsive') !== false);

$usersListView = file_get_contents(BASE_PATH . '/app/Views/admin/users.php');
test('Responsive Design', 'Users list uses responsive grid', strpos($usersListView, 'col-md') !== false || strpos($usersListView, 'table-responsive') !== false);

// ============================================
// 10. DATABASE INTEGRATION CHECKS
// ============================================
echo "\n" . INFO . "[10. DATABASE INTEGRATION CHECKS]" . RESET . "\n";

test('Database Integration', 'Service uses $db->prepare()', substr_count($serviceContent, 'prepare(') > 5);
test('Database Integration', 'Service uses bindParam', substr_count($serviceContent, 'bindParam') > 5 || substr_count($serviceContent, 'execute(') > 5);
test('Database Integration', 'Controller uses service methods', substr_count($controllerContent, '$this->adminService->') > 10);

// ============================================
// SUMMARY
// ============================================
echo "\n" . INFO . "================================================" . RESET . "\n";
echo INFO . "TEST SUMMARY" . RESET . "\n";
echo INFO . "================================================" . RESET . "\n\n";

foreach ($tests as $category => $results) {
    $categoryPassed = count(array_filter($results, fn($r) => $r === 'PASS'));
    $categoryTotal = count($results);
    $categoryPercent = $categoryTotal > 0 ? round(($categoryPassed / $categoryTotal) * 100) : 0;

    if ($categoryPassed === $categoryTotal) {
        echo PASS . "✓" . RESET . " $category: $categoryPassed/$categoryTotal (100%)\n";
    } else {
        echo FAIL . "✗" . RESET . " $category: $categoryPassed/$categoryTotal ($categoryPercent%)\n";
    }
}

echo "\n" . INFO . "TOTALS" . RESET . "\n";
echo INFO . "--------" . RESET . "\n";
echo PASS . "Passed: $passed" . RESET . "\n";
echo FAIL . "Failed: $failed" . RESET . "\n";
$total = $passed + $failed;
$percentage = $total > 0 ? round(($passed / $total) * 100) : 0;
echo INFO . "Total: $total ($percentage%)" . RESET . "\n";

if ($failed === 0) {
    echo "\n" . PASS . "✓ ALL TESTS PASSED!" . RESET . "\n";
} else {
    echo "\n" . FAIL . "✗ Some tests failed. Please review above." . RESET . "\n";
}

echo "\n";

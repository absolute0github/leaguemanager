<?php
// Admin sidebar navigation component
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$user = $user ?? [];

// Helper function to check if route is active
function isActive($routePrefix, $currentPath) {
    return strpos($currentPath, $routePrefix) === 0;
}
?>

<div class="sidebar bg-dark text-white" style="min-height: 100vh; width: 250px; position: fixed; left: 0; top: 0; overflow-y: auto; padding-top: 60px;">
    <nav class="navbar-nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="/admin/dashboard" class="nav-link <?php echo isActive('/admin/dashboard', $currentPath) ? 'active bg-primary' : ''; ?> text-white d-flex align-items-center py-3 px-4">
                <i class="fas fa-chart-line me-3"></i> Dashboard
            </a>
        </li>

        <!-- Divider -->
        <li class="nav-item">
            <hr class="my-2 border-secondary">
        </li>

        <!-- User Management Section -->
        <li class="nav-item">
            <a class="nav-link text-white-50 small text-uppercase fw-bold px-4 py-2" href="#userMenu" data-bs-toggle="collapse">
                <i class="fas fa-users me-2"></i> User Management
            </a>
        </li>
        <div class="collapse <?php echo strpos($currentPath, '/admin/users') === 0 ? 'show' : ''; ?>" id="userMenu">
            <a href="/admin/users" class="nav-link <?php echo isActive('/admin/users', $currentPath) && strpos($currentPath, '/users/') === false ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-list me-2"></i> All Users
            </a>
            <a href="/admin/users/create" class="nav-link <?php echo strpos($currentPath, '/users/create') !== false ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-plus-circle me-2"></i> Create User
            </a>
        </div>

        <!-- Player Management Section -->
        <li class="nav-item">
            <a class="nav-link text-white-50 small text-uppercase fw-bold px-4 py-2" href="#playerMenu" data-bs-toggle="collapse">
                <i class="fas fa-baseball me-2"></i> Player Management
            </a>
        </li>
        <div class="collapse <?php echo strpos($currentPath, '/admin/players') === 0 ? 'show' : ''; ?>" id="playerMenu">
            <a href="/admin/players" class="nav-link <?php echo isActive('/admin/players', $currentPath) && strpos($currentPath, '/players/') === false ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-list me-2"></i> All Players
            </a>
        </div>

        <!-- Team Management Section -->
        <li class="nav-item">
            <a class="nav-link text-white-50 small text-uppercase fw-bold px-4 py-2" href="#teamMenu" data-bs-toggle="collapse">
                <i class="fas fa-users-cog me-2"></i> Team Management
            </a>
        </li>
        <div class="collapse <?php echo strpos($currentPath, '/admin/teams') === 0 ? 'show' : ''; ?>" id="teamMenu">
            <a href="/admin/teams" class="nav-link <?php echo isActive('/admin/teams', $currentPath) && strpos($currentPath, '/teams/') === false ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-list me-2"></i> All Teams
            </a>
            <a href="/admin/teams/create" class="nav-link <?php echo strpos($currentPath, '/teams/create') !== false ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-plus-circle me-2"></i> Create Team
            </a>
        </div>

        <!-- Coach Management -->
        <li class="nav-item">
            <a href="/admin/coaches" class="nav-link <?php echo isActive('/admin/coaches', $currentPath) ? 'active bg-primary' : ''; ?> text-white d-flex align-items-center py-3 px-4">
                <i class="fas fa-chalkboard-user me-3"></i> Coach Management
            </a>
        </li>

        <!-- Divider -->
        <li class="nav-item">
            <hr class="my-2 border-secondary">
        </li>

        <!-- Registration Approvals -->
        <li class="nav-item">
            <a href="/admin/pending-registrations" class="nav-link <?php echo isActive('/admin/pending-registrations', $currentPath) ? 'active bg-primary' : ''; ?> text-white d-flex align-items-center py-3 px-4">
                <i class="fas fa-inbox me-3"></i>
                <span>Pending Approvals</span>
                <?php
                // In a real implementation, you could pass pending count from controller
                // if (!empty($pendingCount) && $pendingCount > 0):
                ?>
                <!-- <span class="badge bg-danger ms-auto">{{ $pendingCount }}</span> -->
                <?php
                // endif;
                ?>
            </a>
        </li>

        <!-- Modules (Superuser only) -->
        <?php if (($user['role'] ?? '') === 'superuser'): ?>
        <li class="nav-item">
            <a href="/admin/modules" class="nav-link <?php echo isActive('/admin/modules', $currentPath) ? 'active bg-primary' : ''; ?> text-white d-flex align-items-center py-3 px-4">
                <i class="fas fa-puzzle-piece me-3"></i> Modules
            </a>
        </li>
        <?php endif; ?>

        <!-- Module Sidebar Links -->
        <?php
        $moduleManager = \App\Modules\ModuleManager::getInstance();
        $moduleLinks = $moduleManager->executeHook('nav.admin.sidebar', ['user' => $user, 'currentPath' => $currentPath]);
        foreach ($moduleLinks as $linkHtml):
            echo $linkHtml;
        endforeach;
        ?>

        <!-- Divider -->
        <li class="nav-item">
            <hr class="my-2 border-secondary">
        </li>

        <!-- Admin Profile -->
        <li class="nav-item">
            <a href="/profile" class="nav-link text-white-75 d-flex align-items-center py-3 px-4">
                <i class="fas fa-user-circle me-3"></i> My Profile
            </a>
        </li>

        <!-- Logout -->
        <li class="nav-item">
            <a href="/logout" class="nav-link text-white-75 d-flex align-items-center py-3 px-4">
                <i class="fas fa-sign-out-alt me-3"></i> Logout
            </a>
        </li>
    </nav>
</div>

<style>
    .sidebar {
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    }

    .sidebar .nav-link {
        border-left: 3px solid transparent;
        transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-left-color: #0d6efd;
    }

    .sidebar .nav-link.active {
        border-left-color: #0d6efd;
    }

    .sidebar .collapse {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .text-white-75 {
        color: rgba(255, 255, 255, 0.75);
    }

    .text-white-50 {
        color: rgba(255, 255, 255, 0.5);
    }

    /* Ensure sidebar doesn't overlap content on small screens */
    @media (max-width: 768px) {
        .sidebar {
            position: relative;
            width: 100%;
            min-height: auto;
            padding-top: 10px;
        }

        body {
            padding-left: 0;
        }
    }
</style>

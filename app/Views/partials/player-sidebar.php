<?php
// Player sidebar navigation component
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$user = $user ?? [];

// Helper function to check if route is active
if (!function_exists('isActive')) {
    function isActive($routePrefix, $currentPath) {
        return strpos($currentPath, $routePrefix) === 0;
    }
}
?>

<div class="sidebar bg-dark text-white" style="min-height: 100vh; width: 250px; position: fixed; left: 0; top: 0; overflow-y: auto; padding-top: 60px;">
    <nav class="navbar-nav flex-column">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="/dashboard" class="nav-link <?php echo isActive('/dashboard', $currentPath) ? 'active bg-primary' : ''; ?> text-white d-flex align-items-center py-3 px-4">
                <i class="fas fa-home me-3"></i> Dashboard
            </a>
        </li>

        <!-- Divider -->
        <li class="nav-item">
            <hr class="my-2 border-secondary">
        </li>

        <!-- Players Section -->
        <li class="nav-item">
            <a href="/player" class="nav-link <?php echo isActive('/player', $currentPath) && !isActive('/tryouts', $currentPath) ? 'active bg-primary' : ''; ?> text-white d-flex align-items-center py-3 px-4">
                <i class="fas fa-users me-3"></i> My Players
            </a>
        </li>

        <!-- Tryouts Section -->
        <li class="nav-item">
            <a class="nav-link text-white-50 small text-uppercase fw-bold px-4 py-2" href="#tryoutsMenu" data-bs-toggle="collapse">
                <i class="fas fa-calendar-check me-2"></i> Tryouts
            </a>
        </li>
        <div class="collapse <?php echo strpos($currentPath, '/tryouts') === 0 ? 'show' : ''; ?>" id="tryoutsMenu">
            <a href="/tryouts" class="nav-link <?php echo $currentPath === '/tryouts' || $currentPath === '/tryouts/' ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-search me-2"></i> Browse Tryouts
            </a>
            <a href="/tryouts/my-registrations" class="nav-link <?php echo strpos($currentPath, '/tryouts/my-registrations') !== false ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-clipboard-list me-2"></i> My Registrations
            </a>
        </div>

        <!-- Module Sidebar Links -->
        <?php
        $moduleManager = \App\Modules\ModuleManager::getInstance();
        $moduleLinks = $moduleManager->executeHook('nav.player.sidebar', ['user' => $user, 'currentPath' => $currentPath]);
        foreach ($moduleLinks as $linkHtml):
            echo $linkHtml;
        endforeach;
        ?>

        <!-- Divider -->
        <li class="nav-item">
            <hr class="my-2 border-secondary">
        </li>

        <!-- Profile -->
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

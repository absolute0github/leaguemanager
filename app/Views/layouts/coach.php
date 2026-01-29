<?php
use App\Modules\Hooks;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Coach Portal'); ?> - IVL Baseball League</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .sidebar {
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #343a40;
            width: 220px;
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: #adb5bd;
            padding: 10px 20px;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255,255,255,0.1);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .sidebar-heading {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6c757d;
            padding: 15px 20px 5px;
        }

        .main-content {
            margin-left: 220px;
            padding: 20px;
            min-height: calc(100vh - 56px);
        }

        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }

            .sidebar.show {
                display: block;
                width: 100%;
            }

            .main-content {
                margin-left: 0;
            }
        }

        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .stat-card.info { border-left-color: #0dcaf0; }

        .player-card {
            transition: box-shadow 0.2s;
        }

        .player-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .jersey-badge {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <button class="btn btn-primary d-md-none me-2" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand" href="/coach/dashboard">
                <i class="fas fa-baseball"></i> Coach Portal
            </a>

            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        <?php echo htmlspecialchars($user['username'] ?? 'Coach'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/dashboard"><i class="fas fa-home me-2"></i>Main Dashboard</a></li>
                        <li><a class="dropdown-item" href="/profile"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="position-sticky pt-3">
            <div class="sidebar-heading">Team Management</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/coach/dashboard') !== false ? 'active' : ''; ?>" href="/coach/dashboard">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/coach/team') !== false ? 'active' : ''; ?>" href="/coach/team">
                        <i class="fas fa-users"></i> My Team
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/coach/roster') !== false ? 'active' : ''; ?>" href="/coach/roster">
                        <i class="fas fa-clipboard-list"></i> Roster
                    </a>
                </li>
            </ul>

            <div class="sidebar-heading">Communication</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/coach/message') !== false ? 'active' : ''; ?>" href="/coach/message">
                        <i class="fas fa-envelope"></i> Send Message
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], '/coach/export') !== false ? 'active' : ''; ?>" href="/coach/export">
                        <i class="fas fa-download"></i> Export Contacts
                    </a>
                </li>
            </ul>

            <!-- Module Links -->
            <?php echo Hooks::render('nav.coach.sidebar', ['current_path' => $_SERVER['REQUEST_URI']]); ?>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" style="margin-top: 56px;">
        <!-- Flash Messages -->
        <?php if (!empty($success) && is_array($success)): ?>
            <?php foreach ($success as $msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors) && is_array($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        <?php elseif (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Page Content -->
        <?php echo $content ?? ''; ?>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Sidebar Toggle -->
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    </script>
</body>
</html>

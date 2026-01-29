<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin Panel'); ?> - IVL Baseball League Manager</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Admin CSS -->
    <link rel="stylesheet" href="/assets/css/admin.css">

    <style>
        body {
            padding-left: 250px;
        }

        .main-content {
            margin-left: 0;
            padding-top: 60px;
        }

        .topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            z-index: 1030;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .topbar-logo {
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            body {
                padding-left: 0;
            }

            .topbar {
                left: 0;
            }

            .sidebar {
                display: none;
            }

            .sidebar.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Top Navigation Bar -->
    <div class="topbar">
        <div class="topbar-left">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-logo">
                <i class="fas fa-baseball"></i> IVL Admin
            </div>
        </div>

        <div class="topbar-right">
            <div class="user-menu dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($user['username'] ?? 'Admin'); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/profile">My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <?php include __DIR__ . '/../partials/admin-sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Flash Messages -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 70px; right: 20px; z-index: 1050; min-width: 300px;">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 70px; right: 20px; z-index: 1050; min-width: 300px;">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <?php echo $content ?? ''; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (optional, if needed for admin functionality) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Admin JS -->
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar?.classList.toggle('show');
            });
        }

        // Close sidebar when a link is clicked on mobile
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    sidebar?.classList.remove('show');
                }
            });
        });

        // Auto-dismiss alerts after 5 seconds
        document.querySelectorAll('.alert').forEach(alert => {
            if (alert.getAttribute('role') === 'alert') {
                const bsAlert = new bootstrap.Alert(alert);
                setTimeout(() => bsAlert.close(), 5000);
            }
        });
    </script>

    <style>
        .admin-container {
            padding: 20px;
        }

        /* Smooth transitions */
        .sidebar {
            transition: all 0.3s ease;
        }

        .nav-link {
            transition: all 0.2s ease;
        }

        /* Better form styling for admin */
        .admin-form .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .admin-form .form-control,
        .admin-form .form-select {
            border-radius: 0.375rem;
        }

        /* Table improvements */
        .admin-table {
            font-size: 0.95rem;
        }

        .admin-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-top: 2px solid #dee2e6;
        }

        .admin-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Badge improvements */
        .badge {
            padding: 0.4rem 0.6rem;
            font-size: 0.85rem;
        }

        /* Button spacing */
        .admin-actions {
            display: flex;
            gap: 5px;
        }

        .admin-actions .btn {
            white-space: nowrap;
        }

        /* Card improvements */
        .admin-card {
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .admin-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            transition: box-shadow 0.3s ease;
        }

        /* Modal improvements */
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .modal-footer {
            background-color: #f8f9fa;
            border-top: 2px solid #dee2e6;
        }
    </style>
</body>
</html>

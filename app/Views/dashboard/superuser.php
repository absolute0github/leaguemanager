<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superuser Dashboard - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container-fluid py-4">
        <!-- Alerts -->
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <?php foreach ($success as $message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h1 class="mb-4">Superuser Dashboard</h1>

        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card border-left-primary">
                    <div class="card-body">
                        <h5 class="card-title">Manage Users</h5>
                        <p class="card-text text-muted">Create and manage admin and coach accounts</p>
                        <a href="/admin/users" class="btn btn-primary btn-sm">Go to Users</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card border-left-success">
                    <div class="card-body">
                        <h5 class="card-title">Manage Admins</h5>
                        <p class="card-text text-muted">Create and manage admin accounts</p>
                        <a href="/admin/users?role=admin" class="btn btn-success btn-sm">Go to Admins</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card border-left-info">
                    <div class="card-body">
                        <h5 class="card-title">System Settings</h5>
                        <p class="card-text text-muted">Configure system settings and modules</p>
                        <a href="/admin/settings" class="btn btn-info btn-sm">Go to Settings</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card border-left-warning">
                    <div class="card-body">
                        <h5 class="card-title">Reports</h5>
                        <p class="card-text text-muted">View system reports and analytics</p>
                        <a href="/admin/reports" class="btn btn-warning btn-sm">Go to Reports</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Quick Actions</h5>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="/admin/users/create?role=admin" class="list-group-item list-group-item-action">
                            Create New Admin User
                        </a>
                        <a href="/admin/users/create?role=coach" class="list-group-item list-group-item-action">
                            Create New Coach User
                        </a>
                        <a href="/admin/settings" class="list-group-item list-group-item-action">
                            Configure System Settings
                        </a>
                        <a href="/admin/modules" class="list-group-item list-group-item-action">
                            Manage Modules
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">System Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Application:</strong> <?php echo $_ENV['APP_NAME']; ?></p>
                        <p><strong>Environment:</strong> <?php echo ucfirst($_ENV['APP_ENV']); ?></p>
                        <p><strong>Version:</strong> 1.0.0</p>
                        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                        <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

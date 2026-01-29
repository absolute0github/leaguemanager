<?php
// Get data from controller
$stats = $stats ?? [];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h1>Admin Dashboard</h1>
            <p class="text-muted">Welcome, <?php echo htmlspecialchars($user['username'] ?? 'Admin'); ?></p>
        </div>
    </div>

    <!-- Alert: Pending Registrations -->
    <?php if (($stats['pending_registrations'] ?? 0) > 0): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Pending Registrations!</strong>
            You have <?php echo $stats['pending_registrations']; ?> new player registration<?php echo $stats['pending_registrations'] > 1 ? 's' : ''; ?> awaiting approval.
            <a href="/admin/pending-registrations" class="alert-link">Review Now</a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Users Card -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-primary text-uppercase small fw-bold">Total Users</div>
                            <div class="h3 mb-0"><?php echo number_format($stats['total_users'] ?? 0); ?></div>
                        </div>
                        <div style="font-size: 2rem; color: #4e73df; opacity: 0.2;">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-primary">
                    <a href="/admin/users" class="small stretched-link">View All</a>
                </div>
            </div>
        </div>

        <!-- Total Players Card -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-success text-uppercase small fw-bold">Total Players</div>
                            <div class="h3 mb-0"><?php echo number_format($stats['total_players'] ?? 0); ?></div>
                        </div>
                        <div style="font-size: 2rem; color: #1cc88a; opacity: 0.2;">
                            <i class="fas fa-baseball"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-success">
                    <a href="/admin/players" class="small stretched-link">Manage Players</a>
                </div>
            </div>
        </div>

        <!-- Total Teams Card -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-info text-uppercase small fw-bold">Total Teams</div>
                            <div class="h3 mb-0"><?php echo number_format($stats['total_teams'] ?? 0); ?></div>
                        </div>
                        <div style="font-size: 2rem; color: #36b9cc; opacity: 0.2;">
                            <i class="fas fa-users-cog"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-info">
                    <a href="/admin/teams" class="small stretched-link">View Teams</a>
                </div>
            </div>
        </div>

        <!-- Pending Registrations Card -->
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-warning text-uppercase small fw-bold">Pending Approvals</div>
                            <div class="h3 mb-0"><?php echo number_format($stats['pending_registrations'] ?? 0); ?></div>
                        </div>
                        <div style="font-size: 2rem; color: #f6c23e; opacity: 0.2;">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top-warning">
                    <a href="/admin/pending-registrations" class="small stretched-link">Review Queue</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Statistics Row -->
    <div class="row mb-4">
        <!-- Total Coaches Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Coaches Assigned</h6>
                            <div class="h4 mb-0 mt-2"><?php echo number_format($stats['total_coaches'] ?? 0); ?></div>
                        </div>
                        <a href="/admin/coaches" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Leagues Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Active Leagues</h6>
                            <div class="h4 mb-0 mt-2"><?php echo number_format($stats['total_leagues'] ?? 0); ?></div>
                        </div>
                        <a href="/admin/leagues" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Users Card -->
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-0">Pending Accounts</h6>
                            <div class="h4 mb-0 mt-2"><?php echo number_format($stats['pending_users'] ?? 0); ?></div>
                        </div>
                        <a href="/admin/users?status=pending" class="btn btn-sm btn-outline-primary">Review</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col">
            <h5 class="mb-3">Quick Actions</h5>
            <div class="row g-2">
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <a href="/admin/users/create" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Create User
                    </a>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <a href="/admin/teams/create" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Create Team
                    </a>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <a href="/admin/pending-registrations" class="btn btn-warning w-100">
                        <i class="fas fa-inbox"></i> Review Registrations
                    </a>
                </div>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <a href="/admin/players" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search Players
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">System Information</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">PHP Version:</dt>
                        <dd class="col-sm-7"><code><?php echo phpversion(); ?></code></dd>

                        <dt class="col-sm-5">Current User:</dt>
                        <dd class="col-sm-7"><?php echo htmlspecialchars($user['username'] ?? 'Unknown'); ?></dd>

                        <dt class="col-sm-5">User Role:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'unknown')); ?>
                            </span>
                        </dd>

                        <dt class="col-sm-5">Current Time:</dt>
                        <dd class="col-sm-7"><?php echo date('M d, Y H:i:s'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Admin Resources</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <a href="/admin/users">Manage Users</a>
                            <small class="text-muted d-block">Create, edit, and manage user accounts</small>
                        </li>
                        <li class="mb-2">
                            <a href="/admin/players">Manage Players</a>
                            <small class="text-muted d-block">Search, view, and edit player information</small>
                        </li>
                        <li class="mb-2">
                            <a href="/admin/teams">Manage Teams</a>
                            <small class="text-muted d-block">Create teams and assign players and coaches</small>
                        </li>
                        <li class="mb-2">
                            <a href="/admin/coaches">Manage Coaches</a>
                            <small class="text-muted d-block">View and assign coaches to teams</small>
                        </li>
                        <li>
                            <a href="/admin/pending-registrations">Review Registrations</a>
                            <small class="text-muted d-block">Approve or reject new player registrations</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-top-primary {
        border-top-color: #4e73df !important;
    }
    .text-primary {
        color: #4e73df !important;
    }

    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-top-success {
        border-top-color: #1cc88a !important;
    }
    .text-success {
        color: #1cc88a !important;
    }

    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    .border-top-info {
        border-top-color: #36b9cc !important;
    }
    .text-info {
        color: #36b9cc !important;
    }

    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .border-top-warning {
        border-top-color: #f6c23e !important;
    }
    .text-warning {
        color: #f6c23e !important;
    }
</style>

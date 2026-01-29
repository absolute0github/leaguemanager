<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container-fluid py-4">
        <!-- Flash Messages -->
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

        <div class="row mb-4">
            <div class="col">
                <h1>Welcome, <?php echo htmlspecialchars($user['username'] ?? 'Player'); ?>!</h1>
                <p class="text-muted">Manage your players, view tryouts, and more</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-users fa-2x me-3"></i>
                            <div>
                                <h5 class="card-title mb-0">My Players</h5>
                                <small><?php echo count($players ?? []); ?> player(s)</small>
                            </div>
                        </div>
                        <a href="/player/players" class="btn btn-light mt-auto">View All</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-user-plus fa-2x me-3"></i>
                            <div>
                                <h5 class="card-title mb-0">Add Player</h5>
                                <small>Register a new player</small>
                            </div>
                        </div>
                        <a href="/player/add" class="btn btn-light mt-auto">Add Now</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-clipboard-list fa-2x me-3"></i>
                            <div>
                                <h5 class="card-title mb-0">Tryouts</h5>
                                <small>View available tryouts</small>
                            </div>
                        </div>
                        <a href="/player/tryouts" class="btn btn-light mt-auto">View Tryouts</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-3">
                <div class="card bg-secondary text-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-baseball-ball fa-2x me-3"></i>
                            <div>
                                <h5 class="card-title mb-0">Teams</h5>
                                <small>View team assignments</small>
                            </div>
                        </div>
                        <a href="/player/teams" class="btn btn-light mt-auto">My Teams</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Players Section -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>My Players</h5>
                        <a href="/player/add" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus me-1"></i>Add Player
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($players)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-user-plus fa-4x text-muted mb-3"></i>
                                <h5>No Players Yet</h5>
                                <p class="text-muted mb-4">You haven't added any players to your account.</p>
                                <a href="/player/add" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add Your First Player
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Age Group</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($players as $player): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></strong>
                                                    <?php if (!empty($player['email'])): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($player['email']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($player['age_group'])): ?>
                                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($player['age_group']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                        $statusColors = [
                                                            'active' => 'success',
                                                            'tryout' => 'warning',
                                                            'committed' => 'info',
                                                            'inactive' => 'secondary',
                                                        ];
                                                        $statusColor = $statusColors[$player['registration_status'] ?? 'active'] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $statusColor; ?>">
                                                        <?php echo ucfirst($player['registration_status'] ?? 'active'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="/player/profile?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        View
                                                    </a>
                                                    <a href="/player/edit?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                        Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Account Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-user-circle me-2"></i>Account Information</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td class="text-muted">Email:</td>
                                <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Role:</td>
                                <td><span class="badge bg-primary"><?php echo ucfirst($user['role'] ?? 'player'); ?></span></td>
                            </tr>
                            <tr>
                                <td class="text-muted">Status:</td>
                                <td>
                                    <?php
                                        $userStatusColors = ['active' => 'success', 'pending' => 'warning', 'inactive' => 'secondary'];
                                        $userStatusColor = $userStatusColors[$user['status'] ?? 'active'] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?php echo $userStatusColor; ?>">
                                        <?php echo ucfirst($user['status'] ?? 'active'); ?>
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Need Help?</h6>
                    </div>
                    <div class="card-body small">
                        <p class="mb-2"><strong>Adding Players:</strong> Click "Add Player" to register a new player for your account.</p>
                        <p class="mb-2"><strong>Tryouts:</strong> View upcoming tryout dates and register your players.</p>
                        <p class="mb-0"><strong>Questions?</strong> Contact the league administrator for assistance.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

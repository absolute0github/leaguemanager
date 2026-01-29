<?php
// Get data from controller
$user = $user ?? [];
$players = $players ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">My Players</li>
                </ol>
            </nav>
            <h2>My Players</h2>
            <p class="text-muted">Manage players associated with your account</p>
        </div>
        <div class="col-auto">
            <a href="/player/add" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Player
            </a>
        </div>
    </div>

    <?php if (empty($players)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-4"></i>
                <h4>No Players Yet</h4>
                <p class="text-muted mb-4">You haven't added any players to your account.</p>
                <a href="/player/add" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-2"></i> Add Your First Player
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($players as $player): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 100px;">Age Group:</td>
                                    <td>
                                        <?php if (!empty($player['age_group'])): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($player['age_group']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Email:</td>
                                    <td>
                                        <?php if (!empty($player['email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($player['email']); ?>">
                                                <?php echo htmlspecialchars($player['email']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Phone:</td>
                                    <td>
                                        <?php if (!empty($player['phone'])): ?>
                                            <?php echo htmlspecialchars($player['phone']); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Birthdate:</td>
                                    <td>
                                        <?php if (!empty($player['birthdate'])): ?>
                                            <?php
                                                $birthdate = new DateTime($player['birthdate']);
                                                echo $birthdate->format('M j, Y');
                                            ?>
                                        <?php else: ?>
                                            <span class="text-muted">Not set</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Status:</td>
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
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex gap-2">
                                <a href="/player/profile?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    View
                                </a>
                                <a href="/player/edit?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

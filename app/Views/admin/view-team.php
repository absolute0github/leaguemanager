<?php
// Get data from controller
$team = $team ?? [];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2><?php echo htmlspecialchars($team['name'] ?? ''); ?></h2>
            <p class="text-muted">
                <span class="badge bg-secondary"><?php echo htmlspecialchars($team['age_group'] ?? 'N/A'); ?></span>
                <span class="badge bg-primary ms-2"><?php echo htmlspecialchars($team['league_name'] ?? 'N/A'); ?></span>
            </p>
        </div>
        <div class="col-auto">
            <a href="/admin/teams/edit?id=<?php echo $team['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Team
            </a>
            <a href="/admin/teams" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Team Information -->
        <div class="col-md-8">
            <!-- Team Details -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Team Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Team Name</label>
                            <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($team['name'] ?? ''); ?></strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">League</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($team['league_name'] ?? 'N/A'); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Age Group</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($team['age_group'] ?? 'N/A'); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Status</label>
                            <p class="form-control-plaintext">
                                <?php
                                $statusClass = ($team['status'] ?? '') === 'active' ? 'success' : 'danger';
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($team['status'] ?? '')); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Max Players</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($team['max_players'] ?? '15'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Current Roster</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-info">
                                    <?php echo (count($team['roster'] ?? []) ?? 0); ?>/<?php echo htmlspecialchars($team['max_players'] ?? '15'); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coaches -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Coaches</h6>
                    <span class="badge bg-primary"><?php echo count($team['coaches'] ?? []) ?? 0; ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($team['coaches'])): ?>
                        <p class="text-muted small">No coaches assigned</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team['coaches'] as $coach): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($coach['username'] ?? ''); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo ($coach['coach_type'] === 'head') ? 'warning' : 'info'; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($coach['coach_type'] ?? '')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo htmlspecialchars($coach['email']); ?>">
                                                    <?php echo htmlspecialchars($coach['email']); ?>
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

            <!-- Roster -->
            <div class="card">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Team Roster</h6>
                    <span class="badge bg-info"><?php echo count($team['roster'] ?? []) ?? 0; ?> Players</span>
                </div>
                <div class="card-body">
                    <?php if (empty($team['roster'])): ?>
                        <p class="text-muted small">No players assigned to team</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jersey</th>
                                        <th>Player Name</th>
                                        <th>Age Group</th>
                                        <th>Position</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($team['roster'] as $player): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($player['jersey_number'] ?? '-'); ?></strong>
                                            </td>
                                            <td>
                                                <a href="/admin/players/view?id=<?php echo $player['id']; ?>">
                                                    <?php echo htmlspecialchars($player['first_name'] ?? ''); ?> <?php echo htmlspecialchars($player['last_name'] ?? ''); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?php echo htmlspecialchars($player['age_group'] ?? ''); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($player['primary_position'] ?? '-'); ?>
                                            </td>
                                            <td>
                                                <?php
                                                $statusClass = match($player['roster_status'] ?? '') {
                                                    'active' => 'success',
                                                    'inactive' => 'danger',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($player['roster_status'] ?? '')); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="/admin/players/view?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
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

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <a href="/admin/teams/edit?id=<?php echo $team['id']; ?>" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Team
                    </a>
                    <button type="button" class="btn btn-secondary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#addPlayerModal">
                        <i class="fas fa-user-plus"></i> Add Player
                    </button>
                </div>
            </div>

            <!-- Team Summary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Age Group:</dt>
                        <dd class="col-sm-6"><?php echo htmlspecialchars($team['age_group'] ?? '-'); ?></dd>

                        <dt class="col-sm-6">League:</dt>
                        <dd class="col-sm-6"><?php echo htmlspecialchars($team['league_name'] ?? '-'); ?></dd>

                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-<?php echo ($team['status'] === 'active') ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars(ucfirst($team['status'] ?? '')); ?>
                            </span>
                        </dd>

                        <dt class="col-sm-6">Roster:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-info">
                                <?php echo count($team['roster'] ?? []) ?? 0; ?>/<?php echo htmlspecialchars($team['max_players'] ?? '15'); ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Coaches Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Coaches</h6>
                </div>
                <div class="card-body small">
                    <?php if (empty($team['coaches'])): ?>
                        <p class="text-muted mb-0">No coaches assigned</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($team['coaches'] as $coach): ?>
                                <li class="mb-2">
                                    <strong><?php echo htmlspecialchars($coach['username']); ?></strong>
                                    <span class="badge bg-<?php echo ($coach['coach_type'] === 'head') ? 'warning' : 'info'; ?>">
                                        <?php echo htmlspecialchars(ucfirst($coach['coach_type'])); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Player Modal -->
<div class="modal fade" id="addPlayerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Player to Roster</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Add existing players to this team's roster.</p>
                <p class="text-muted small">This feature is currently in development.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
$team = $team ?? [];
$coaches = $coaches ?? [];
$stats = $stats ?? [];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-users me-2"></i>Team Details</h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($team['name'] ?? 'Unknown Team'); ?></p>
        </div>
        <a href="/coach/roster?id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-primary">
            <i class="fas fa-clipboard-list me-1"></i> View Full Roster
        </a>
    </div>

    <div class="row">
        <!-- Team Info Card -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Team Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 40%;">Team Name</th>
                            <td><?php echo htmlspecialchars($team['name'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Age Group</th>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($team['age_group'] ?? '-'); ?></span></td>
                        </tr>
                        <tr>
                            <th>League</th>
                            <td><?php echo htmlspecialchars($team['league_name'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th>Season</th>
                            <td><?php echo htmlspecialchars(($team['season'] ?? '') . ' ' . ($team['year'] ?? '')); ?></td>
                        </tr>
                        <tr>
                            <th>Max Players</th>
                            <td><?php echo $team['max_players'] ?? '-'; ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $statusColors = ['active' => 'success', 'forming' => 'warning', 'inactive' => 'secondary'];
                                $status = $team['status'] ?? 'unknown';
                                ?>
                                <span class="badge bg-<?php echo $statusColors[$status] ?? 'secondary'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Roster Stats Card -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Roster Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-4">
                            <div class="h2 mb-0 text-primary"><?php echo $stats['total_players'] ?? 0; ?></div>
                            <small class="text-muted">Total</small>
                        </div>
                        <div class="col-4">
                            <div class="h2 mb-0 text-success"><?php echo $stats['active_players'] ?? 0; ?></div>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-4">
                            <div class="h2 mb-0 text-warning"><?php echo $stats['injured_players'] ?? 0; ?></div>
                            <small class="text-muted">Injured</small>
                        </div>
                    </div>

                    <!-- Capacity Progress -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Roster Capacity</span>
                            <span><?php echo $stats['active_players'] ?? 0; ?> / <?php echo $team['max_players'] ?? 15; ?></span>
                        </div>
                        <?php $percent = ($team['max_players'] ?? 15) > 0 ? (($stats['active_players'] ?? 0) / ($team['max_players'] ?? 15)) * 100 : 0; ?>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-<?php echo $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success'); ?>"
                                 style="width: <?php echo min(100, $percent); ?>%">
                                <?php echo round($percent); ?>%
                            </div>
                        </div>
                    </div>

                    <!-- Position Breakdown -->
                    <?php if (!empty($stats['positions'])): ?>
                        <h6 class="text-muted mb-2">Positions</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($stats['positions'] as $position => $count): ?>
                                <span class="badge bg-info">
                                    <?php echo htmlspecialchars($position); ?>: <?php echo $count; ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Coaching Staff -->
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Coaching Staff</h5>
        </div>
        <div class="card-body">
            <?php if (empty($coaches)): ?>
                <p class="text-muted mb-0">No coaches assigned to this team.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($coaches as $coach): ?>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-<?php echo $coach['coach_type'] === 'head' ? 'primary' : 'secondary'; ?> text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong><?php echo htmlspecialchars($coach['username']); ?></strong>
                                        <br>
                                        <span class="badge bg-<?php echo $coach['coach_type'] === 'head' ? 'primary' : ($coach['coach_type'] === 'assistant' ? 'info' : 'secondary'); ?>">
                                            <?php echo ucfirst($coach['coach_type']); ?> Coach
                                        </span>
                                        <?php if ($coach['email']): ?>
                                            <br>
                                            <small>
                                                <a href="mailto:<?php echo htmlspecialchars($coach['email']); ?>">
                                                    <?php echo htmlspecialchars($coach['email']); ?>
                                                </a>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-2">
                    <a href="/coach/roster?id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-outline-primary w-100">
                        <i class="fas fa-clipboard-list me-2"></i>View Roster
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="/coach/message?id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-outline-primary w-100">
                        <i class="fas fa-envelope me-2"></i>Message Team
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="/coach/export?id=<?php echo $team['id'] ?? 0; ?>&type=parents" class="btn btn-outline-primary w-100">
                        <i class="fas fa-download me-2"></i>Export Parents
                    </a>
                </div>
                <div class="col-md-3 mb-2">
                    <a href="/coach/export?id=<?php echo $team['id'] ?? 0; ?>&type=players" class="btn btn-outline-primary w-100">
                        <i class="fas fa-download me-2"></i>Export Players
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

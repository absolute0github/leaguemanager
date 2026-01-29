<?php
$teams = $teams ?? [];
$teamStats = $teamStats ?? [];
$totalPlayers = $totalPlayers ?? 0;
$coach = $coach ?? null;

use App\Modules\Hooks;
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-tachometer-alt me-2"></i>Coach Dashboard</h2>
            <p class="text-muted mb-0">
                Welcome back<?php echo $coach ? ', ' . htmlspecialchars($user['username']) : ''; ?>!
                <?php if ($coach): ?>
                    <span class="badge bg-<?php echo $coach['coach_type'] === 'head' ? 'primary' : 'secondary'; ?>">
                        <?php echo ucfirst($coach['coach_type']); ?> Coach
                    </span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">My Teams</h6>
                            <h3 class="mb-0"><?php echo count($teams); ?></h3>
                        </div>
                        <div class="text-primary opacity-50">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Players</h6>
                            <h3 class="mb-0"><?php echo $totalPlayers; ?></h3>
                        </div>
                        <div class="text-success opacity-50">
                            <i class="fas fa-running fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Quick Actions</h6>
                            <a href="/coach/message" class="btn btn-sm btn-info">
                                <i class="fas fa-envelope"></i> Message
                            </a>
                        </div>
                        <div class="text-info opacity-50">
                            <i class="fas fa-bolt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card stat-card warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Export</h6>
                            <a href="/coach/export" class="btn btn-sm btn-warning">
                                <i class="fas fa-download"></i> Contacts
                            </a>
                        </div>
                        <div class="text-warning opacity-50">
                            <i class="fas fa-file-export fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Module Widgets -->
    <div class="row mb-4">
        <?php echo Hooks::render('dashboard.coach', ['user' => $user ?? null, 'teams' => $teams]); ?>
    </div>

    <!-- Teams List -->
    <?php if (empty($teams)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            You are not currently assigned to any team. Please contact an administrator.
        </div>
    <?php else: ?>
        <h4 class="mb-3"><i class="fas fa-baseball me-2"></i>My Teams</h4>
        <div class="row">
            <?php foreach ($teams as $team): ?>
                <?php $stats = $teamStats[$team['id']] ?? []; ?>
                <div class="col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 player-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>
                                <?php echo htmlspecialchars($team['name']); ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($team['age_group']); ?></span>
                                <span class="badge bg-info"><?php echo htmlspecialchars($team['league_name']); ?></span>
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($team['season'] . ' ' . $team['year']); ?></span>
                            </div>

                            <div class="row text-center mb-3">
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h4 mb-0 text-success"><?php echo $stats['active_players'] ?? 0; ?></div>
                                        <small class="text-muted">Active</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h4 mb-0 text-warning"><?php echo $stats['injured_players'] ?? 0; ?></div>
                                        <small class="text-muted">Injured</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-2">
                                        <div class="h4 mb-0 text-primary"><?php echo $stats['available_spots'] ?? 0; ?></div>
                                        <small class="text-muted">Spots</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Roster Progress -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small>Roster Capacity</small>
                                    <small><?php echo ($stats['active_players'] ?? 0); ?>/<?php echo $team['max_players']; ?></small>
                                </div>
                                <?php $percent = $team['max_players'] > 0 ? (($stats['active_players'] ?? 0) / $team['max_players']) * 100 : 0; ?>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?php echo $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success'); ?>"
                                         style="width: <?php echo min(100, $percent); ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex gap-2">
                                <a href="/coach/roster?id=<?php echo $team['id']; ?>" class="btn btn-primary btn-sm flex-fill">
                                    <i class="fas fa-clipboard-list me-1"></i> Roster
                                </a>
                                <a href="/coach/team?id=<?php echo $team['id']; ?>" class="btn btn-outline-primary btn-sm flex-fill">
                                    <i class="fas fa-info-circle me-1"></i> Details
                                </a>
                                <a href="/coach/message?id=<?php echo $team['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-envelope"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

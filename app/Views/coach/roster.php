<?php
$team = $team ?? [];
$roster = $roster ?? [];
$stats = $stats ?? [];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-clipboard-list me-2"></i>Team Roster</h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($team['name'] ?? 'Unknown Team'); ?>
                <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($team['age_group'] ?? ''); ?></span>
            </p>
        </div>
        <div class="btn-group">
            <a href="/coach/export?id=<?php echo $team['id'] ?? 0; ?>&type=all" class="btn btn-outline-primary">
                <i class="fas fa-download me-1"></i> Export All
            </a>
            <a href="/coach/message?id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-primary">
                <i class="fas fa-envelope me-1"></i> Message Team
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2 text-center">
                    <div class="h4 mb-0 text-primary"><?php echo $stats['total_players'] ?? 0; ?></div>
                    <small class="text-muted">Total Players</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2 text-center">
                    <div class="h4 mb-0 text-success"><?php echo $stats['active_players'] ?? 0; ?></div>
                    <small class="text-muted">Active</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2 text-center">
                    <div class="h4 mb-0 text-warning"><?php echo $stats['injured_players'] ?? 0; ?></div>
                    <small class="text-muted">Injured</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-2">
            <div class="card bg-light">
                <div class="card-body py-2 text-center">
                    <div class="h4 mb-0 text-info"><?php echo $stats['available_spots'] ?? 0; ?></div>
                    <small class="text-muted">Available Spots</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Roster Table -->
    <?php if (empty($roster)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No players on this roster yet.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Players (<?php echo count($roster); ?>)</h5>
                    <input type="text" class="form-control form-control-sm" id="rosterSearch"
                           placeholder="Search players..." style="max-width: 250px;">
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="rosterTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Player</th>
                            <th>Position</th>
                            <th>Contact</th>
                            <th>Parent/Guardian</th>
                            <th>Status</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roster as $player): ?>
                            <tr class="player-row" data-name="<?php echo strtolower($player['first_name'] . ' ' . $player['last_name']); ?>">
                                <td>
                                    <div class="jersey-badge bg-primary text-white rounded-circle">
                                        <?php echo $player['jersey_number'] ?? '-'; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($player['age_group'] ?? ''); ?>
                                            <?php if ($player['birthdate']): ?>
                                                | <?php echo date('M j, Y', strtotime($player['birthdate'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($player['primary_position']): ?>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($player['primary_position']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($player['secondary_position']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($player['secondary_position']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($player['phone']): ?>
                                        <a href="tel:<?php echo htmlspecialchars($player['phone']); ?>" class="text-decoration-none">
                                            <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($player['phone']); ?>
                                        </a><br>
                                    <?php endif; ?>
                                    <?php if ($player['email']): ?>
                                        <a href="mailto:<?php echo htmlspecialchars($player['email']); ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($player['email']); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($player['parents'])): ?>
                                        <?php foreach ($player['parents'] as $parent): ?>
                                            <div class="mb-1">
                                                <strong><?php echo htmlspecialchars($parent['full_name']); ?></strong>
                                                <small class="text-muted">(Guardian <?php echo $parent['guardian_number']; ?>)</small>
                                                <?php if ($parent['phone']): ?>
                                                    <br><a href="tel:<?php echo htmlspecialchars($parent['phone']); ?>" class="text-decoration-none small">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($parent['phone']); ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($parent['email']): ?>
                                                    <br><a href="mailto:<?php echo htmlspecialchars($parent['email']); ?>" class="text-decoration-none small">
                                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($parent['email']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No parent info</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'active' => 'success',
                                        'injured' => 'warning',
                                        'inactive' => 'secondary'
                                    ];
                                    $status = $player['roster_status'] ?? 'active';
                                    ?>
                                    <span class="badge bg-<?php echo $statusColors[$status] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/coach/player?id=<?php echo $player['id']; ?>&team_id=<?php echo $team['id']; ?>"
                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Simple roster search
document.getElementById('rosterSearch')?.addEventListener('input', function(e) {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.player-row').forEach(row => {
        const name = row.getAttribute('data-name');
        row.style.display = name.includes(search) ? '' : 'none';
    });
});
</script>

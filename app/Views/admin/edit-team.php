<?php
// Get data from controller
$team = $team ?? [];
$leagues = $leagues ?? [];
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Edit Team</h2>
            <p class="text-muted"><?php echo htmlspecialchars($team['name'] ?? ''); ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Team Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/teams/update" class="admin-form">
                        <!-- Hidden Fields -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="team_id" value="<?php echo htmlspecialchars($team['id'] ?? ''); ?>">

                        <!-- Team Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Team Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                   value="<?php echo htmlspecialchars($team['name'] ?? ''); ?>"
                                   required>
                        </div>

                        <!-- League -->
                        <div class="mb-3">
                            <label for="league_id" class="form-label">League <span class="text-danger">*</span></label>
                            <select class="form-select" id="league_id" name="league_id" required>
                                <option value="">-- Select League --</option>
                                <?php foreach ($leagues as $league): ?>
                                    <option value="<?php echo htmlspecialchars($league['id']); ?>"
                                            <?php echo ($team['league_id'] ?? '') == $league['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($league['name'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Age Group -->
                        <div class="mb-3">
                            <label for="age_group" class="form-label">Age Group <span class="text-danger">*</span></label>
                            <select class="form-select" id="age_group" name="age_group" required>
                                <option value="">-- Select Age Group --</option>
                                <option value="8U" <?php echo ($team['age_group'] ?? '') === '8U' ? 'selected' : ''; ?>>8U (Under 8)</option>
                                <option value="10U" <?php echo ($team['age_group'] ?? '') === '10U' ? 'selected' : ''; ?>>10U (Under 10)</option>
                                <option value="12U" <?php echo ($team['age_group'] ?? '') === '12U' ? 'selected' : ''; ?>>12U (Under 12)</option>
                                <option value="14U" <?php echo ($team['age_group'] ?? '') === '14U' ? 'selected' : ''; ?>>14U (Under 14)</option>
                                <option value="16U" <?php echo ($team['age_group'] ?? '') === '16U' ? 'selected' : ''; ?>>16U (Under 16)</option>
                                <option value="18U" <?php echo ($team['age_group'] ?? '') === '18U' ? 'selected' : ''; ?>>18U (Under 18)</option>
                            </select>
                        </div>

                        <!-- Max Players -->
                        <div class="mb-3">
                            <label for="max_players" class="form-label">Maximum Players <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_players" name="max_players"
                                   value="<?php echo htmlspecialchars($team['max_players'] ?? 15); ?>"
                                   min="1"
                                   max="50"
                                   required>
                            <small class="text-muted">Current roster: <?php echo count($team['roster'] ?? []) ?? 0; ?> players</small>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo ($team['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($team['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            <small class="text-muted">Inactive teams are hidden from player signup</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="/admin/teams/view?id=<?php echo $team['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Roster Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Roster Status</h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3" style="height: 25px;">
                        <?php
                        $rosterCount = count($team['roster'] ?? []) ?? 0;
                        $maxPlayers = $team['max_players'] ?? 15;
                        $percentage = ($rosterCount / $maxPlayers) * 100;
                        $progressClass = $percentage >= 100 ? 'bg-danger' : ($percentage >= 75 ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="progress-bar <?php echo $progressClass; ?>" role="progressbar"
                             style="width: <?php echo min($percentage, 100); ?>%"
                             aria-valuenow="<?php echo $rosterCount; ?>"
                             aria-valuemin="0"
                             aria-valuemax="<?php echo $maxPlayers; ?>">
                            <?php echo $rosterCount; ?>/<?php echo $maxPlayers; ?>
                        </div>
                    </div>
                    <p class="text-muted small mb-0">
                        <?php
                        $spots = $maxPlayers - $rosterCount;
                        if ($spots > 0) {
                            echo $spots . ' spot' . ($spots > 1 ? 's' : '') . ' available';
                        } else {
                            echo 'Roster is full';
                        }
                        ?>
                    </p>
                </div>
            </div>

            <!-- Team Summary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Current Information</h6>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">League:</dt>
                        <dd class="col-sm-6"><?php echo htmlspecialchars($team['league_name'] ?? '-'); ?></dd>

                        <dt class="col-sm-6">Age Group:</dt>
                        <dd class="col-sm-6"><?php echo htmlspecialchars($team['age_group'] ?? '-'); ?></dd>

                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-<?php echo ($team['status'] === 'active') ? 'success' : 'danger'; ?>">
                                <?php echo htmlspecialchars(ucfirst($team['status'] ?? '')); ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Coaches -->
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

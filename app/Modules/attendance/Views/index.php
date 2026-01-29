<?php
$teams = $teams ?? [];
$recentEvents = $recentEvents ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-clipboard-check me-2"></i>Attendance Tracking</h2>
            <p class="text-muted mb-0">Track practice and game attendance</p>
        </div>
    </div>

    <?php if (empty($teams)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            You are not assigned to any teams.
        </div>
    <?php else: ?>
        <div class="row">
            <!-- Quick Take Attendance -->
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Take Attendance</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="/coach/attendance/take">
                            <div class="mb-3">
                                <label for="team_id" class="form-label">Team</label>
                                <select class="form-select" id="team_id" name="team_id" required>
                                    <?php foreach ($teams as $team): ?>
                                        <option value="<?php echo $team['id']; ?>">
                                            <?php echo htmlspecialchars($team['name']); ?>
                                            (<?php echo htmlspecialchars($team['age_group']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date"
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label">Event Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="practice">Practice</option>
                                        <option value="game">Game</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-clipboard-list me-1"></i> Take Attendance
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            <?php foreach ($teams as $team): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($team['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($team['age_group']); ?></small>
                                    </div>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/coach/attendance/history?team_id=<?php echo $team['id']; ?>"
                                           class="btn btn-outline-primary">
                                            <i class="fas fa-history"></i>
                                        </a>
                                        <a href="/coach/attendance/report?team_id=<?php echo $team['id']; ?>"
                                           class="btn btn-outline-info">
                                            <i class="fas fa-chart-bar"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Events -->
        <?php if (!empty($recentEvents)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Attendance Records</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Team</th>
                                <th>Type</th>
                                <th>Players</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEvents as $event): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($event['event_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($event['team_name'] ?? 'Unknown'); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $event['event_type'] === 'game' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $event['player_count']; ?> recorded</td>
                                    <td>
                                        <a href="/coach/attendance/take?team_id=<?php echo $event['team_id']; ?>&date=<?php echo $event['event_date']; ?>"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
$team = $team ?? [];
$teams = $teams ?? [];
$history = $history ?? [];
$month = $month ?? date('Y-m');
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-history me-2"></i>Attendance History</h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($team['name'] ?? 'Unknown Team'); ?></p>
        </div>
        <a href="/coach/attendance" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/coach/attendance/history" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Team</label>
                    <select class="form-select" name="team_id" onchange="this.form.submit()">
                        <?php foreach ($teams as $t): ?>
                            <option value="<?php echo $t['id']; ?>" <?php echo ($team['id'] ?? 0) == $t['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Month</label>
                    <input type="month" class="form-control" name="month"
                           value="<?php echo htmlspecialchars($month); ?>"
                           onchange="this.form.submit()">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <a href="/coach/attendance/report?team_id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-info">
                        <i class="fas fa-chart-bar me-1"></i> View Report
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($history)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No attendance records found for this month.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Player</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $currentDate = '';
                        foreach ($history as $record):
                            $isNewDate = $record['event_date'] !== $currentDate;
                            $currentDate = $record['event_date'];
                        ?>
                            <tr <?php echo $isNewDate ? 'class="table-light"' : ''; ?>>
                                <td>
                                    <?php if ($isNewDate): ?>
                                        <strong><?php echo date('M j, Y', strtotime($record['event_date'])); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $record['event_type'] === 'game' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($record['event_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'present' => 'success',
                                        'absent' => 'danger',
                                        'excused' => 'warning',
                                        'late' => 'info'
                                    ];
                                    ?>
                                    <span class="badge bg-<?php echo $statusColors[$record['status']] ?? 'secondary'; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?php echo htmlspecialchars($record['notes'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

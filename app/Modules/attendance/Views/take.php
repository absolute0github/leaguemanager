<?php
$team = $team ?? [];
$roster = $roster ?? [];
$eventDate = $eventDate ?? date('Y-m-d');
$eventType = $eventType ?? 'practice';
$existingAttendance = $existingAttendance ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-clipboard-check me-2"></i>Take Attendance</h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars($team['name'] ?? 'Unknown Team'); ?>
                - <?php echo date('F j, Y', strtotime($eventDate)); ?>
            </p>
        </div>
        <a href="/coach/attendance" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <?php if (empty($roster)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            No players on this team's roster.
        </div>
    <?php else: ?>
        <form method="POST" action="/coach/attendance/save">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="team_id" value="<?php echo $team['id']; ?>">
            <input type="hidden" name="event_date" value="<?php echo htmlspecialchars($eventDate); ?>">
            <input type="hidden" name="event_type" value="<?php echo htmlspecialchars($eventType); ?>">

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-<?php echo $eventType === 'game' ? 'danger' : 'primary'; ?> me-2">
                            <?php echo ucfirst($eventType); ?>
                        </span>
                        <strong><?php echo date('l, F j, Y', strtotime($eventDate)); ?></strong>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                            All Present
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                            All Absent
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Player</th>
                                    <th style="width: 200px;">Status</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roster as $player): ?>
                                    <?php
                                    $existing = $existingAttendance[$player['id']] ?? null;
                                    $currentStatus = $existing['status'] ?? 'present';
                                    $currentNotes = $existing['notes'] ?? '';
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $player['jersey_number'] ?? '-'; ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></strong>
                                            <?php if ($player['primary_position']): ?>
                                                <br><small class="text-muted"><?php echo htmlspecialchars($player['primary_position']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                <input type="radio" class="btn-check"
                                                       name="attendance[<?php echo $player['id']; ?>][status]"
                                                       id="present_<?php echo $player['id']; ?>"
                                                       value="present"
                                                       <?php echo $currentStatus === 'present' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-success" for="present_<?php echo $player['id']; ?>">
                                                    <i class="fas fa-check"></i>
                                                </label>

                                                <input type="radio" class="btn-check"
                                                       name="attendance[<?php echo $player['id']; ?>][status]"
                                                       id="absent_<?php echo $player['id']; ?>"
                                                       value="absent"
                                                       <?php echo $currentStatus === 'absent' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-danger" for="absent_<?php echo $player['id']; ?>">
                                                    <i class="fas fa-times"></i>
                                                </label>

                                                <input type="radio" class="btn-check"
                                                       name="attendance[<?php echo $player['id']; ?>][status]"
                                                       id="excused_<?php echo $player['id']; ?>"
                                                       value="excused"
                                                       <?php echo $currentStatus === 'excused' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-warning" for="excused_<?php echo $player['id']; ?>">
                                                    <i class="fas fa-clock"></i>
                                                </label>

                                                <input type="radio" class="btn-check"
                                                       name="attendance[<?php echo $player['id']; ?>][status]"
                                                       id="late_<?php echo $player['id']; ?>"
                                                       value="late"
                                                       <?php echo $currentStatus === 'late' ? 'checked' : ''; ?>>
                                                <label class="btn btn-outline-info" for="late_<?php echo $player['id']; ?>">
                                                    <i class="fas fa-hourglass-half"></i>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm"
                                                   name="attendance[<?php echo $player['id']; ?>][notes]"
                                                   value="<?php echo htmlspecialchars($currentNotes); ?>"
                                                   placeholder="Optional notes...">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <span class="badge bg-success me-1"><i class="fas fa-check"></i></span> Present
                            <span class="badge bg-danger mx-1"><i class="fas fa-times"></i></span> Absent
                            <span class="badge bg-warning mx-1"><i class="fas fa-clock"></i></span> Excused
                            <span class="badge bg-info mx-1"><i class="fas fa-hourglass-half"></i></span> Late
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Attendance
                        </button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
function markAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(radio => {
        radio.checked = true;
    });
}
</script>

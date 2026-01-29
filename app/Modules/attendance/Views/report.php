<?php
$team = $team ?? [];
$report = $report ?? [];
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-chart-bar me-2"></i>Attendance Report</h2>
            <p class="text-muted mb-0"><?php echo htmlspecialchars($team['name'] ?? 'Unknown Team'); ?></p>
        </div>
        <div>
            <a href="/coach/attendance/history?team_id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-history me-1"></i> History
            </a>
            <a href="/coach/attendance" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <?php if (empty($report)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No attendance data available for this team.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Player Attendance Summary</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Player</th>
                            <th class="text-center">Total Events</th>
                            <th class="text-center text-success">Present</th>
                            <th class="text-center text-danger">Absent</th>
                            <th class="text-center text-warning">Excused</th>
                            <th class="text-center text-info">Late</th>
                            <th class="text-center">Attendance Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report as $player): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?></strong>
                                </td>
                                <td class="text-center"><?php echo $player['total_events'] ?? 0; ?></td>
                                <td class="text-center text-success"><?php echo $player['present'] ?? 0; ?></td>
                                <td class="text-center text-danger"><?php echo $player['absent'] ?? 0; ?></td>
                                <td class="text-center text-warning"><?php echo $player['excused'] ?? 0; ?></td>
                                <td class="text-center text-info"><?php echo $player['late'] ?? 0; ?></td>
                                <td class="text-center">
                                    <?php
                                    $rate = $player['attendance_rate'] ?? 0;
                                    $barColor = $rate >= 90 ? 'success' : ($rate >= 70 ? 'warning' : 'danger');
                                    ?>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1" style="height: 20px;">
                                            <div class="progress-bar bg-<?php echo $barColor; ?>"
                                                 style="width: <?php echo $rate; ?>%">
                                                <?php echo $rate; ?>%
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?php echo array_sum(array_column($report, 'present')); ?></h3>
                        <p class="mb-0">Total Present</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <h3><?php echo array_sum(array_column($report, 'absent')); ?></h3>
                        <p class="mb-0">Total Absent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <?php
                        $totalPresent = array_sum(array_column($report, 'present'));
                        $totalEvents = array_sum(array_column($report, 'total_events'));
                        $overallRate = $totalEvents > 0 ? round(($totalPresent / $totalEvents) * 100, 1) : 0;
                        ?>
                        <h3><?php echo $overallRate; ?>%</h3>
                        <p class="mb-0">Overall Rate</p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3>Registrations: <?= htmlspecialchars($tryout['age_group']) ?></h3>
        <p class="text-muted mb-0">
            <?= date('F j, Y', strtotime($tryout['tryout_date'])) ?> at <?= date('g:i A', strtotime($tryout['start_time'])) ?><br>
            <?= htmlspecialchars($tryout['location_name']) ?>, <?= htmlspecialchars($tryout['city']) ?>, <?= htmlspecialchars($tryout['state']) ?>
        </p>
    </div>
    <div>
        <a href="/admin/tryouts/edit?id=<?= $tryout['id'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil"></i> Edit Tryout
        </a>
        <a href="/admin/tryouts" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Tryouts
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Confirmed</h6>
                <h2 class="mb-0"><?= $tryout['current_participants'] ?></h2>
                <?php if ($tryout['max_participants']): ?>
                    <small class="text-muted">of <?= $tryout['max_participants'] ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Waitlist</h6>
                <h2 class="mb-0">
                    <?php
                    $waitlistCount = count(array_filter($registrations, fn($r) => $r['waitlist_position'] !== null));
                    echo $waitlistCount;
                    ?>
                </h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Spots Available</h6>
                <h2 class="mb-0">
                    <?php
                    if ($tryout['max_participants']) {
                        $available = $tryout['max_participants'] - $tryout['current_participants'];
                        echo max(0, $available);
                    } else {
                        echo '∞';
                    }
                    ?>
                </h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Revenue</h6>
                <h2 class="mb-0">$<?= number_format($tryout['cost'] * $tryout['current_participants'], 2) ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'confirmed' ? 'active' : '' ?>"
           href="?tryout_id=<?= $tryout['id'] ?>&tab=confirmed">
            Confirmed (<?= $tryout['current_participants'] ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'waitlist' ? 'active' : '' ?>"
           href="?tryout_id=<?= $tryout['id'] ?>&tab=waitlist">
            Waitlist (<?= $waitlistCount ?? 0 ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'cancelled' ? 'active' : '' ?>"
           href="?tryout_id=<?= $tryout['id'] ?>&tab=cancelled">
            Cancelled
        </a>
    </li>
</ul>

<!-- Registrations Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <?php if ($tab === 'waitlist'): ?>
                        <th style="width: 60px;">Position</th>
                    <?php endif; ?>
                    <th>Player</th>
                    <th>Age</th>
                    <th>Email</th>
                    <th>Registration Date</th>
                    <th>Payment</th>
                    <?php if ($tab === 'confirmed'): ?>
                        <th>Attendance</th>
                    <?php endif; ?>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registrations)): ?>
                    <tr>
                        <td colspan="<?= $tab === 'waitlist' ? 7 : ($tab === 'confirmed' ? 7 : 6) ?>" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No registrations found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($registrations as $registration): ?>
                        <?php
                        $age = $registration['date_of_birth']
                            ? floor((time() - strtotime($registration['date_of_birth'])) / (365.25 * 24 * 60 * 60))
                            : 'N/A';
                        ?>
                        <tr>
                            <?php if ($tab === 'waitlist'): ?>
                                <td>
                                    <strong class="badge bg-warning">#<?= $registration['waitlist_position'] ?></strong>
                                </td>
                            <?php endif; ?>
                            <td>
                                <strong><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></strong>
                            </td>
                            <td><?= $age ?></td>
                            <td><?= htmlspecialchars($registration['email'] ?? 'N/A') ?></td>
                            <td><?= date('M j, Y g:i A', strtotime($registration['registration_date'])) ?></td>
                            <td>
                                <?php
                                $paymentColors = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'waived' => 'secondary',
                                    'refunded' => 'info'
                                ];
                                $color = $paymentColors[$registration['payment_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= ucfirst($registration['payment_status']) ?></span>
                            </td>
                            <?php if ($tab === 'confirmed'): ?>
                                <td>
                                    <?php
                                    $attendanceColors = [
                                        'registered' => 'info',
                                        'attended' => 'success',
                                        'no_show' => 'danger',
                                        'cancelled' => 'secondary'
                                    ];
                                    $color = $attendanceColors[$registration['attendance_status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= ucfirst(str_replace('_', ' ', $registration['attendance_status'])) ?>
                                    </span>
                                </td>
                            <?php endif; ?>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/tryout-registrations/view?id=<?= $registration['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($tab === 'waitlist'): ?>
                                        <button type="button" class="btn btn-outline-success"
                                                onclick="promoteFromWaitlist(<?= $registration['id'] ?>)">
                                            <i class="bi bi-arrow-up-circle"></i> Promote
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($tab !== 'cancelled'): ?>
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="cancelRegistration(<?= $registration['id'] ?>, '<?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name'], ENT_QUOTES) ?>')">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Hidden forms -->
<form id="promoteForm" method="POST" action="/admin/tryout-registrations/promote-waitlist" style="display: none;">
    <input type="hidden" name="id" id="promoteId">
</form>

<form id="cancelForm" method="POST" action="/admin/tryout-registrations/cancel" style="display: none;">
    <input type="hidden" name="id" id="cancelId">
    <input type="hidden" name="reason" id="cancelReason">
</form>

<script>
function promoteFromWaitlist(id) {
    if (confirm('Are you sure you want to promote this registration from the waitlist?\n\nThis will send a confirmation email to the player.')) {
        document.getElementById('promoteId').value = id;
        document.getElementById('promoteForm').submit();
    }
}

function cancelRegistration(id, playerName) {
    const reason = prompt('Cancel registration for ' + playerName + '?\n\nEnter cancellation reason (will be included in email):');
    if (reason !== null && reason.trim() !== '') {
        document.getElementById('cancelId').value = id;
        document.getElementById('cancelReason').value = reason.trim();
        document.getElementById('cancelForm').submit();
    }
}
</script>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>My Tryout Registrations</h3>
    <a href="/tryouts" class="btn btn-primary">
        <i class="bi bi-search"></i> Browse Tryouts
    </a>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'upcoming' ? 'active' : '' ?>" href="?tab=upcoming">
            <i class="bi bi-calendar-event"></i> Upcoming
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'waitlist' ? 'active' : '' ?>" href="?tab=waitlist">
            <i class="bi bi-clock-history"></i> Waitlist
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'past' ? 'active' : '' ?>" href="?tab=past">
            <i class="bi bi-calendar-check"></i> Past
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'cancelled' ? 'active' : '' ?>" href="?tab=cancelled">
            <i class="bi bi-x-circle"></i> Cancelled
        </a>
    </li>
</ul>

<!-- Registrations List -->
<?php if (empty($registrations)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
            <h5 class="text-muted">No Registrations Found</h5>
            <p class="text-muted">
                <?php if ($tab === 'upcoming'): ?>
                    You don't have any upcoming tryout registrations.
                <?php elseif ($tab === 'waitlist'): ?>
                    You don't have any registrations on the waitlist.
                <?php elseif ($tab === 'past'): ?>
                    You don't have any past tryout registrations.
                <?php else: ?>
                    You don't have any cancelled registrations.
                <?php endif; ?>
            </p>
            <a href="/tryouts" class="btn btn-primary">Browse Available Tryouts</a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 g-4">
        <?php foreach ($registrations as $registration): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-0"><?= htmlspecialchars($registration['age_group']) ?> Tryout</h5>
                                <small class="text-muted">
                                    <?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?>
                                </small>
                            </div>
                            <?php if ($registration['waitlist_position'] !== null): ?>
                                <span class="badge bg-warning">Waitlist #<?= $registration['waitlist_position'] ?></span>
                            <?php elseif ($tab === 'upcoming'): ?>
                                <span class="badge bg-success">Confirmed</span>
                            <?php elseif ($tab === 'past'): ?>
                                <?php
                                $attendanceColors = [
                                    'attended' => 'success',
                                    'no_show' => 'danger',
                                    'registered' => 'secondary'
                                ];
                                $color = $attendanceColors[$registration['attendance_status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $color ?>">
                                    <?= ucfirst(str_replace('_', ' ', $registration['attendance_status'])) ?>
                                </span>
                            <?php elseif ($tab === 'cancelled'): ?>
                                <span class="badge bg-secondary">Cancelled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <strong><?= date('l, F j, Y', strtotime($registration['tryout_date'])) ?></strong>
                        </div>

                        <div class="mb-2">
                            <i class="bi bi-clock text-primary"></i>
                            <?= date('g:i A', strtotime($registration['start_time'])) ?> -
                            <?= date('g:i A', strtotime($registration['end_time'])) ?>
                        </div>

                        <div class="mb-2">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <strong><?= htmlspecialchars($registration['location_name']) ?></strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($registration['city']) ?>, <?= htmlspecialchars($registration['state']) ?>
                            </small>
                        </div>

                        <div class="mb-2">
                            <i class="bi bi-cash text-primary"></i>
                            <strong>$<?= number_format($registration['cost'], 2) ?></strong>
                            -
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
                        </div>

                        <div class="mb-2">
                            <i class="bi bi-check-circle text-primary"></i>
                            <small class="text-muted">
                                Registered: <?= date('M j, Y', strtotime($registration['registration_date'])) ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-grid gap-2">
                            <a href="/tryouts/view?id=<?= $registration['tryout_id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-info-circle"></i> View Tryout Details
                            </a>
                            <?php if ($tab === 'upcoming' || $tab === 'waitlist'): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="cancelRegistration(<?= $registration['id'] ?>, '<?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($registration['age_group'], ENT_QUOTES) ?>')">
                                    <i class="bi bi-x-circle"></i> Cancel Registration
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="text-muted mt-4 text-center">
        Showing <?= count($registrations) ?> registration<?= count($registrations) != 1 ? 's' : '' ?>
    </p>
<?php endif; ?>

<!-- Hidden form for cancellation -->
<form id="cancelForm" method="POST" action="/tryouts/cancel-registration" style="display: none;">
    <input type="hidden" name="id" id="cancelId">
</form>

<script>
function cancelRegistration(id, playerName, ageGroup) {
    if (confirm('Are you sure you want to cancel the registration for ' + playerName + ' (' + ageGroup + ')?\n\nYou will receive a confirmation email.')) {
        document.getElementById('cancelId').value = id;
        document.getElementById('cancelForm').submit();
    }
}
</script>

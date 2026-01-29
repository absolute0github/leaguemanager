<div class="mb-4">
    <a href="/admin/tryout-registrations?tryout_id=<?= $registration['tryout_id'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Registrations
    </a>
</div>

<div class="row">
    <!-- Left Column: Registration Details -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?>
                </h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Email:</dt>
                            <dd class="col-sm-7"><?= htmlspecialchars($registration['email'] ?? 'N/A') ?></dd>

                            <dt class="col-sm-5">Date of Birth:</dt>
                            <dd class="col-sm-7">
                                <?php if ($registration['date_of_birth']): ?>
                                    <?= date('M j, Y', strtotime($registration['date_of_birth'])) ?>
                                    (Age: <?= floor((time() - strtotime($registration['date_of_birth'])) / (365.25 * 24 * 60 * 60)) ?>)
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-5">Registration Date:</dt>
                            <dd class="col-sm-7"><?= date('M j, Y g:i A', strtotime($registration['registration_date'])) ?></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row">
                            <dt class="col-sm-5">Waiver Signed:</dt>
                            <dd class="col-sm-7">
                                <?php if ($registration['waiver_signed']): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> Yes</span>
                                <?php else: ?>
                                    <span class="badge bg-warning"><i class="bi bi-x-circle"></i> No</span>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-5">Payment Method:</dt>
                            <dd class="col-sm-7"><?= htmlspecialchars($registration['payment_method'] ?? 'N/A') ?></dd>

                            <dt class="col-sm-5">Transaction ID:</dt>
                            <dd class="col-sm-7"><?= htmlspecialchars($registration['payment_transaction_id'] ?? 'N/A') ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Tryout Details</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Age Group:</dt>
                    <dd class="col-sm-9"><strong><?= htmlspecialchars($registration['age_group']) ?></strong></dd>

                    <dt class="col-sm-3">Date & Time:</dt>
                    <dd class="col-sm-9">
                        <?= date('F j, Y', strtotime($registration['tryout_date'])) ?><br>
                        <?= date('g:i A', strtotime($registration['start_time'])) ?> - <?= date('g:i A', strtotime($registration['end_time'])) ?>
                    </dd>

                    <dt class="col-sm-3">Location:</dt>
                    <dd class="col-sm-9">
                        <strong><?= htmlspecialchars($registration['location_name']) ?></strong><br>
                        <?= htmlspecialchars($registration['street_address']) ?><br>
                        <?= htmlspecialchars($registration['city']) ?>, <?= htmlspecialchars($registration['state']) ?> <?= htmlspecialchars($registration['zip_code']) ?>
                    </dd>

                    <dt class="col-sm-3">Cost:</dt>
                    <dd class="col-sm-9">$<?= number_format($registration['cost'], 2) ?></dd>
                </dl>
            </div>
        </div>

        <?php if (!empty($registration['admin_notes'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Admin Notes</h5>
                </div>
                <div class="card-body">
                    <pre class="mb-0" style="white-space: pre-wrap;"><?= htmlspecialchars($registration['admin_notes']) ?></pre>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Right Column: Actions -->
    <div class="col-md-4">
        <!-- Status Card -->
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Status</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-5">Payment:</dt>
                    <dd class="col-sm-7">
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
                    </dd>

                    <dt class="col-sm-5">Attendance:</dt>
                    <dd class="col-sm-7">
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
                    </dd>

                    <?php if ($registration['waitlist_position'] !== null): ?>
                        <dt class="col-sm-5">Waitlist:</dt>
                        <dd class="col-sm-7">
                            <span class="badge bg-warning">Position #<?= $registration['waitlist_position'] ?></span>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Update Payment Status -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Update Payment</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryout-registrations/update-payment">
                    <input type="hidden" name="id" value="<?= $registration['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm">
                            <option value="pending" <?= $registration['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $registration['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="waived" <?= $registration['payment_status'] === 'waived' ? 'selected' : '' ?>>Waived</option>
                            <option value="refunded" <?= $registration['payment_status'] === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" name="payment_transaction_id" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($registration['payment_transaction_id'] ?? '') ?>"
                               placeholder="Optional">
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-save"></i> Update Payment
                    </button>
                </form>
            </div>
        </div>

        <!-- Update Attendance Status -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Update Attendance</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryout-registrations/update-attendance">
                    <input type="hidden" name="id" value="<?= $registration['id'] ?>">

                    <div class="mb-2">
                        <label class="form-label">Attendance Status</label>
                        <select name="attendance_status" class="form-select form-select-sm">
                            <option value="registered" <?= $registration['attendance_status'] === 'registered' ? 'selected' : '' ?>>Registered</option>
                            <option value="attended" <?= $registration['attendance_status'] === 'attended' ? 'selected' : '' ?>>Attended</option>
                            <option value="no_show" <?= $registration['attendance_status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                            <option value="cancelled" <?= $registration['attendance_status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-save"></i> Update Attendance
                    </button>
                </form>
            </div>
        </div>

        <!-- Add Note -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">Add Note</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryout-registrations/add-note">
                    <input type="hidden" name="id" value="<?= $registration['id'] ?>">

                    <div class="mb-2">
                        <textarea name="note" class="form-control form-control-sm" rows="3"
                                  placeholder="Enter note..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Add Note
                    </button>
                </form>
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">Danger Zone</h6>
            </div>
            <div class="card-body">
                <?php if ($registration['waitlist_position'] !== null): ?>
                    <button type="button" class="btn btn-sm btn-success w-100 mb-2"
                            onclick="promoteFromWaitlist(<?= $registration['id'] ?>)">
                        <i class="bi bi-arrow-up-circle"></i> Promote from Waitlist
                    </button>
                <?php endif; ?>

                <?php if ($registration['attendance_status'] !== 'cancelled'): ?>
                    <button type="button" class="btn btn-sm btn-danger w-100"
                            onclick="cancelRegistration(<?= $registration['id'] ?>, '<?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name'], ENT_QUOTES) ?>')">
                        <i class="bi bi-x-circle"></i> Cancel Registration
                    </button>
                <?php endif; ?>
            </div>
        </div>
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

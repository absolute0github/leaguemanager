<div class="mb-4">
    <a href="/tryouts/view?id=<?= $tryout['id'] ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Tryout Details
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Register for <?= htmlspecialchars($tryout['age_group']) ?> Tryout</h4>
            </div>
            <div class="card-body">
                <!-- Tryout Summary -->
                <div class="alert alert-info mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Date:</strong> <?= date('F j, Y', strtotime($tryout['tryout_date'])) ?><br>
                            <strong>Time:</strong> <?= date('g:i A', strtotime($tryout['start_time'])) ?> - <?= date('g:i A', strtotime($tryout['end_time'])) ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Location:</strong> <?= htmlspecialchars($tryout['location_name']) ?><br>
                            <strong>Cost:</strong>
                            <?php if ($tryout['cost'] > 0): ?>
                                $<?= number_format($tryout['cost'], 2) ?>
                            <?php else: ?>
                                Free
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <form method="POST" action="/tryouts/register">
                    <input type="hidden" name="tryout_id" value="<?= $tryout['id'] ?>">

                    <!-- Player Selection -->
                    <div class="mb-4">
                        <label for="player_id" class="form-label">Select Player <span class="text-danger">*</span></label>
                        <select class="form-select" id="player_id" name="player_id" required>
                            <option value="">Choose a player...</option>
                            <?php foreach ($players as $player): ?>
                                <option value="<?= $player['id'] ?>">
                                    <?= htmlspecialchars($player['first_name'] . ' ' . $player['last_name']) ?>
                                    <?php if ($player['date_of_birth']): ?>
                                        (Age: <?= floor((time() - strtotime($player['date_of_birth'])) / (365.25 * 24 * 60 * 60)) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Payment Information (if cost > 0) -->
                    <?php if ($tryout['cost'] > 0): ?>
                        <div class="mb-4">
                            <h5>Payment Information</h5>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle"></i>
                                Registration fee: <strong>$<?= number_format($tryout['cost'], 2) ?></strong>
                            </div>

                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select payment method...</option>
                                    <option value="credit_card">Credit Card</option>
                                    <option value="debit_card">Debit Card</option>
                                    <option value="cash">Cash (Pay at tryout)</option>
                                    <option value="check">Check</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="payment_transaction_id" class="form-label">Transaction/Confirmation ID</label>
                                <input type="text" class="form-control" id="payment_transaction_id" name="payment_transaction_id"
                                       placeholder="Optional - if paying online">
                                <small class="text-muted">If paying by cash/check at tryout, leave blank</small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Waiver -->
                    <div class="mb-4">
                        <h5>Waiver & Agreement</h5>
                        <div class="card bg-light">
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                <h6>Tryout Participation Waiver</h6>
                                <p class="small">
                                    I hereby acknowledge that participation in baseball/softball tryouts involves physical activity and inherent risks.
                                    I voluntarily agree to allow the participant to take part in this tryout event.
                                </p>
                                <p class="small">
                                    <strong>Assumption of Risk:</strong> I understand that tryouts involve running, throwing, catching, and other physical activities
                                    that could result in injury. I acknowledge these risks and agree that the participant will participate at their own risk.
                                </p>
                                <p class="small">
                                    <strong>Release of Liability:</strong> I release, waive, discharge, and covenant not to sue the league, its officers,
                                    coaches, volunteers, and facility owners from any and all liability for injury, death, or property damage arising from
                                    participation in this tryout.
                                </p>
                                <p class="small">
                                    <strong>Medical Authorization:</strong> I authorize league officials to seek emergency medical treatment for the participant
                                    if necessary, and I agree to be responsible for any medical costs incurred.
                                </p>
                                <p class="small mb-0">
                                    <strong>Photo/Video Release:</strong> I grant permission for photos/videos taken during the tryout to be used for
                                    league promotional purposes.
                                </p>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" id="waiver_signed" name="waiver_signed" value="1" required>
                            <label class="form-check-label" for="waiver_signed">
                                <strong>I have read and accept the waiver agreement <span class="text-danger">*</span></strong>
                            </label>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> Complete Registration
                        </button>
                        <a href="/tryouts/view?id=<?= $tryout['id'] ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-light mb-3">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-info-circle"></i> Registration Info</h6>
                <ul class="small mb-0">
                    <li>You must select one of your registered players</li>
                    <?php if ($tryout['cost'] > 0): ?>
                        <li>Payment is required to complete registration</li>
                        <li>You may pay online or at the tryout</li>
                    <?php else: ?>
                        <li>This is a free tryout - no payment required</li>
                    <?php endif; ?>
                    <li>You must accept the waiver to register</li>
                    <li>Confirmation email will be sent after registration</li>
                    <li>You can cancel your registration from "My Registrations"</li>
                </ul>
            </div>
        </div>

        <?php
        $spotsLeft = $tryout['max_participants']
            ? max(0, $tryout['max_participants'] - $tryout['current_participants'])
            : null;
        $isFull = $tryout['max_participants'] && $tryout['current_participants'] >= $tryout['max_participants'];
        ?>

        <?php if ($isFull): ?>
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle"></i> Waitlist Registration</h6>
                <p class="small mb-0">
                    This tryout is currently full. By registering, you will be added to the waitlist.
                    You will be notified if a spot becomes available.
                </p>
            </div>
        <?php elseif ($spotsLeft !== null && $spotsLeft <= 5): ?>
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-circle"></i> Limited Spots</h6>
                <p class="small mb-0">
                    Only <strong><?= $spotsLeft ?></strong> spot<?= $spotsLeft != 1 ? 's' : '' ?> remaining! Register soon.
                </p>
            </div>
        <?php endif; ?>

        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-question-circle"></i> Questions?</h6>
                <p class="small mb-0">
                    If you have questions about registration or the tryout, please contact the league office.
                </p>
            </div>
        </div>
    </div>
</div>

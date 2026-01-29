<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Success Message -->
        <?php if ($registration['waitlist_position'] !== null): ?>
            <div class="alert alert-warning text-center py-4">
                <i class="bi bi-clock-history" style="font-size: 3rem;"></i>
                <h3 class="mt-3">Added to Waitlist</h3>
                <p class="lead">You have been added to the waitlist for this tryout.</p>
                <p class="mb-0">
                    <strong>Waitlist Position: #<?= $registration['waitlist_position'] ?></strong>
                </p>
            </div>
        <?php else: ?>
            <div class="alert alert-success text-center py-4">
                <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                <h3 class="mt-3">Registration Confirmed!</h3>
                <p class="lead">Your tryout registration has been successfully submitted.</p>
            </div>
        <?php endif; ?>

        <!-- Registration Details -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Registration Details</h4>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Player:</dt>
                    <dd class="col-sm-8">
                        <strong><?= htmlspecialchars($registration['first_name'] . ' ' . $registration['last_name']) ?></strong>
                    </dd>

                    <dt class="col-sm-4">Age Group:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($registration['age_group']) ?></dd>

                    <dt class="col-sm-4">Date & Time:</dt>
                    <dd class="col-sm-8">
                        <?= date('l, F j, Y', strtotime($registration['tryout_date'])) ?><br>
                        <?= date('g:i A', strtotime($registration['start_time'])) ?> -
                        <?= date('g:i A', strtotime($registration['end_time'])) ?>
                    </dd>

                    <dt class="col-sm-4">Location:</dt>
                    <dd class="col-sm-8">
                        <strong><?= htmlspecialchars($registration['location_name']) ?></strong><br>
                        <?= htmlspecialchars($registration['street_address']) ?><br>
                        <?= htmlspecialchars($registration['city']) ?>, <?= htmlspecialchars($registration['state']) ?> <?= htmlspecialchars($registration['zip_code']) ?>
                        <?php if (!empty($registration['map_link'])): ?>
                            <br><a href="<?= htmlspecialchars($registration['map_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-map"></i> Get Directions
                            </a>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-sm-4">Registration Date:</dt>
                    <dd class="col-sm-8"><?= date('F j, Y g:i A', strtotime($registration['registration_date'])) ?></dd>

                    <dt class="col-sm-4">Cost:</dt>
                    <dd class="col-sm-8">
                        <strong>$<?= number_format($registration['cost'], 2) ?></strong>
                    </dd>

                    <dt class="col-sm-4">Payment Status:</dt>
                    <dd class="col-sm-8">
                        <?php
                        $paymentColors = [
                            'pending' => 'warning',
                            'paid' => 'success',
                            'waived' => 'secondary'
                        ];
                        $color = $paymentColors[$registration['payment_status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($registration['payment_status']) ?></span>
                        <?php if ($registration['payment_status'] === 'pending'): ?>
                            <br><small class="text-muted">Payment can be made at the tryout or contact the league office</small>
                        <?php endif; ?>
                    </dd>
                </dl>
            </div>
        </div>

        <!-- Next Steps -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Next Steps</h5>
            </div>
            <div class="card-body">
                <ol class="mb-0">
                    <li class="mb-2">
                        <strong>Check your email</strong> - A confirmation email has been sent with all tryout details
                    </li>
                    <?php if ($registration['payment_status'] === 'pending'): ?>
                        <li class="mb-2">
                            <strong>Complete payment</strong> - You can pay at the tryout or contact the league office
                        </li>
                    <?php endif; ?>
                    <?php if ($registration['waitlist_position'] !== null): ?>
                        <li class="mb-2">
                            <strong>Wait for notification</strong> - You will be contacted if a spot opens up
                        </li>
                    <?php else: ?>
                        <li class="mb-2">
                            <strong>Arrive 15 minutes early</strong> - Allow time for check-in
                        </li>
                        <li class="mb-2">
                            <strong>Bring required items</strong> - Glove, cleats, athletic clothing, water bottle, and ID
                        </li>
                    <?php endif; ?>
                    <li>
                        <strong>View your registration</strong> - Access it anytime from "My Registrations"
                    </li>
                </ol>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="/tryouts/my-registrations" class="btn btn-primary">
                <i class="bi bi-list"></i> View My Registrations
            </a>
            <a href="/tryouts" class="btn btn-outline-primary">
                <i class="bi bi-search"></i> Browse More Tryouts
            </a>
            <a href="/dashboard" class="btn btn-secondary">
                <i class="bi bi-house"></i> Go to Dashboard
            </a>
        </div>

        <!-- Additional Info -->
        <?php if (!empty($registration['special_instructions'])): ?>
            <div class="alert alert-info mt-4">
                <h6><i class="bi bi-info-circle"></i> Special Instructions</h6>
                <p class="mb-0"><?= nl2br(htmlspecialchars($registration['special_instructions'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

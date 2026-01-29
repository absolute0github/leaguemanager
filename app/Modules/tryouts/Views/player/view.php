<div class="mb-4">
    <a href="/tryouts" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Back to Tryouts
    </a>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0"><?= htmlspecialchars($tryout['age_group']) ?> Tryout</h3>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5><i class="bi bi-calendar3"></i> Date & Time</h5>
                        <p class="mb-0">
                            <strong><?= date('l, F j, Y', strtotime($tryout['tryout_date'])) ?></strong><br>
                            <?= date('g:i A', strtotime($tryout['start_time'])) ?> -
                            <?= date('g:i A', strtotime($tryout['end_time'])) ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="bi bi-cash"></i> Cost</h5>
                        <p class="mb-0">
                            <?php if ($tryout['cost'] > 0): ?>
                                <strong class="h4">$<?= number_format($tryout['cost'], 2) ?></strong>
                            <?php else: ?>
                                <strong class="h4 text-success">Free</strong>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <div class="mb-4">
                    <h5><i class="bi bi-geo-alt"></i> Location</h5>
                    <p class="mb-2">
                        <strong><?= htmlspecialchars($tryout['location_name']) ?></strong><br>
                        <?= htmlspecialchars($tryout['street_address']) ?><br>
                        <?= htmlspecialchars($tryout['city']) ?>, <?= htmlspecialchars($tryout['state']) ?> <?= htmlspecialchars($tryout['zip_code']) ?>
                    </p>
                    <?php if (!empty($tryout['map_link'])): ?>
                        <a href="<?= htmlspecialchars($tryout['map_link']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-map"></i> Get Directions
                        </a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tryout['special_instructions'])): ?>
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Special Instructions</h6>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($tryout['special_instructions'])) ?></p>
                    </div>
                <?php endif; ?>

                <div class="mb-4">
                    <h5><i class="bi bi-people"></i> Capacity</h5>
                    <?php
                    $spotsLeft = $tryout['max_participants']
                        ? max(0, $tryout['max_participants'] - $tryout['current_participants'])
                        : null;
                    $isFull = $tryout['max_participants'] && $tryout['current_participants'] >= $tryout['max_participants'];
                    ?>
                    <div class="progress" style="height: 30px;">
                        <?php
                        $percentage = $tryout['max_participants']
                            ? ($tryout['current_participants'] / $tryout['max_participants']) * 100
                            : 50;
                        $color = $percentage >= 100 ? 'danger' : ($percentage >= 75 ? 'warning' : 'success');
                        ?>
                        <div class="progress-bar bg-<?= $color ?>" style="width: <?= min(100, $percentage) ?>%">
                            <?= $tryout['current_participants'] ?><?= $tryout['max_participants'] ? ' / ' . $tryout['max_participants'] : '' ?>
                        </div>
                    </div>
                    <p class="mt-2 mb-0">
                        <?php if ($isFull): ?>
                            <span class="badge bg-warning">Full - Waitlist Available</span>
                        <?php elseif ($spotsLeft !== null): ?>
                            <span class="badge bg-success"><?= $spotsLeft ?> spots remaining</span>
                        <?php else: ?>
                            <span class="badge bg-info">Unlimited capacity</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <?php if (!empty($existingRegistrations)): ?>
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle"></i> Already Registered</h5>
                <p class="mb-2">You have already registered the following player(s):</p>
                <ul class="mb-0">
                    <?php foreach ($existingRegistrations as $reg): ?>
                        <li>
                            <strong><?= htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']) ?></strong>
                            <?php if ($reg['waitlist_position'] !== null): ?>
                                <br><span class="badge bg-warning">Waitlist #<?= $reg['waitlist_position'] ?></span>
                            <?php else: ?>
                                <br><span class="badge bg-success">Confirmed</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card mb-3">
            <div class="card-body text-center">
                <h5 class="card-title">Ready to Register?</h5>
                <p class="card-text text-muted">
                    <?php if ($isFull): ?>
                        This tryout is full, but you can join the waitlist.
                    <?php else: ?>
                        Register now to secure your spot!
                    <?php endif; ?>
                </p>
                <a href="/tryouts/register?id=<?= $tryout['id'] ?>" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-person-plus"></i> Register Now
                </a>
            </div>
        </div>

        <div class="card bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-lightbulb"></i> What to Bring</h6>
                <ul class="small mb-0">
                    <li>Baseball/softball glove</li>
                    <li>Cleats or athletic shoes</li>
                    <li>Athletic clothing</li>
                    <li>Water bottle</li>
                    <li>Valid ID for check-in</li>
                </ul>
            </div>
        </div>
    </div>
</div>

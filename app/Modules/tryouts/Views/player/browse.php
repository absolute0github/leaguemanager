<div class="mb-4">
    <h3>Open Tryouts</h3>
    <p class="text-muted">Browse and register for upcoming tryouts</p>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/tryouts" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Age Group</label>
                <input type="text" name="age_group" class="form-control" value="<?= htmlspecialchars($filters['age_group'] ?? '') ?>" placeholder="e.g., 10U, 12U">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>" <?= (isset($filters['location_id']) && $filters['location_id'] == $location['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($location['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Tryouts Grid -->
<?php if (empty($tryouts)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
            <h5 class="text-muted">No Open Tryouts</h5>
            <p class="text-muted">There are no tryouts available matching your criteria.</p>
            <a href="/tryouts" class="btn btn-primary">Clear Filters</a>
        </div>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($tryouts as $tryout): ?>
            <?php
            $spotsLeft = $tryout['max_participants']
                ? max(0, $tryout['max_participants'] - $tryout['current_participants'])
                : null;
            $isFull = $tryout['max_participants'] && $tryout['current_participants'] >= $tryout['max_participants'];
            ?>
            <div class="col">
                <div class="card h-100 <?= $isFull ? 'border-warning' : '' ?>">
                    <?php if ($isFull): ?>
                        <div class="card-header bg-warning text-dark">
                            <i class="bi bi-exclamation-triangle"></i> Full - Waitlist Available
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($tryout['age_group']) ?> Tryout</h5>

                        <div class="mb-3">
                            <i class="bi bi-calendar3 text-primary"></i>
                            <strong><?= date('l, F j, Y', strtotime($tryout['tryout_date'])) ?></strong>
                        </div>

                        <div class="mb-3">
                            <i class="bi bi-clock text-primary"></i>
                            <?= date('g:i A', strtotime($tryout['start_time'])) ?> -
                            <?= date('g:i A', strtotime($tryout['end_time'])) ?>
                        </div>

                        <div class="mb-3">
                            <i class="bi bi-geo-alt text-primary"></i>
                            <strong><?= htmlspecialchars($tryout['location_name']) ?></strong><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($tryout['location_city']) ?>, <?= htmlspecialchars($tryout['location_state']) ?>
                            </small>
                        </div>

                        <div class="mb-3">
                            <i class="bi bi-cash text-primary"></i>
                            <strong>
                                <?php if ($tryout['cost'] > 0): ?>
                                    $<?= number_format($tryout['cost'], 2) ?>
                                <?php else: ?>
                                    Free
                                <?php endif; ?>
                            </strong>
                        </div>

                        <div class="mb-3">
                            <i class="bi bi-people text-primary"></i>
                            <?php if ($spotsLeft !== null): ?>
                                <span class="badge bg-<?= $spotsLeft > 0 ? 'success' : 'danger' ?>">
                                    <?= $tryout['current_participants'] ?> / <?= $tryout['max_participants'] ?> registered
                                </span>
                                <?php if ($spotsLeft > 0): ?>
                                    <br><small class="text-muted"><?= $spotsLeft ?> spots left</small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-info">
                                    <?= $tryout['current_participants'] ?> registered
                                </span>
                                <br><small class="text-muted">No capacity limit</small>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-grid gap-2">
                            <a href="/tryouts/view?id=<?= $tryout['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-info-circle"></i> View Details
                            </a>
                            <a href="/tryouts/register?id=<?= $tryout['id'] ?>" class="btn btn-primary btn-sm">
                                <i class="bi bi-person-plus"></i> Register Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="text-muted mt-4 text-center">
        Showing <?= count($tryouts) ?> open tryouts
    </p>
<?php endif; ?>

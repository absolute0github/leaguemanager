<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Edit Tryout: <?= htmlspecialchars($tryout['age_group']) ?> - <?= date('M j, Y', strtotime($tryout['tryout_date'])) ?></h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryouts/update">
                    <input type="hidden" name="id" value="<?= $tryout['id'] ?>">

                    <div class="mb-3">
                        <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>" <?= $location['id'] == $tryout['location_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($location['name']) ?> - <?= htmlspecialchars($location['city']) ?>, <?= htmlspecialchars($location['state']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="age_group" class="form-label">Age Group <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="age_group" name="age_group" required
                               value="<?= htmlspecialchars($tryout['age_group']) ?>"
                               placeholder="e.g., 10U, 12U, 14U">
                        <small class="text-muted">Standard format: 8U, 10U, 12U, 14U, 16U, 18U</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tryout_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tryout_date" name="tryout_date" required
                                   value="<?= $tryout['tryout_date'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required
                                   value="<?= $tryout['start_time'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required
                                   value="<?= $tryout['end_time'] ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="cost" name="cost" required
                                       min="0" step="0.01" value="<?= $tryout['cost'] ?>">
                            </div>
                            <small class="text-muted">Set to $0.00 for free tryouts</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_participants" class="form-label">Max Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants"
                                   min="<?= $tryout['current_participants'] ?>"
                                   value="<?= $tryout['max_participants'] ?? '' ?>"
                                   placeholder="Leave blank for unlimited">
                            <small class="text-muted">
                                Current: <?= $tryout['current_participants'] ?> registered
                                <?php if ($tryout['current_participants'] > 0): ?>
                                    <br>(Cannot be less than current registrations)
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="scheduled" <?= $tryout['status'] === 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                            <option value="open" <?= $tryout['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                            <option value="closed" <?= $tryout['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                            <option value="completed" <?= $tryout['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= $tryout['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Tryout
                        </button>
                        <a href="/admin/tryouts" class="btn btn-secondary">
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
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Tryout Info</h5>
                <dl class="row mb-0 small">
                    <dt class="col-sm-5">Created:</dt>
                    <dd class="col-sm-7"><?= date('M j, Y', strtotime($tryout['created_at'])) ?></dd>

                    <dt class="col-sm-5">Registrations:</dt>
                    <dd class="col-sm-7">
                        <strong><?= $tryout['current_participants'] ?></strong>
                        <?php if ($tryout['max_participants']): ?>
                            / <?= $tryout['max_participants'] ?>
                        <?php endif; ?>
                        <br>
                        <a href="/admin/tryout-registrations?tryout_id=<?= $tryout['id'] ?>" class="btn btn-sm btn-outline-primary mt-1">
                            <i class="bi bi-people"></i> View Registrations
                        </a>
                    </dd>

                    <dt class="col-sm-5">Status:</dt>
                    <dd class="col-sm-7">
                        <?php
                        $statusColors = [
                            'scheduled' => 'secondary',
                            'open' => 'success',
                            'closed' => 'warning',
                            'cancelled' => 'danger',
                            'completed' => 'dark'
                        ];
                        $color = $statusColors[$tryout['status']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?= $color ?>"><?= ucfirst($tryout['status']) ?></span>
                    </dd>
                </dl>
            </div>
        </div>

        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-lightbulb"></i> Tips</h5>
                <ul class="small mb-0">
                    <li>Cannot reduce max participants below current registrations</li>
                    <li>Changing status to "Closed" prevents new registrations</li>
                    <li>Use "Completed" status after the tryout has finished</li>
                    <li>Cancelled tryouts should notify registered players</li>
                </ul>
            </div>
        </div>
    </div>
</div>

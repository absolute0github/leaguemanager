<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Create Tryout Event</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/tryouts/create">
                    <div class="mb-3">
                        <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                        <select class="form-select" id="location_id" name="location_id" required>
                            <option value="">Select a location...</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= $location['id'] ?>">
                                    <?= htmlspecialchars($location['name']) ?> - <?= htmlspecialchars($location['city']) ?>, <?= htmlspecialchars($location['state']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($locations)): ?>
                            <small class="text-danger">No active locations found. <a href="/admin/tryout-locations/create">Create one first</a>.</small>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label for="age_group" class="form-label">Age Group <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="age_group" name="age_group" required
                               placeholder="e.g., 10U, 12U, 14U">
                        <small class="text-muted">Standard format: 8U, 10U, 12U, 14U, 16U, 18U</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tryout_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tryout_date" name="tryout_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="start_time" name="start_time" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="end_time" name="end_time" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cost" class="form-label">Cost <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="cost" name="cost" required
                                       min="0" step="0.01" value="0.00">
                            </div>
                            <small class="text-muted">Set to $0.00 for free tryouts</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_participants" class="form-label">Max Participants</label>
                            <input type="number" class="form-control" id="max_participants" name="max_participants"
                                   min="1" placeholder="Leave blank for unlimited">
                            <small class="text-muted">Leave blank for no capacity limit</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Initial Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="scheduled">Scheduled (not yet open for registration)</option>
                            <option value="open" selected>Open (accepting registrations)</option>
                        </select>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Create Tryout
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
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Tryout Tips</h5>
                <ul class="small">
                    <li><strong>Age Groups:</strong> Use standard format (8U, 10U, 12U, etc.)</li>
                    <li><strong>Scheduling:</strong>
                        <ul>
                            <li>Allow 2-3 hours for thorough evaluation</li>
                            <li>Schedule breaks between age groups</li>
                            <li>Avoid conflicting with games/practices</li>
                        </ul>
                    </li>
                    <li><strong>Capacity:</strong>
                        <ul>
                            <li>Consider available coaches/evaluators</li>
                            <li>Recommended: 20-30 players per tryout</li>
                            <li>Leave blank for unlimited capacity</li>
                        </ul>
                    </li>
                    <li><strong>Cost:</strong>
                        <ul>
                            <li>Typical range: $25-$50</li>
                            <li>Set to $0 for free tryouts</li>
                            <li>Payment tracking available after creation</li>
                        </ul>
                    </li>
                    <li><strong>Status:</strong>
                        <ul>
                            <li><strong>Scheduled:</strong> Created but not yet open</li>
                            <li><strong>Open:</strong> Accepting registrations</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Manage Tryouts</h3>
    <div>
        <a href="/admin/tryouts/import" class="btn btn-outline-secondary">
            <i class="bi bi-upload"></i> Import CSV
        </a>
        <a href="/admin/tryouts/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Tryout
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/tryouts" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Age Group</label>
                <input type="text" name="age_group" class="form-control" value="<?= htmlspecialchars($filters['age_group'] ?? '') ?>" placeholder="e.g., 10U">
            </div>
            <div class="col-md-2">
                <label class="form-label">Location</label>
                <select name="location_id" class="form-select">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= $location['id'] ?>" <?= (isset($filters['location_id']) && $filters['location_id'] == $location['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($location['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="scheduled" <?= (isset($filters['status']) && $filters['status'] === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                    <option value="open" <?= (isset($filters['status']) && $filters['status'] === 'open') ? 'selected' : '' ?>>Open</option>
                    <option value="closed" <?= (isset($filters['status']) && $filters['status'] === 'closed') ? 'selected' : '' ?>>Closed</option>
                    <option value="cancelled" <?= (isset($filters['status']) && $filters['status'] === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    <option value="completed" <?= (isset($filters['status']) && $filters['status'] === 'completed') ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Results count -->
<p class="text-muted">Showing <?= count($tryouts) ?> of <?= $totalCount ?> tryouts</p>

<!-- Tryouts table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>Age Group</th>
                    <th>Location</th>
                    <th>Capacity</th>
                    <th>Cost</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tryouts)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No tryouts found. <a href="/admin/tryouts/create">Create one</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tryouts as $tryout): ?>
                        <?php
                        $isPast = strtotime($tryout['tryout_date']) < strtotime('today');
                        $spotsLeft = $tryout['max_participants'] ? ($tryout['max_participants'] - $tryout['current_participants']) : 'Unlimited';
                        ?>
                        <tr class="<?= $isPast ? 'table-secondary' : '' ?>">
                            <td>
                                <strong><?= date('M j, Y', strtotime($tryout['tryout_date'])) ?></strong><br>
                                <small class="text-muted">
                                    <?= date('g:i A', strtotime($tryout['start_time'])) ?> -
                                    <?= date('g:i A', strtotime($tryout['end_time'])) ?>
                                </small>
                            </td>
                            <td><strong><?= htmlspecialchars($tryout['age_group']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($tryout['location_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($tryout['location_city']) ?>, <?= htmlspecialchars($tryout['location_state']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $tryout['max_participants'] && $tryout['current_participants'] >= $tryout['max_participants'] ? 'danger' : 'info' ?>">
                                    <?= $tryout['current_participants'] ?><?= $tryout['max_participants'] ? '/' . $tryout['max_participants'] : '' ?>
                                </span>
                                <?php if ($spotsLeft !== 'Unlimited'): ?>
                                    <br><small class="text-muted"><?= $spotsLeft ?> spots left</small>
                                <?php endif; ?>
                            </td>
                            <td>$<?= number_format($tryout['cost'], 2) ?></td>
                            <td>
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
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/tryout-registrations?tryout_id=<?= $tryout['id'] ?>" class="btn btn-outline-info" title="View Registrations">
                                        <i class="bi bi-people"></i>
                                    </a>
                                    <a href="/admin/tryouts/edit?id=<?= $tryout['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            Status
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $tryout['id'] ?>, 'scheduled')">Scheduled</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $tryout['id'] ?>, 'open')">Open</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $tryout['id'] ?>, 'closed')">Closed</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="updateStatus(<?= $tryout['id'] ?>, 'completed')">Completed</a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?= $tryout['id'] ?>, 'cancelled')">Cancelled</a></li>
                                        </ul>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteTryout(<?= $tryout['id'] ?>, '<?= htmlspecialchars($tryout['age_group'], ENT_QUOTES) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page - 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page + 1 ?><?= http_build_query($filters) ? '&' . http_build_query($filters) : '' ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
<?php endif; ?>

<!-- Hidden forms for POST actions -->
<form id="statusForm" method="POST" action="/admin/tryouts/update-status" style="display: none;">
    <input type="hidden" name="id" id="statusId">
    <input type="hidden" name="status" id="statusValue">
</form>

<form id="deleteForm" method="POST" action="/admin/tryouts/delete" style="display: none;">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function updateStatus(id, status) {
    if (confirm('Are you sure you want to change the status to "' + status + '"?')) {
        document.getElementById('statusId').value = id;
        document.getElementById('statusValue').value = status;
        document.getElementById('statusForm').submit();
    }
}

function deleteTryout(id, ageGroup) {
    if (confirm('Are you sure you want to delete the ' + ageGroup + ' tryout?\n\nThis will also delete all associated registrations.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

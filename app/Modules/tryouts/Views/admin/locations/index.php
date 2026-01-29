<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Tryout Locations</h3>
    <a href="/admin/tryout-locations/create" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Location
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="/admin/tryout-locations" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="active" class="form-select">
                    <option value="">All</option>
                    <option value="1" <?= isset($filters['active']) && $filters['active'] === 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= isset($filters['active']) && $filters['active'] === 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($filters['city'] ?? '') ?>" placeholder="Filter by city">
            </div>
            <div class="col-md-2">
                <label class="form-label">State</label>
                <input type="text" name="state" class="form-control" value="<?= htmlspecialchars($filters['state'] ?? '') ?>" placeholder="State">
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Name or address">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Results count -->
<p class="text-muted">Showing <?= count($locations) ?> of <?= $totalCount ?> locations</p>

<!-- Locations table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>City/State</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($locations)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No locations found. <a href="/admin/tryout-locations/create">Create one</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($locations as $location): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($location['name']) ?></strong>
                                <?php if (!empty($location['special_instructions'])): ?>
                                    <br><small class="text-muted"><i class="bi bi-info-circle"></i> Has special instructions</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($location['street_address']) ?></td>
                            <td><?= htmlspecialchars($location['city']) ?>, <?= htmlspecialchars($location['state']) ?> <?= htmlspecialchars($location['zip_code']) ?></td>
                            <td>
                                <?php if ($location['active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="/admin/tryout-locations/edit?id=<?= $location['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-outline-<?= $location['active'] ? 'warning' : 'success' ?>"
                                            onclick="toggleActive(<?= $location['id'] ?>, <?= $location['active'] ? 0 : 1 ?>)">
                                        <i class="bi bi-<?= $location['active'] ? 'pause' : 'play' ?>-circle"></i>
                                        <?= $location['active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteLocation(<?= $location['id'] ?>, '<?= htmlspecialchars($location['name'], ENT_QUOTES) ?>')">
                                        <i class="bi bi-trash"></i> Delete
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
<form id="toggleForm" method="POST" action="/admin/tryout-locations/toggle-active" style="display: none;">
    <input type="hidden" name="id" id="toggleId">
    <input type="hidden" name="active" id="toggleActive">
</form>

<form id="deleteForm" method="POST" action="/admin/tryout-locations/delete" style="display: none;">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function toggleActive(id, active) {
    if (confirm('Are you sure you want to ' + (active ? 'activate' : 'deactivate') + ' this location?')) {
        document.getElementById('toggleId').value = id;
        document.getElementById('toggleActive').value = active;
        document.getElementById('toggleForm').submit();
    }
}

function deleteLocation(id, name) {
    if (confirm('Are you sure you want to delete "' + name + '"?\n\nThis will fail if the location has associated tryouts.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}
</script>

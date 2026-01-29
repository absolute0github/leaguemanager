<?php
// Get data from controller
$players = $players ?? [];
$filters = $filters ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
$stats = $stats ?? ['ageGroups' => [], 'statuses' => []];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Player Management</h2>
            <p class="text-muted">Search and manage player information</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <?php if (!empty($stats['ageGroups']) || !empty($stats['statuses'])): ?>
        <div class="row mb-4">
            <!-- Age Groups -->
            <?php foreach ($stats['ageGroups'] ?? [] as $ageGroup): ?>
                <div class="col-sm-6 col-md-4 col-lg-2 mb-2">
                    <div class="card text-center">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1"><?php echo htmlspecialchars($ageGroup['age_group'] ?? 'N/A'); ?></h6>
                            <div class="h4 mb-0"><?php echo number_format($ageGroup['count'] ?? 0); ?></div>
                            <small class="text-muted">Players</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row mb-4">
            <!-- Registration Statuses -->
            <?php foreach ($stats['statuses'] ?? [] as $status): ?>
                <div class="col-sm-6 col-md-4 col-lg-2 mb-2">
                    <div class="card text-center">
                        <div class="card-body p-2">
                            <h6 class="card-title mb-1"><?php echo htmlspecialchars(ucfirst($status['registration_status'] ?? 'N/A')); ?></h6>
                            <div class="h4 mb-0"><?php echo number_format($status['count'] ?? 0); ?></div>
                            <small class="text-muted">Count</small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/players" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Name, email, phone..."
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>

                <!-- Age Group Filter -->
                <div class="col-md-3">
                    <label for="age_group" class="form-label">Age Group</label>
                    <select class="form-select" id="age_group" name="age_group">
                        <option value="">All Age Groups</option>
                        <option value="8U" <?php echo ($filters['age_group'] ?? '') === '8U' ? 'selected' : ''; ?>>8U</option>
                        <option value="10U" <?php echo ($filters['age_group'] ?? '') === '10U' ? 'selected' : ''; ?>>10U</option>
                        <option value="12U" <?php echo ($filters['age_group'] ?? '') === '12U' ? 'selected' : ''; ?>>12U</option>
                        <option value="14U" <?php echo ($filters['age_group'] ?? '') === '14U' ? 'selected' : ''; ?>>14U</option>
                        <option value="16U" <?php echo ($filters['age_group'] ?? '') === '16U' ? 'selected' : ''; ?>>16U</option>
                        <option value="18U" <?php echo ($filters['age_group'] ?? '') === '18U' ? 'selected' : ''; ?>>18U</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="tryout" <?php echo ($filters['status'] ?? '') === 'tryout' ? 'selected' : ''; ?>>Tryout</option>
                        <option value="committed" <?php echo ($filters['status'] ?? '') === 'committed' ? 'selected' : ''; ?>>Committed</option>
                        <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Results Header -->
    <div class="row mb-3">
        <div class="col">
            <p class="text-muted mb-0">
                Showing <strong><?php echo count($players); ?></strong> of <strong><?php echo number_format($totalCount); ?></strong> players
                <?php if (!empty($filters['search'])): ?>
                    matching "<strong><?php echo htmlspecialchars($filters['search']); ?></strong>"
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Players Table -->
    <?php if (empty($players)): ?>
        <div class="alert alert-info">
            <strong>No players found.</strong> Try adjusting your filters.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Age Group</th>
                        <th>Status</th>
                        <th>User Account</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($players as $player): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($player['first_name'] ?? ''); ?> <?php echo htmlspecialchars($player['last_name'] ?? ''); ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($player['email'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($player['email'] ?? ''); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($player['phone'] ?? '-'); ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($player['age_group'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = match($player['registration_status'] ?? '') {
                                    'active' => 'success',
                                    'committed' => 'info',
                                    'tryout' => 'primary',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($player['registration_status'] ?? 'unknown')); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($player['user_id'])): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Linked
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-times"></i> None
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/admin/players/view?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/admin/players/edit?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/players?page=<?php echo max(1, $page - 1); ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['age_group']) ? '&age_group=' . urlencode($filters['age_group']) : ''; ?><?php echo !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : ''; ?>">
                            Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/admin/players?page=<?php echo $i; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['age_group']) ? '&age_group=' . urlencode($filters['age_group']) : ''; ?><?php echo !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/players?page=<?php echo min($totalPages, $page + 1); ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['age_group']) ? '&age_group=' . urlencode($filters['age_group']) : ''; ?><?php echo !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

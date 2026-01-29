<?php
// Get data from controller
$users = $users ?? [];
$filters = $filters ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>User Management</h2>
            <p class="text-muted">Manage system users and their roles</p>
        </div>
        <div class="col-auto">
            <a href="/admin/users/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create User
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/users" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Username, email..."
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>

                <!-- Role Filter -->
                <div class="col-md-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="">All Roles</option>
                        <option value="superuser" <?php echo ($filters['role'] ?? '') === 'superuser' ? 'selected' : ''; ?>>Superuser</option>
                        <option value="admin" <?php echo ($filters['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="coach" <?php echo ($filters['role'] ?? '') === 'coach' ? 'selected' : ''; ?>>Coach</option>
                        <option value="player" <?php echo ($filters['role'] ?? '') === 'player' ? 'selected' : ''; ?>>Player</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="pending" <?php echo ($filters['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
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
                Showing <strong><?php echo count($users); ?></strong> of <strong><?php echo number_format($totalCount); ?></strong> users
                <?php if (!empty($filters['search'])): ?>
                    matching "<strong><?php echo htmlspecialchars($filters['search']); ?></strong>"
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Users Table -->
    <?php if (empty($users)): ?>
        <div class="alert alert-info">
            <strong>No users found.</strong> Try adjusting your filters or <a href="/admin/users/create">create a new user</a>.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Email Verified</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($u['username'] ?? ''); ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($u['email'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($u['email'] ?? ''); ?>
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars(ucfirst($u['role'] ?? 'unknown')); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = match($u['status'] ?? '') {
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($u['status'] ?? 'unknown')); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($u['email_verified'] ?? false): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-times"></i> No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php
                                        if (!empty($u['created_at'])) {
                                            $date = new DateTime($u['created_at']);
                                            echo $date->format('M d, Y');
                                        }
                                    ?>
                                </small>
                            </td>
                            <td>
                                <a href="/admin/users/view?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/admin/users/edit?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning">
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
                        <a class="page-link" href="/admin/users?page=<?php echo max(1, $page - 1); ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . urlencode($filters['role']) : ''; ?><?php echo !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : ''; ?>">
                            Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/admin/users?page=<?php echo $i; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . urlencode($filters['role']) : ''; ?><?php echo !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/users?page=<?php echo min($totalPages, $page + 1); ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['role']) ? '&role=' . urlencode($filters['role']) : ''; ?><?php echo !empty($filters['status']) ? '&status=' . urlencode($filters['status']) : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

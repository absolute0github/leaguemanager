<?php
// Get data from controller
$coaches = $coaches ?? [];
$filters = $filters ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
$teams = $teams ?? [];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Coach Management</h2>
            <p class="text-muted">View and manage coach assignments</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/coaches" class="row g-3">
                <!-- Team Filter -->
                <div class="col-md-4">
                    <label for="team_id" class="form-label">Team</label>
                    <select class="form-select" id="team_id" name="team_id">
                        <option value="">All Teams</option>
                        <?php foreach ($teams as $team): ?>
                            <option value="<?php echo $team['id']; ?>"
                                    <?php echo ($filters['team_id'] ?? '') == $team['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($team['name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Coach Type Filter -->
                <div class="col-md-4">
                    <label for="coach_type" class="form-label">Coach Type</label>
                    <select class="form-select" id="coach_type" name="coach_type">
                        <option value="">All Types</option>
                        <option value="head" <?php echo ($filters['coach_type'] ?? '') === 'head' ? 'selected' : ''; ?>>Head Coach</option>
                        <option value="assistant" <?php echo ($filters['coach_type'] ?? '') === 'assistant' ? 'selected' : ''; ?>>Assistant Coach</option>
                        <option value="volunteer" <?php echo ($filters['coach_type'] ?? '') === 'volunteer' ? 'selected' : ''; ?>>Volunteer</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="col-md-4 d-flex align-items-end">
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
                Showing <strong><?php echo count($coaches); ?></strong> of <strong><?php echo number_format($totalCount); ?></strong> coaches
            </p>
        </div>
    </div>

    <!-- Coaches Table -->
    <?php if (empty($coaches)): ?>
        <div class="alert alert-info">
            <strong>No coaches found.</strong> Try adjusting your filters.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Team</th>
                        <th>Coach Type</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coaches as $coach): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($coach['username'] ?? ''); ?></strong>
                            </td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($coach['email'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($coach['email'] ?? ''); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($coach['team_name'] ?? 'Unassigned'); ?>
                            </td>
                            <td>
                                <?php
                                $typeClass = match($coach['coach_type'] ?? '') {
                                    'head' => 'warning',
                                    'assistant' => 'info',
                                    'volunteer' => 'secondary',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $typeClass; ?>">
                                    <?php
                                    $typeLabel = match($coach['coach_type'] ?? '') {
                                        'head' => 'Head Coach',
                                        'assistant' => 'Assistant',
                                        'volunteer' => 'Volunteer',
                                        default => 'Unknown'
                                    };
                                    echo $typeLabel;
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = match($coach['status'] ?? '') {
                                    'active' => 'success',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($coach['status'] ?? 'unknown')); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/admin/users/view?id=<?php echo $coach['user_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
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
                        <a class="page-link" href="/admin/coaches?page=<?php echo max(1, $page - 1); ?><?php echo !empty($filters['team_id']) ? '&team_id=' . urlencode($filters['team_id']) : ''; ?><?php echo !empty($filters['coach_type']) ? '&coach_type=' . urlencode($filters['coach_type']) : ''; ?>">
                            Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/admin/coaches?page=<?php echo $i; ?><?php echo !empty($filters['team_id']) ? '&team_id=' . urlencode($filters['team_id']) : ''; ?><?php echo !empty($filters['coach_type']) ? '&coach_type=' . urlencode($filters['coach_type']) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/coaches?page=<?php echo min($totalPages, $page + 1); ?><?php echo !empty($filters['team_id']) ? '&team_id=' . urlencode($filters['team_id']) : ''; ?><?php echo !empty($filters['coach_type']) ? '&coach_type=' . urlencode($filters['coach_type']) : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

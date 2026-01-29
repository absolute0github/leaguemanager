<?php
// Get data from controller
$teams = $teams ?? [];
$filters = $filters ?? [];
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalCount = $totalCount ?? 0;
$leagues = $leagues ?? [];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Team Management</h2>
            <p class="text-muted">Create and manage teams</p>
        </div>
        <div class="col-auto">
            <a href="/admin/teams/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Team
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="/admin/teams" class="row g-3">
                <!-- Search -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Team name..."
                           value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                </div>

                <!-- League Filter -->
                <div class="col-md-3">
                    <label for="league_id" class="form-label">League</label>
                    <select class="form-select" id="league_id" name="league_id">
                        <option value="">All Leagues</option>
                        <?php foreach ($leagues as $league): ?>
                            <option value="<?php echo $league['id']; ?>"
                                    <?php echo ($filters['league_id'] ?? '') == $league['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($league['name'] ?? ''); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
                Showing <strong><?php echo count($teams); ?></strong> of <strong><?php echo number_format($totalCount); ?></strong> teams
                <?php if (!empty($filters['search'])): ?>
                    matching "<strong><?php echo htmlspecialchars($filters['search']); ?></strong>"
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Teams Table -->
    <?php if (empty($teams)): ?>
        <div class="alert alert-info">
            <strong>No teams found.</strong> Try adjusting your filters or <a href="/admin/teams/create">create a new team</a>.
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Team Name</th>
                        <th>League</th>
                        <th>Age Group</th>
                        <th>Status</th>
                        <th>Roster</th>
                        <th>Coaches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teams as $team): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($team['name'] ?? ''); ?></strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($team['league_name'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars($team['age_group'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $statusClass = match($team['status'] ?? '') {
                                    'active' => 'success',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($team['status'] ?? 'unknown')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo ($team['player_count'] ?? 0); ?>/<?php echo ($team['max_players'] ?? 15); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo ($team['coach_count'] ?? 0); ?> coach<?php echo ($team['coach_count'] ?? 0) !== 1 ? 'es' : ''; ?>
                            </td>
                            <td>
                                <a href="/admin/teams/view?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/admin/teams/edit?id=<?php echo $team['id']; ?>" class="btn btn-sm btn-warning">
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
                        <a class="page-link" href="/admin/teams?page=<?php echo max(1, $page - 1); ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['league_id']) ? '&league_id=' . urlencode($filters['league_id']) : ''; ?><?php echo !empty($filters['age_group']) ? '&age_group=' . urlencode($filters['age_group']) : ''; ?>">
                            Previous
                        </a>
                    </li>

                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="/admin/teams?page=<?php echo $i; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['league_id']) ? '&league_id=' . urlencode($filters['league_id']) : ''; ?><?php echo !empty($filters['age_group']) ? '&age_group=' . urlencode($filters['age_group']) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="/admin/teams?page=<?php echo min($totalPages, $page + 1); ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['league_id']) ? '&league_id=' . urlencode($filters['league_id']) : ''; ?><?php echo !empty($filters['age_group']) ? '&age_group=' . urlencode($filters['age_group']) : ''; ?>">
                            Next
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

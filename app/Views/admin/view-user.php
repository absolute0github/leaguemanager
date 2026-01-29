<?php
// Get data from controller
$targetUser = $targetUser ?? [];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>User Details</h2>
            <p class="text-muted"><?php echo htmlspecialchars($targetUser['username'] ?? ''); ?></p>
        </div>
        <div class="col-auto">
            <a href="/admin/users/edit?id=<?php echo $targetUser['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit User
            </a>
            <a href="/admin/users" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Account Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Username</label>
                            <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($targetUser['username'] ?? ''); ?></strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email</label>
                            <p class="form-control-plaintext"><a href="mailto:<?php echo htmlspecialchars($targetUser['email'] ?? ''); ?>"><?php echo htmlspecialchars($targetUser['email'] ?? ''); ?></a></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Role</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-secondary">
                                    <?php echo htmlspecialchars(ucfirst($targetUser['role'] ?? 'unknown')); ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Account Status</label>
                            <p class="form-control-plaintext">
                                <?php
                                $statusClass = match($targetUser['status'] ?? '') {
                                    'active' => 'success',
                                    'pending' => 'warning',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($targetUser['status'] ?? 'unknown')); ?>
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email Verified</label>
                            <p class="form-control-plaintext">
                                <?php if ($targetUser['email_verified'] ?? false): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning">
                                        <i class="fas fa-times"></i> No
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Created</label>
                            <p class="form-control-plaintext">
                                <?php
                                if (!empty($targetUser['created_at'])) {
                                    $date = new DateTime($targetUser['created_at']);
                                    echo $date->format('M d, Y H:i:s');
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Last Login</label>
                            <p class="form-control-plaintext">
                                <?php
                                if (!empty($targetUser['last_login'])) {
                                    $date = new DateTime($targetUser['last_login']);
                                    echo $date->format('M d, Y H:i:s');
                                } else {
                                    echo '<span class="text-muted">Never</span>';
                                }
                                ?>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">2FA Enabled</label>
                            <p class="form-control-plaintext">
                                <?php if ($targetUser['two_factor_enabled'] ?? false): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check"></i> Yes
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times"></i> No
                                    </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Activity -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Account Security</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Failed Login Attempts</label>
                            <p class="form-control-plaintext"><strong><?php echo htmlspecialchars($targetUser['failed_login_attempts'] ?? 0); ?></strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Account Lockout Until</label>
                            <p class="form-control-plaintext">
                                <?php
                                if (!empty($targetUser['lockout_until'])) {
                                    $date = new DateTime($targetUser['lockout_until']);
                                    echo '<span class="badge bg-danger">' . $date->format('M d, Y H:i:s') . '</span>';
                                } else {
                                    echo '<span class="text-muted">Not locked</span>';
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <a href="/admin/users/edit?id=<?php echo $targetUser['id']; ?>" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit User
                    </a>
                    <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash"></i> Deactivate Account
                    </button>
                </div>
            </div>

            <!-- User Details Summary -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Role:</strong><br>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($targetUser['role'] ?? '')); ?></span>
                        </li>
                        <li class="mb-2">
                            <strong>Status:</strong><br>
                            <span class="badge bg-<?php echo ($targetUser['status'] === 'active') ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars(ucfirst($targetUser['status'] ?? '')); ?>
                            </span>
                        </li>
                        <li>
                            <strong>Email Verified:</strong><br>
                            <?php echo ($targetUser['email_verified'] ?? false) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>'; ?>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete/Deactivate Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deactivate Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to deactivate <strong><?php echo htmlspecialchars($targetUser['username'] ?? ''); ?></strong>'s account?</p>
                <p class="text-muted small">This will prevent them from logging in but will not delete their data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger">Deactivate</button>
            </div>
        </div>
    </div>
</div>

<?php
// Get data from controller
$targetUser = $targetUser ?? [];
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Edit User</h2>
            <p class="text-muted"><?php echo htmlspecialchars($targetUser['username'] ?? ''); ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">User Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/users/update" class="admin-form">
                        <!-- Hidden Fields -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($targetUser['id'] ?? ''); ?>">

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?php echo htmlspecialchars($targetUser['username'] ?? ''); ?>"
                                   readonly>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo htmlspecialchars($targetUser['email'] ?? ''); ?>"
                                   required>
                            <small class="text-muted">User will need to verify email if changed</small>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="superuser" <?php echo ($targetUser['role'] ?? '') === 'superuser' ? 'selected' : ''; ?>>Superuser</option>
                                <option value="admin" <?php echo ($targetUser['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="coach" <?php echo ($targetUser['role'] ?? '') === 'coach' ? 'selected' : ''; ?>>Coach</option>
                                <option value="player" <?php echo ($targetUser['role'] ?? '') === 'player' ? 'selected' : ''; ?>>Player</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">-- Select Status --</option>
                                <option value="active" <?php echo ($targetUser['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo ($targetUser['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="inactive" <?php echo ($targetUser['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <!-- Email Verified -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="email_verified" name="email_verified"
                                       <?php echo ($targetUser['email_verified'] ?? false) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="email_verified">
                                    Email Verified
                                </label>
                            </div>
                        </div>

                        <!-- Password Reset Section -->
                        <div class="card mb-3 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Change Password</h6>
                                <p class="text-muted small">Leave blank to keep current password</p>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Leave blank to keep current">
                                    <small class="text-muted">
                                        Minimum 8 characters, must include uppercase, lowercase, number, and special character
                                    </small>
                                </div>

                                <div class="mb-0">
                                    <label for="password_confirm" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <a href="/admin/users/view?id=<?php echo $targetUser['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Account Info Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Account Created</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <?php
                        if (!empty($targetUser['created_at'])) {
                            $date = new DateTime($targetUser['created_at']);
                            echo $date->format('M d, Y H:i:s');
                        }
                        ?>
                    </p>
                </div>
            </div>

            <!-- Current Status Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Current Status</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Role:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-secondary">
                                <?php echo htmlspecialchars(ucfirst($targetUser['role'] ?? '')); ?>
                            </span>
                        </dd>

                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-<?php echo ($targetUser['status'] === 'active') ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars(ucfirst($targetUser['status'] ?? '')); ?>
                            </span>
                        </dd>

                        <dt class="col-sm-6">Email Verified:</dt>
                        <dd class="col-sm-6">
                            <?php echo ($targetUser['email_verified'] ?? false) ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-warning"></i>'; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Get data from controller
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Create New User</h2>
            <p class="text-muted">Add a new user to the system</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">User Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/users/create" class="admin-form">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                        <!-- Username -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username"
                                   placeholder="Enter username (minimum 3 characters)"
                                   minlength="3"
                                   required>
                            <small class="text-muted">Usernames must be unique and contain only letters, numbers, and underscores</small>
                        </div>

                        <!-- Email -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                   placeholder="user@example.com"
                                   required>
                            <small class="text-muted">Email must be unique in the system</small>
                        </div>

                        <!-- Role -->
                        <div class="mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">-- Select Role --</option>
                                <option value="superuser">Superuser (Full system access)</option>
                                <option value="admin">Admin (Manage users, teams, players)</option>
                                <option value="coach">Coach (Manage team and roster)</option>
                                <option value="player">Player (View profile and teams)</option>
                            </select>
                        </div>

                        <!-- Password Section -->
                        <div class="card mb-3 bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Password Requirements</h6>
                                <p class="text-muted small mb-3">Password must contain:</p>
                                <ul class="small text-muted">
                                    <li>Minimum 8 characters</li>
                                    <li>At least one uppercase letter (A-Z)</li>
                                    <li>At least one lowercase letter (a-z)</li>
                                    <li>At least one number (0-9)</li>
                                    <li>At least one special character (!@#$%^&*)</li>
                                </ul>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Enter a strong password"
                                           minlength="8"
                                           required>
                                    <div id="passwordStrength" class="mt-2"></div>
                                </div>

                                <div class="mb-0">
                                    <label for="password_confirm" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                           placeholder="Re-enter password"
                                           required>
                                    <div id="passwordMatch" class="mt-2"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Verification -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="email_verified" name="email_verified">
                                <label class="form-check-label" for="email_verified">
                                    Mark email as verified
                                </label>
                            </div>
                            <small class="text-muted">If unchecked, user will receive email verification link</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create User
                            </button>
                            <a href="/admin/users" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-md-4">
            <!-- Role Guide -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Role Guide</h6>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-12 mb-2">Superuser</dt>
                        <dd class="col-sm-12 mb-3 small text-muted">
                            Full system access. Can create other admins and manage all settings.
                        </dd>

                        <dt class="col-sm-12 mb-2">Admin</dt>
                        <dd class="col-sm-12 mb-3 small text-muted">
                            Manage users, players, teams, coaches, and review registrations.
                        </dd>

                        <dt class="col-sm-12 mb-2">Coach</dt>
                        <dd class="col-sm-12 mb-3 small text-muted">
                            Manage assigned teams and rosters. View player information.
                        </dd>

                        <dt class="col-sm-12 mb-2">Player</dt>
                        <dd class="col-sm-12 mb-0 small text-muted">
                            View own profile, teams, and tryout information.
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Tips</h6>
                </div>
                <div class="card-body small">
                    <ul class="ps-3 mb-0">
                        <li class="mb-2">Usernames are case-sensitive</li>
                        <li class="mb-2">Emails must be unique and valid</li>
                        <li class="mb-2">Passwords are case-sensitive</li>
                        <li>Users will be notified of their account via email</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-side Password Validation -->
<script>
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthEl = document.getElementById('passwordStrength');

    // Check password requirements
    const hasLength = password.length >= 8;
    const hasUpper = /[A-Z]/.test(password);
    const hasLower = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);

    const strength = [hasLength, hasUpper, hasLower, hasNumber, hasSpecial].filter(Boolean).length;

    let html = '<div class="small">';
    html += `<div class="form-check"><input type="checkbox" disabled ${hasLength ? 'checked' : ''}> 8+ characters</div>`;
    html += `<div class="form-check"><input type="checkbox" disabled ${hasUpper ? 'checked' : ''}> Uppercase letter</div>`;
    html += `<div class="form-check"><input type="checkbox" disabled ${hasLower ? 'checked' : ''}> Lowercase letter</div>`;
    html += `<div class="form-check"><input type="checkbox" disabled ${hasNumber ? 'checked' : ''}> Number</div>`;
    html += `<div class="form-check"><input type="checkbox" disabled ${hasSpecial ? 'checked' : ''}> Special character</div>`;
    html += '</div>';

    strengthEl.innerHTML = html;
});

document.getElementById('password_confirm').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirm = this.value;
    const matchEl = document.getElementById('passwordMatch');

    if (confirm === '') {
        matchEl.innerHTML = '';
    } else if (password === confirm) {
        matchEl.innerHTML = '<div class="alert alert-success mb-0 py-1 px-2 small"><i class="fas fa-check"></i> Passwords match</div>';
    } else {
        matchEl.innerHTML = '<div class="alert alert-danger mb-0 py-1 px-2 small"><i class="fas fa-exclamation"></i> Passwords do not match</div>';
    }
});
</script>

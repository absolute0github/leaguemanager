<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd6 0%, #6a4190 100%);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="text-center mb-4">
                    <h1 class="text-white"><i class="fas fa-baseball-ball me-2"></i><?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?></h1>
                </div>

                <div class="card shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="card-title text-center mb-4">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </h2>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php foreach ((array)$errors as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php foreach ((array)$success as $msg): ?>
                                    <div><?php echo htmlspecialchars($msg); ?></div>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Step 1: Email Lookup -->
                        <?php if (empty($email)): ?>
                            <p class="text-muted text-center mb-4">
                                Enter your email address to create an account. Your email will be your username.
                            </p>

                            <form method="GET" action="/register" class="mb-4">
                                <div class="mb-3">
                                    <label for="email_lookup" class="form-label">
                                        <i class="fas fa-envelope me-1"></i>Email Address
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email_lookup" name="email"
                                           placeholder="Enter your email" required autofocus>
                                    <div class="form-text">
                                        We'll check if you have any existing player profiles.
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-arrow-right me-2"></i>Continue
                                </button>
                            </form>

                            <hr class="my-4">
                            <p class="text-center text-muted mb-0">
                                Already have an account? <a href="/login">Log in here</a>
                            </p>

                        <!-- Step 2: Password Creation -->
                        <?php else: ?>
                            <form method="POST" action="/register">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">

                                <!-- Show email (will be username) -->
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-envelope me-1"></i>Email (Your Username)</label>
                                    <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                    <a href="/register" class="small text-muted">Use different email</a>
                                </div>

                                <?php if (!empty($existingPlayer)): ?>
                                    <!-- Existing Player Found -->
                                    <div class="alert alert-success mb-4">
                                        <i class="fas fa-check-circle me-2"></i>
                                        <strong>Player Profile Found!</strong><br>
                                        <small>We found a player profile for <strong><?php echo htmlspecialchars($existingPlayer['first_name'] . ' ' . $existingPlayer['last_name']); ?></strong>
                                        <?php if (!empty($existingPlayer['age_group'])): ?>
                                            (<?php echo htmlspecialchars($existingPlayer['age_group']); ?>)
                                        <?php endif; ?>
                                        associated with this email. Create a password to access your account.</small>
                                    </div>
                                <?php else: ?>
                                    <!-- New User -->
                                    <div class="alert alert-info mb-4">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Create Your Account</strong><br>
                                        <small>After your account is approved, you'll be able to add and manage player profiles.</small>
                                    </div>
                                <?php endif; ?>

                                <!-- Password Fields -->
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="password" name="password"
                                           minlength="8" placeholder="Create a password" required>
                                    <div class="form-text">At least 8 characters</div>
                                </div>

                                <div class="mb-4">
                                    <label for="password_confirm" class="form-label">
                                        <i class="fas fa-lock me-1"></i>Confirm Password <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control form-control-lg" id="password_confirm" name="password_confirm"
                                           minlength="8" placeholder="Confirm your password" required>
                                </div>

                                <div class="alert alert-light border mb-4">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Your account will require administrator approval before you can log in.
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-user-plus me-2"></i>Create Account
                                </button>

                                <p class="text-center text-muted mb-0">
                                    Already have an account? <a href="/login">Log in here</a>
                                </p>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <p class="text-center text-white-50 mt-4 small">
                    &copy; <?php echo date('Y'); ?> <?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

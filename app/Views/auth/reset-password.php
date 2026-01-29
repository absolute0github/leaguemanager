<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Reset Your Password</h2>

                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <form method="POST" action="/reset-password" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Enter new password"
                                    required
                                    autofocus
                                >
                                <small class="text-muted d-block mt-2">
                                    <strong>Password requirements:</strong>
                                    <ul class="mb-0 small">
                                        <li>At least 8 characters</li>
                                        <li>At least one uppercase letter (A-Z)</li>
                                        <li>At least one lowercase letter (a-z)</li>
                                        <li>At least one number (0-9)</li>
                                        <li>At least one special character (!@#$%^&*)</li>
                                    </ul>
                                </small>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirm Password</label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password_confirm"
                                    name="password_confirm"
                                    placeholder="Confirm new password"
                                    required
                                >
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Reset Password
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <a href="/login" class="btn btn-link btn-sm">Back to login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

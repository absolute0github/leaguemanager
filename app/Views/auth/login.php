<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?></title>
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
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="text-center mb-4">
                    <h1 class="text-white"><i class="fas fa-baseball-ball me-2"></i><?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?></h1>
                </div>

                <div class="card shadow-lg">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="card-title text-center mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </h2>

                        <!-- Error Messages -->
                        <?php if (!empty($errors)): ?>
                            <?php foreach ((array)$errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Success Messages -->
                        <?php if (!empty($success)): ?>
                            <?php foreach ((array)$success as $msg): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($msg); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="POST" action="/login">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken ?? ''); ?>">

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username"
                                       placeholder="Enter your username" required autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password"
                                       placeholder="Enter your password" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>

                            <div class="text-center">
                                <a href="/forgot-password" class="text-muted small">Forgot your password?</a>
                            </div>
                        </form>

                        <hr class="my-4">
                        <p class="text-center text-muted mb-0">
                            Don't have an account? <a href="/register">Register here</a>
                        </p>
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

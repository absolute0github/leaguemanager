<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-1">Verify Your Email</h2>
                        <p class="text-muted text-center mb-4">
                            We've sent a verification code to<br>
                            <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                        </p>

                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <form method="POST" action="/verify-email" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                            <div class="mb-4">
                                <label for="token" class="form-label">Verification Code</label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg text-center"
                                    id="token"
                                    name="token"
                                    placeholder="Enter code"
                                    required
                                    autofocus
                                >
                                <small class="text-muted d-block mt-2">
                                    Check your email for the verification code (check spam folder too)
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Verify Email
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <a href="/logout" class="btn btn-link btn-sm">Back to login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

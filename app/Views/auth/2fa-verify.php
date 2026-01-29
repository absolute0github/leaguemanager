<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify 2FA - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-1">Two-Factor Authentication</h2>
                        <p class="text-muted text-center mb-4">
                            Enter the code from your authenticator app
                        </p>

                        <?php if (!empty($errors)): ?>
                            <?php foreach ($errors as $error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <form method="POST" action="/auth/2fa-verify" class="needs-validation">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                            <div class="mb-4">
                                <label for="code" class="form-label">Verification Code</label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg text-center"
                                    id="code"
                                    name="code"
                                    placeholder="000000"
                                    maxlength="20"
                                    required
                                    autofocus
                                    inputmode="numeric"
                                >
                                <small class="text-muted d-block mt-2">
                                    Enter the 6-digit code from your authenticator app or a backup code
                                </small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Verify
                            </button>
                        </form>

                        <hr>

                        <div class="alert alert-info small" role="alert">
                            <strong>Don't have access to your authenticator?</strong>
                            <p class="mb-0">Use one of your backup codes. You can use each backup code only once.</p>
                        </div>

                        <div class="text-center">
                            <a href="/logout" class="text-muted">Try a different account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Allow input of 6-digit code or backup codes (with dashes/spaces)
        document.getElementById('code').addEventListener('input', function(e) {
            // Allow numbers, dashes, and spaces
            this.value = this.value.replace(/[^0-9\-\s]/g, '');
        });
    </script>
</body>
</html>

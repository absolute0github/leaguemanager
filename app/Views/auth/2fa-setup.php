<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Setup Two-Factor Authentication</h2>
                        <p class="text-muted text-center mb-4">
                            Two-factor authentication adds an extra layer of security to your account.
                        </p>

                        <div class="alert alert-info" role="alert">
                            <strong>Step 1:</strong> Install an authenticator app on your phone:
                            <ul class="mb-0 mt-2">
                                <li>Google Authenticator</li>
                                <li>Microsoft Authenticator</li>
                                <li>Authy</li>
                                <li>Any RFC 6238 TOTP compatible app</li>
                            </ul>
                        </div>

                        <div class="alert alert-info" role="alert">
                            <strong>Step 2:</strong> Scan the QR code below with your authenticator app
                        </div>

                        <div class="text-center mb-4">
                            <div class="card bg-white p-4" style="border: 1px solid #ddd;">
                                <div style="display: inline-block; background: white; padding: 10px; border-radius: 4px;">
                                    <?php echo $qrCode; ?>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning" role="alert">
                            <strong>Can't scan QR code?</strong>
                            <p class="mb-0">Enter this code manually in your authenticator app:</p>
                            <code class="d-block mt-2 p-2 bg-light"><?php echo htmlspecialchars($secret); ?></code>
                        </div>

                        <form method="POST" action="/auth/2fa-verify-setup" class="mt-4">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                            <div class="mb-3">
                                <label for="code" class="form-label">
                                    <strong>Step 3:</strong> Enter the 6-digit code from your authenticator app
                                </label>
                                <input
                                    type="text"
                                    class="form-control form-control-lg text-center"
                                    id="code"
                                    name="code"
                                    placeholder="000000"
                                    maxlength="6"
                                    pattern="[0-9]{6}"
                                    required
                                    autofocus
                                    inputmode="numeric"
                                >
                                <small class="text-muted">Enter the 6-digit code that changes every 30 seconds</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                Verify & Enable 2FA
                            </button>
                        </form>

                        <hr>

                        <div class="text-center">
                            <a href="/dashboard" class="btn btn-secondary btn-sm">
                                Skip for now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-format input as user types
        document.getElementById('code').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        });
    </script>
</body>
</html>

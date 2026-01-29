<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Codes - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Save Your Backup Codes</h2>

                        <div class="alert alert-warning" role="alert">
                            <strong>⚠️ Important!</strong>
                            <p class="mb-0">
                                Save these backup codes in a safe place. Each code can be used once if you lose access to your authenticator app.
                            </p>
                        </div>

                        <div class="card bg-light p-4 mb-4">
                            <div class="row g-2">
                                <?php foreach ($backupCodes as $index => $code): ?>
                                    <div class="col-6">
                                        <div class="p-2 bg-white border rounded text-center font-monospace small">
                                            <input type="text" class="form-control form-control-sm backup-code"
                                                   value="<?php echo htmlspecialchars($code); ?>" readonly>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary w-100 mb-2" onclick="copyAllCodes()">
                                📋 Copy All Codes
                            </button>
                            <button type="button" class="btn btn-outline-secondary w-100" onclick="printCodes()">
                                🖨️ Print Codes
                            </button>
                        </div>

                        <hr>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="confirm" required>
                            <label class="form-check-label" for="confirm">
                                I have saved my backup codes in a safe place
                            </label>
                        </div>

                        <a href="/dashboard" class="btn btn-success w-100" id="continueBtn" disabled>
                            Continue to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyAllCodes() {
            const codes = [];
            document.querySelectorAll('.backup-code').forEach(input => {
                codes.push(input.value);
            });
            const text = codes.join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('Backup codes copied to clipboard!');
            });
        }

        function printCodes() {
            window.print();
        }

        document.getElementById('confirm').addEventListener('change', function() {
            document.getElementById('continueBtn').disabled = !this.checked;
        });
    </script>

    <style media="print">
        body {
            background: white;
        }
        .btn, .alert, .form-check {
            display: none;
        }
    </style>
</body>
</html>

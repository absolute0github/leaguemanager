<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body>
    <div class="d-flex flex-column min-vh-100">
        <!-- Header/Navigation -->
        <?php include __DIR__ . '/../partials/header.php'; ?>

        <!-- Alerts -->
        <div class="container-fluid mt-3">
            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <?php foreach ($success as $message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Main Content -->
        <main class="flex-grow-1">
            <div class="container-fluid">
                <?php echo $content ?? ''; ?>
            </div>
        </main>

        <!-- Footer -->
        <?php include __DIR__ . '/../partials/footer.php'; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>

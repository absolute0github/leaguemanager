<header class="bg-primary text-white py-3 shadow-sm">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-0"><?php echo $_ENV['APP_NAME'] ?? 'IVL Baseball League'; ?></h1>
            </div>
            <?php if (isset($user) && $user): ?>
                <div class="text-end">
                    <p class="mb-1">Welcome, <strong><?php echo htmlspecialchars($user['username']); ?></strong></p>
                    <small class="text-light"><?php echo ucfirst($user['role']); ?></small>
                    <br>
                    <a href="/logout" class="btn btn-light btn-sm mt-2">Logout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

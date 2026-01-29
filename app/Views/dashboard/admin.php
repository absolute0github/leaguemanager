<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container-fluid py-4">
        <h1 class="mb-4">Admin Dashboard</h1>
        <p class="text-muted">Welcome to the Admin Control Panel. Here you can manage players, teams, tryouts, and more.</p>

        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Players</h5>
                        <p class="card-text text-muted">Manage player profiles</p>
                        <a href="/admin/players" class="btn btn-primary btn-sm">View Players</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Teams</h5>
                        <p class="card-text text-muted">Manage team rosters</p>
                        <a href="/admin/teams" class="btn btn-primary btn-sm">View Teams</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tryouts</h5>
                        <p class="card-text text-muted">Manage tryout schedule</p>
                        <a href="/admin/tryouts" class="btn btn-primary btn-sm">View Tryouts</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Coaches</h5>
                        <p class="card-text text-muted">Manage coach assignments</p>
                        <a href="/admin/coaches" class="btn btn-primary btn-sm">View Coaches</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coach Dashboard - <?php echo $_ENV['APP_NAME']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <div class="container-fluid py-4">
        <h1 class="mb-4">Coach Dashboard</h1>
        <p class="text-muted">Manage your team and communicate with parents</p>

        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">My Team</h5>
                        <a href="/coach/team" class="btn btn-primary btn-sm">View Team</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Roster</h5>
                        <a href="/coach/roster" class="btn btn-primary btn-sm">View Roster</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Messages</h5>
                        <a href="/coach/messages" class="btn btn-primary btn-sm">Messages</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Schedule</h5>
                        <a href="/coach/schedule" class="btn btn-primary btn-sm">View Schedule</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Get data from controller
$user = $user ?? [];
$tryouts = $tryouts ?? [];
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tryouts</li>
                </ol>
            </nav>
            <h2>Available Tryouts</h2>
            <p class="text-muted">View and register for upcoming tryouts</p>
        </div>
    </div>

    <?php if (empty($tryouts)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-calendar-alt fa-4x text-muted mb-4"></i>
                <h4>No Upcoming Tryouts</h4>
                <p class="text-muted mb-4">There are no tryouts scheduled at this time. Check back later!</p>
                <a href="/dashboard" class="btn btn-primary">
                    Return to Dashboard
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($tryouts as $tryout): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <?php echo htmlspecialchars($tryout['age_group']); ?> Tryout
                            </h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 100px;"><i class="fas fa-calendar me-2"></i>Date:</td>
                                    <td>
                                        <?php
                                            $date = new DateTime($tryout['tryout_date']);
                                            echo $date->format('l, M j, Y');
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-clock me-2"></i>Time:</td>
                                    <td><?php echo htmlspecialchars($tryout['start_time']); ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>Location:</td>
                                    <td>
                                        <?php echo htmlspecialchars($tryout['location_name']); ?><br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($tryout['street_address']); ?><br>
                                            <?php echo htmlspecialchars($tryout['city'] . ', ' . $tryout['state']); ?>
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-dollar-sign me-2"></i>Cost:</td>
                                    <td>
                                        <?php if (!empty($tryout['cost'])): ?>
                                            $<?php echo number_format($tryout['cost'], 2); ?>
                                        <?php else: ?>
                                            <span class="text-success">Free</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><i class="fas fa-info-circle me-2"></i>Status:</td>
                                    <td>
                                        <?php
                                            $statusColors = [
                                                'scheduled' => 'info',
                                                'open' => 'success',
                                                'closed' => 'secondary',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusColor = $statusColors[$tryout['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $statusColor; ?>">
                                            <?php echo ucfirst($tryout['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer bg-light">
                            <?php if ($tryout['status'] === 'open'): ?>
                                <a href="/player/tryouts/register?id=<?php echo $tryout['id']; ?>" class="btn btn-sm btn-primary w-100">
                                    Register
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary w-100" disabled>
                                    Registration Closed
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

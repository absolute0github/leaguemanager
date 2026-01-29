<?php
// Get data from controller
$user = $user ?? [];
$player = $player ?? null;
$parents = $parents ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/player/players">My Players</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (!$player): ?>
        <div class="alert alert-info">
            <strong>No Player Found</strong> - You don't have any player profiles linked to your account.
            <a href="/player/add" class="alert-link">Add a player</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-lg-8">
                <!-- Player Info Card -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php echo htmlspecialchars($player['first_name'] . ' ' . $player['last_name']); ?>
                        </h5>
                        <a href="/player/edit?id=<?php echo $player['id']; ?>" class="btn btn-sm btn-light">
                            Edit
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small mb-3">Personal Information</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Email:</td>
                                        <td>
                                            <?php if (!empty($player['email'])): ?>
                                                <a href="mailto:<?php echo htmlspecialchars($player['email']); ?>">
                                                    <?php echo htmlspecialchars($player['email']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Phone:</td>
                                        <td><?php echo htmlspecialchars($player['phone'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Birthdate:</td>
                                        <td>
                                            <?php if (!empty($player['birthdate'])): ?>
                                                <?php
                                                    $birthdate = new DateTime($player['birthdate']);
                                                    echo $birthdate->format('F j, Y');
                                                ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Age Group:</td>
                                        <td>
                                            <?php if (!empty($player['age_group'])): ?>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($player['age_group']); ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted text-uppercase small mb-3">Registration</h6>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Status:</td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'tryout' => 'warning',
                                                    'committed' => 'info',
                                                    'inactive' => 'secondary',
                                                ];
                                                $statusColor = $statusColors[$player['registration_status'] ?? 'active'] ?? 'secondary';
                                            ?>
                                            <span class="badge bg-<?php echo $statusColor; ?>">
                                                <?php echo ucfirst($player['registration_status'] ?? 'active'); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Source:</td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $player['registration_source'] ?? 'Unknown')); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Registered:</td>
                                        <td>
                                            <?php
                                                if (!empty($player['created_at'])) {
                                                    $created = new DateTime($player['created_at']);
                                                    echo $created->format('M j, Y');
                                                } else {
                                                    echo '-';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Card (if available) -->
                <?php if (!empty($player['street_address']) || !empty($player['city'])): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Address</h6>
                        </div>
                        <div class="card-body">
                            <?php echo htmlspecialchars($player['street_address'] ?? ''); ?>
                            <?php if (!empty($player['address_line2'])): ?>
                                <br><?php echo htmlspecialchars($player['address_line2']); ?>
                            <?php endif; ?>
                            <br>
                            <?php echo htmlspecialchars($player['city'] ?? ''); ?>,
                            <?php echo htmlspecialchars($player['state'] ?? ''); ?>
                            <?php echo htmlspecialchars($player['zip_code'] ?? ''); ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Parents/Guardians (if any) -->
                <?php if (!empty($parents)): ?>
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Parents/Guardians</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($parents as $parent): ?>
                                    <div class="col-md-6 mb-3">
                                        <h6><?php echo htmlspecialchars($parent['full_name']); ?></h6>
                                        <?php if (!empty($parent['email'])): ?>
                                            <div><a href="mailto:<?php echo htmlspecialchars($parent['email']); ?>"><?php echo htmlspecialchars($parent['email']); ?></a></div>
                                        <?php endif; ?>
                                        <?php if (!empty($parent['phone'])): ?>
                                            <div><?php echo htmlspecialchars($parent['phone']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Quick Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/player/edit?id=<?php echo $player['id']; ?>" class="btn btn-outline-primary">
                                Edit Profile
                            </a>
                            <a href="/player/players" class="btn btn-outline-secondary">
                                View All Players
                            </a>
                            <a href="/player/tryouts" class="btn btn-outline-info">
                                View Tryouts
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

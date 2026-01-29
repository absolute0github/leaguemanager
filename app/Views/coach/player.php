<?php
$team = $team ?? [];
$player = $player ?? [];
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-user me-2"></i>Player Details</h2>
            <p class="text-muted mb-0">
                <?php echo htmlspecialchars(($player['first_name'] ?? '') . ' ' . ($player['last_name'] ?? '')); ?>
                <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($team['name'] ?? ''); ?></span>
            </p>
        </div>
        <a href="/coach/roster?id=<?php echo $team['id'] ?? 0; ?>" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Back to Roster
        </a>
    </div>

    <div class="row">
        <!-- Player Info Card -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Player Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width: 80px; height: 80px; font-size: 2rem;">
                            <?php
                            $initials = substr($player['first_name'] ?? 'P', 0, 1) . substr($player['last_name'] ?? 'L', 0, 1);
                            echo htmlspecialchars(strtoupper($initials));
                            ?>
                        </div>
                        <h4 class="mt-3 mb-1"><?php echo htmlspecialchars(($player['first_name'] ?? '') . ' ' . ($player['last_name'] ?? '')); ?></h4>
                        <?php if ($player['jersey_number']): ?>
                            <span class="badge bg-primary fs-5">#<?php echo $player['jersey_number']; ?></span>
                        <?php endif; ?>
                    </div>

                    <table class="table table-borderless mb-0">
                        <tr>
                            <th style="width: 40%;">Age Group</th>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($player['age_group'] ?? '-'); ?></span></td>
                        </tr>
                        <tr>
                            <th>Birthdate</th>
                            <td>
                                <?php if ($player['birthdate']): ?>
                                    <?php echo date('F j, Y', strtotime($player['birthdate'])); ?>
                                    <small class="text-muted">
                                        (<?php
                                        $birth = new DateTime($player['birthdate']);
                                        $now = new DateTime();
                                        echo $birth->diff($now)->y . ' years old';
                                        ?>)
                                    </small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Primary Position</th>
                            <td>
                                <?php if ($player['primary_position']): ?>
                                    <span class="badge bg-success"><?php echo htmlspecialchars($player['primary_position']); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Secondary Position</th>
                            <td>
                                <?php if ($player['secondary_position']): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($player['secondary_position']); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $statusColors = [
                                    'active' => 'success',
                                    'injured' => 'warning',
                                    'inactive' => 'secondary'
                                ];
                                $status = $player['roster_status'] ?? 'active';
                                ?>
                                <span class="badge bg-<?php echo $statusColors[$status] ?? 'secondary'; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Shirt Size</th>
                            <td><?php echo htmlspecialchars($player['shirt_size'] ?? '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Contact Info Card -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-muted mb-3">Player Contact</h6>
                    <table class="table table-borderless mb-4">
                        <tr>
                            <th style="width: 30%;"><i class="fas fa-phone me-2 text-primary"></i>Phone</th>
                            <td>
                                <?php if ($player['phone']): ?>
                                    <a href="tel:<?php echo htmlspecialchars($player['phone']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($player['phone']); ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><i class="fas fa-envelope me-2 text-primary"></i>Email</th>
                            <td>
                                <?php if ($player['email']): ?>
                                    <a href="mailto:<?php echo htmlspecialchars($player['email']); ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($player['email']); ?>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <h6 class="text-muted mb-3">Address</h6>
                    <address class="mb-0">
                        <?php if ($player['street_address']): ?>
                            <?php echo htmlspecialchars($player['street_address']); ?><br>
                            <?php if ($player['street_address_line_2']): ?>
                                <?php echo htmlspecialchars($player['street_address_line_2']); ?><br>
                            <?php endif; ?>
                            <?php echo htmlspecialchars(($player['city'] ?? '') . ', ' . ($player['state'] ?? '') . ' ' . ($player['zip'] ?? '')); ?>
                        <?php else: ?>
                            <span class="text-muted">No address on file</span>
                        <?php endif; ?>
                    </address>
                </div>
            </div>
        </div>
    </div>

    <!-- School Info -->
    <?php if ($player['school_name'] || $player['grade_level']): ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-school me-2"></i>School Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>School:</strong> <?php echo htmlspecialchars($player['school_name'] ?? '-'); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Grade Level:</strong> <?php echo htmlspecialchars($player['grade_level'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Parents/Guardians -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-user-friends me-2"></i>Parents / Guardians</h5>
        </div>
        <div class="card-body">
            <?php if (empty($player['parents'])): ?>
                <p class="text-muted mb-0">No parent/guardian information on file.</p>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($player['parents'] as $parent): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                             style="width: 50px; height: 50px; min-width: 50px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($parent['full_name']); ?></h5>
                                            <span class="badge bg-secondary mb-2">Guardian <?php echo $parent['guardian_number']; ?></span>

                                            <div class="mt-2">
                                                <?php if ($parent['phone']): ?>
                                                    <div class="mb-1">
                                                        <i class="fas fa-phone me-2 text-primary"></i>
                                                        <a href="tel:<?php echo htmlspecialchars($parent['phone']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($parent['phone']); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($parent['email']): ?>
                                                    <div class="mb-1">
                                                        <i class="fas fa-envelope me-2 text-primary"></i>
                                                        <a href="mailto:<?php echo htmlspecialchars($parent['email']); ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($parent['email']); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($parent['coaching_interest'] === 'yes' || $parent['coaching_interest'] === '1'): ?>
                                                <div class="mt-2">
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clipboard-check me-1"></i>Interested in Coaching
                                                    </span>
                                                    <?php if ($parent['baseball_level_played']): ?>
                                                        <br><small class="text-muted">
                                                            Played: <?php echo htmlspecialchars($parent['baseball_level_played']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    <?php if ($parent['coaching_experience_years']): ?>
                                                        <br><small class="text-muted">
                                                            <?php echo $parent['coaching_experience_years']; ?> years coaching experience
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <?php if ($player['email']): ?>
                    <a href="mailto:<?php echo htmlspecialchars($player['email']); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-envelope me-1"></i> Email Player
                    </a>
                <?php endif; ?>

                <?php if (!empty($player['parents'])): ?>
                    <?php
                    $parentEmails = array_filter(array_column($player['parents'], 'email'));
                    if (!empty($parentEmails)):
                    ?>
                        <a href="mailto:<?php echo htmlspecialchars(implode(',', $parentEmails)); ?>" class="btn btn-outline-success">
                            <i class="fas fa-envelope me-1"></i> Email Parents
                        </a>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if ($player['phone']): ?>
                    <a href="tel:<?php echo htmlspecialchars($player['phone']); ?>" class="btn btn-outline-info">
                        <i class="fas fa-phone me-1"></i> Call Player
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Get data from controller
$player = $player ?? [];
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2><?php echo htmlspecialchars($player['first_name'] ?? ''); ?> <?php echo htmlspecialchars($player['last_name'] ?? ''); ?></h2>
            <p class="text-muted">
                <span class="badge bg-secondary"><?php echo htmlspecialchars($player['age_group'] ?? 'N/A'); ?></span>
                <?php if (!empty($player['registration_status'])): ?>
                    <span class="badge bg-primary ms-2"><?php echo htmlspecialchars(ucfirst($player['registration_status'])); ?></span>
                <?php endif; ?>
            </p>
        </div>
        <div class="col-auto">
            <a href="/admin/players/edit?id=<?php echo $player['id']; ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Player
            </a>
            <a href="/admin/players" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Player Information -->
        <div class="col-md-8">
            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email</label>
                            <p class="form-control-plaintext">
                                <a href="mailto:<?php echo htmlspecialchars($player['email'] ?? ''); ?>">
                                    <?php echo htmlspecialchars($player['email'] ?? ''); ?>
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Phone</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($player['phone'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-label text-muted small">Address</label>
                            <p class="form-control-plaintext">
                                <?php
                                $address = [];
                                if (!empty($player['street_address'])) $address[] = htmlspecialchars($player['street_address']);
                                if (!empty($player['street_address_line2'])) $address[] = htmlspecialchars($player['street_address_line2']);
                                if (!empty($player['city'])) $address[] = htmlspecialchars($player['city']);
                                if (!empty($player['state'])) $address[] = htmlspecialchars($player['state']);
                                if (!empty($player['zip_code'])) $address[] = htmlspecialchars($player['zip_code']);
                                echo !empty($address) ? implode(', ', $address) : '-';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Player Information -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Player Information</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Age Group</label>
                            <p class="form-control-plaintext">
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($player['age_group'] ?? 'N/A'); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Birthdate</label>
                            <p class="form-control-plaintext">
                                <?php
                                if (!empty($player['birthdate'])) {
                                    $date = new DateTime($player['birthdate']);
                                    echo $date->format('M d, Y');
                                }
                                ?>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Shirt Size</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($player['shirt_size'] ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Grade Level</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($player['grade_level'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Primary Position</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($player['primary_position'] ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Secondary Position</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($player['secondary_position'] ?? '-'); ?></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">School Name</label>
                            <p class="form-control-plaintext"><?php echo htmlspecialchars($player['school_name'] ?? '-'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Registration Status</label>
                            <p class="form-control-plaintext">
                                <?php
                                $statusClass = match($player['registration_status'] ?? '') {
                                    'active' => 'success',
                                    'committed' => 'info',
                                    'tryout' => 'primary',
                                    'inactive' => 'danger',
                                    default => 'secondary'
                                };
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo htmlspecialchars(ucfirst($player['registration_status'] ?? '')); ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parent/Guardian Information -->
            <?php if (!empty($player['parents']) && count($player['parents']) > 0): ?>
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Parent/Guardian Information</h6>
                    </div>
                    <div class="card-body">
                        <?php foreach ($player['parents'] as $parent): ?>
                            <div class="mb-4">
                                <h6>Guardian <?php echo htmlspecialchars($parent['guardian_number'] ?? ''); ?></h6>
                                <dl class="row">
                                    <dt class="col-sm-4">Name:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($parent['full_name'] ?? '-'); ?></dd>

                                    <dt class="col-sm-4">Phone:</dt>
                                    <dd class="col-sm-8"><?php echo htmlspecialchars($parent['phone'] ?? '-'); ?></dd>

                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8">
                                        <?php if (!empty($parent['email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($parent['email']); ?>">
                                                <?php echo htmlspecialchars($parent['email']); ?>
                                            </a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </dd>

                                    <?php if (!empty($parent['coaching_interest']) || !empty($parent['baseball_level_played'])): ?>
                                        <dt class="col-sm-4">Coaching Interest:</dt>
                                        <dd class="col-sm-8"><?php echo htmlspecialchars($parent['coaching_interest'] ?? '-'); ?></dd>

                                        <dt class="col-sm-4">Baseball Level:</dt>
                                        <dd class="col-sm-8"><?php echo htmlspecialchars($parent['baseball_level_played'] ?? '-'); ?></dd>
                                    <?php endif; ?>
                                </dl>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Account Link -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">User Account</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($player['user_id'])): ?>
                        <p class="mb-2">
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Linked
                            </span>
                        </p>
                        <a href="/admin/users/view?id=<?php echo $player['user_id']; ?>" class="btn btn-sm btn-primary w-100">
                            <i class="fas fa-user"></i> View User Account
                        </a>
                    <?php else: ?>
                        <p class="text-muted small mb-2">No user account linked</p>
                        <p class="text-muted small">This player can be linked to a user account when they register.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    <a href="/admin/players/edit?id=<?php echo $player['id']; ?>" class="btn btn-primary w-100 mb-2">
                        <i class="fas fa-edit"></i> Edit Player
                    </a>
                    <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#sendEmailModal">
                        <i class="fas fa-envelope"></i> Send Email
                    </button>
                </div>
            </div>

            <!-- Player Summary -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Summary</h6>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Age Group:</dt>
                        <dd class="col-sm-6"><?php echo htmlspecialchars($player['age_group'] ?? '-'); ?></dd>

                        <dt class="col-sm-6">Status:</dt>
                        <dd class="col-sm-6">
                            <span class="badge bg-primary">
                                <?php echo htmlspecialchars(ucfirst($player['registration_status'] ?? '')); ?>
                            </span>
                        </dd>

                        <dt class="col-sm-6">Position:</dt>
                        <dd class="col-sm-6"><?php echo htmlspecialchars($player['primary_position'] ?? '-'); ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Email</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Send an email to the player or their parents</p>
                <div class="list-group">
                    <a href="mailto:<?php echo htmlspecialchars($player['email'] ?? ''); ?>" class="list-group-item list-group-item-action">
                        <i class="fas fa-user"></i> Email Player
                        <small class="d-block text-muted"><?php echo htmlspecialchars($player['email'] ?? ''); ?></small>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

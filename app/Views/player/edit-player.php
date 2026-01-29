<?php
// Get data from controller
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
$player = $player ?? [];
$errors = $errors ?? [];
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/player/players">My Players</a></li>
                    <li class="breadcrumb-item active">Edit Player</li>
                </ol>
            </nav>
            <h2>Edit Player</h2>
            <p class="text-muted">Update player information</p>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Player Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/player/update">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player['id'] ?? ''); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?php echo htmlspecialchars($player['first_name'] ?? ''); ?>"
                                       placeholder="Enter first name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?php echo htmlspecialchars($player['last_name'] ?? ''); ?>"
                                       placeholder="Enter last name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($player['email'] ?? ''); ?>"
                                       placeholder="player@example.com">
                                <small class="text-muted">Optional - player's email address</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($player['phone'] ?? ''); ?>"
                                       placeholder="(555) 123-4567">
                                <small class="text-muted">Optional - player's phone number</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="birthdate" class="form-label">Birthdate</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate"
                                       value="<?php echo htmlspecialchars($player['birthdate'] ?? ''); ?>">
                                <small class="text-muted">Used to calculate age for age groups</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="age_group" class="form-label">Age Group</label>
                                <select class="form-select" id="age_group" name="age_group">
                                    <option value="">-- Select Age Group --</option>
                                    <?php
                                    $ageGroups = ['6U', '8U', '9U', '10U', '11U', '12U', '13U', '14U', '15U', '16U', '17U', '18U'];
                                    foreach ($ageGroups as $ag):
                                        $selected = ($player['age_group'] ?? '') === $ag ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $ag; ?>" <?php echo $selected; ?>>
                                            <?php echo $ag; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Update Player
                            </button>
                            <a href="/player/players" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Player Status</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2">
                        <strong>Status:</strong>
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
                    </p>
                    <p class="mb-2">
                        <strong>Source:</strong>
                        <span class="text-muted">
                            <?php echo ucfirst(str_replace('_', ' ', $player['registration_source'] ?? 'Unknown')); ?>
                        </span>
                    </p>
                    <p class="mb-0">
                        <strong>Added:</strong>
                        <span class="text-muted">
                            <?php
                                if (!empty($player['created_at'])) {
                                    $created = new DateTime($player['created_at']);
                                    echo $created->format('M j, Y');
                                } else {
                                    echo 'Unknown';
                                }
                            ?>
                        </span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

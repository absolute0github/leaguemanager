<?php
// Get data from controller
$leagues = $leagues ?? [];
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Create New Team</h2>
            <p class="text-muted">Add a new team to the system</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Team Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/teams/create" class="admin-form">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                        <!-- Team Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Team Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                   placeholder="e.g., Thunder Hawks, Elite Diamonds"
                                   required>
                            <small class="text-muted">Give the team a memorable name</small>
                        </div>

                        <!-- League -->
                        <div class="mb-3">
                            <label for="league_id" class="form-label">League <span class="text-danger">*</span></label>
                            <select class="form-select" id="league_id" name="league_id" required>
                                <option value="">-- Select League --</option>
                                <?php foreach ($leagues as $league): ?>
                                    <option value="<?php echo htmlspecialchars($league['id']); ?>">
                                        <?php echo htmlspecialchars($league['name'] ?? ''); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">The league this team belongs to</small>
                        </div>

                        <!-- Age Group -->
                        <div class="mb-3">
                            <label for="age_group" class="form-label">Age Group <span class="text-danger">*</span></label>
                            <select class="form-select" id="age_group" name="age_group" required>
                                <option value="">-- Select Age Group --</option>
                                <option value="8U">8U (Under 8)</option>
                                <option value="10U">10U (Under 10)</option>
                                <option value="12U">12U (Under 12)</option>
                                <option value="14U">14U (Under 14)</option>
                                <option value="16U">16U (Under 16)</option>
                                <option value="18U">18U (Under 18)</option>
                            </select>
                        </div>

                        <!-- Max Players -->
                        <div class="mb-3">
                            <label for="max_players" class="form-label">Maximum Players <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_players" name="max_players"
                                   value="15"
                                   min="1"
                                   max="50"
                                   required>
                            <small class="text-muted">Standard roster size is 15 players</small>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <small class="text-muted">Active teams are visible and can accept players</small>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Team
                            </button>
                            <a href="/admin/teams" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-md-4">
            <!-- League Guide -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Team Setup</h6>
                </div>
                <div class="card-body small">
                    <p class="mb-3">A team consists of:</p>
                    <ul class="ps-3 mb-0">
                        <li class="mb-2"><strong>Team Name</strong> - Unique identifier</li>
                        <li class="mb-2"><strong>League</strong> - Season or league affiliation</li>
                        <li class="mb-2"><strong>Age Group</strong> - Player age classification</li>
                        <li class="mb-2"><strong>Roster</strong> - List of assigned players</li>
                        <li><strong>Coaches</strong> - Head coach and assistants</li>
                    </ul>
                </div>
            </div>

            <!-- Age Groups Info -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Age Groups</h6>
                </div>
                <div class="card-body small">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">8U:</dt>
                        <dd class="col-sm-6">Ages 7-8</dd>

                        <dt class="col-sm-6">10U:</dt>
                        <dd class="col-sm-6">Ages 9-10</dd>

                        <dt class="col-sm-6">12U:</dt>
                        <dd class="col-sm-6">Ages 11-12</dd>

                        <dt class="col-sm-6">14U:</dt>
                        <dd class="col-sm-6">Ages 13-14</dd>

                        <dt class="col-sm-6">16U:</dt>
                        <dd class="col-sm-6">Ages 15-16</dd>

                        <dt class="col-sm-6">18U:</dt>
                        <dd class="col-sm-6">Ages 17-18</dd>
                    </dl>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Tips</h6>
                </div>
                <div class="card-body small">
                    <ul class="ps-3 mb-0">
                        <li class="mb-2">Create a league first before adding teams</li>
                        <li class="mb-2">Choose an age group that matches your players</li>
                        <li class="mb-2">Set roster size to match league requirements</li>
                        <li>You can edit team details after creation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

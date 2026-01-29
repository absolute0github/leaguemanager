<?php
// Get data from controller
$player = $player ?? [];
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2>Edit Player</h2>
            <p class="text-muted"><?php echo htmlspecialchars($player['first_name'] ?? ''); ?> <?php echo htmlspecialchars($player['last_name'] ?? ''); ?></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="/admin/players/update" class="admin-form">
                <!-- Hidden Fields -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <input type="hidden" name="player_id" value="<?php echo htmlspecialchars($player['id'] ?? ''); ?>">

                <!-- Personal Information Section -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Personal Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       value="<?php echo htmlspecialchars($player['first_name'] ?? ''); ?>"
                                       required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       value="<?php echo htmlspecialchars($player['last_name'] ?? ''); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?php echo htmlspecialchars($player['email'] ?? ''); ?>"
                                       readonly>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?php echo htmlspecialchars($player['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="birthdate" class="form-label">Birthdate</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate"
                                       value="<?php echo htmlspecialchars($player['birthdate'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="age_group" class="form-label">Age Group</label>
                                <select class="form-select" id="age_group" name="age_group">
                                    <option value="">-- Select Age Group --</option>
                                    <option value="8U" <?php echo ($player['age_group'] ?? '') === '8U' ? 'selected' : ''; ?>>8U</option>
                                    <option value="10U" <?php echo ($player['age_group'] ?? '') === '10U' ? 'selected' : ''; ?>>10U</option>
                                    <option value="12U" <?php echo ($player['age_group'] ?? '') === '12U' ? 'selected' : ''; ?>>12U</option>
                                    <option value="14U" <?php echo ($player['age_group'] ?? '') === '14U' ? 'selected' : ''; ?>>14U</option>
                                    <option value="16U" <?php echo ($player['age_group'] ?? '') === '16U' ? 'selected' : ''; ?>>16U</option>
                                    <option value="18U" <?php echo ($player['age_group'] ?? '') === '18U' ? 'selected' : ''; ?>>18U</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Address</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="street_address" class="form-label">Street Address</label>
                            <input type="text" class="form-control" id="street_address" name="street_address"
                                   value="<?php echo htmlspecialchars($player['street_address'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="street_address_line2" class="form-label">Apt/Suite (Optional)</label>
                            <input type="text" class="form-control" id="street_address_line2" name="street_address_line2"
                                   value="<?php echo htmlspecialchars($player['street_address_line2'] ?? ''); ?>">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?php echo htmlspecialchars($player['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       placeholder="CA"
                                       maxlength="2"
                                       value="<?php echo htmlspecialchars($player['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="zip_code" class="form-label">ZIP Code</label>
                                <input type="text" class="form-control" id="zip_code" name="zip_code"
                                       value="<?php echo htmlspecialchars($player['zip_code'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Baseball Information Section -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Baseball Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="primary_position" class="form-label">Primary Position</label>
                                <select class="form-select" id="primary_position" name="primary_position">
                                    <option value="">-- Select Position --</option>
                                    <option value="Pitcher" <?php echo ($player['primary_position'] ?? '') === 'Pitcher' ? 'selected' : ''; ?>>Pitcher</option>
                                    <option value="Catcher" <?php echo ($player['primary_position'] ?? '') === 'Catcher' ? 'selected' : ''; ?>>Catcher</option>
                                    <option value="First Base" <?php echo ($player['primary_position'] ?? '') === 'First Base' ? 'selected' : ''; ?>>First Base</option>
                                    <option value="Second Base" <?php echo ($player['primary_position'] ?? '') === 'Second Base' ? 'selected' : ''; ?>>Second Base</option>
                                    <option value="Third Base" <?php echo ($player['primary_position'] ?? '') === 'Third Base' ? 'selected' : ''; ?>>Third Base</option>
                                    <option value="Shortstop" <?php echo ($player['primary_position'] ?? '') === 'Shortstop' ? 'selected' : ''; ?>>Shortstop</option>
                                    <option value="Left Field" <?php echo ($player['primary_position'] ?? '') === 'Left Field' ? 'selected' : ''; ?>>Left Field</option>
                                    <option value="Center Field" <?php echo ($player['primary_position'] ?? '') === 'Center Field' ? 'selected' : ''; ?>>Center Field</option>
                                    <option value="Right Field" <?php echo ($player['primary_position'] ?? '') === 'Right Field' ? 'selected' : ''; ?>>Right Field</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="secondary_position" class="form-label">Secondary Position</label>
                                <select class="form-select" id="secondary_position" name="secondary_position">
                                    <option value="">-- Select Position --</option>
                                    <option value="Pitcher" <?php echo ($player['secondary_position'] ?? '') === 'Pitcher' ? 'selected' : ''; ?>>Pitcher</option>
                                    <option value="Catcher" <?php echo ($player['secondary_position'] ?? '') === 'Catcher' ? 'selected' : ''; ?>>Catcher</option>
                                    <option value="First Base" <?php echo ($player['secondary_position'] ?? '') === 'First Base' ? 'selected' : ''; ?>>First Base</option>
                                    <option value="Second Base" <?php echo ($player['secondary_position'] ?? '') === 'Second Base' ? 'selected' : ''; ?>>Second Base</option>
                                    <option value="Third Base" <?php echo ($player['secondary_position'] ?? '') === 'Third Base' ? 'selected' : ''; ?>>Third Base</option>
                                    <option value="Shortstop" <?php echo ($player['secondary_position'] ?? '') === 'Shortstop' ? 'selected' : ''; ?>>Shortstop</option>
                                    <option value="Left Field" <?php echo ($player['secondary_position'] ?? '') === 'Left Field' ? 'selected' : ''; ?>>Left Field</option>
                                    <option value="Center Field" <?php echo ($player['secondary_position'] ?? '') === 'Center Field' ? 'selected' : ''; ?>>Center Field</option>
                                    <option value="Right Field" <?php echo ($player['secondary_position'] ?? '') === 'Right Field' ? 'selected' : ''; ?>>Right Field</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shirt_size" class="form-label">Shirt Size</label>
                                <select class="form-select" id="shirt_size" name="shirt_size">
                                    <option value="">-- Select Size --</option>
                                    <option value="XS" <?php echo ($player['shirt_size'] ?? '') === 'XS' ? 'selected' : ''; ?>>Extra Small</option>
                                    <option value="S" <?php echo ($player['shirt_size'] ?? '') === 'S' ? 'selected' : ''; ?>>Small</option>
                                    <option value="M" <?php echo ($player['shirt_size'] ?? '') === 'M' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="L" <?php echo ($player['shirt_size'] ?? '') === 'L' ? 'selected' : ''; ?>>Large</option>
                                    <option value="XL" <?php echo ($player['shirt_size'] ?? '') === 'XL' ? 'selected' : ''; ?>>Extra Large</option>
                                    <option value="XXL" <?php echo ($player['shirt_size'] ?? '') === 'XXL' ? 'selected' : ''; ?>>2X Large</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- School Information Section -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">School Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="school_name" class="form-label">School Name</label>
                            <input type="text" class="form-control" id="school_name" name="school_name"
                                   value="<?php echo htmlspecialchars($player['school_name'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="grade_level" class="form-label">Grade Level</label>
                            <select class="form-select" id="grade_level" name="grade_level">
                                <option value="">-- Select Grade --</option>
                                <option value="3rd" <?php echo ($player['grade_level'] ?? '') === '3rd' ? 'selected' : ''; ?>>3rd</option>
                                <option value="4th" <?php echo ($player['grade_level'] ?? '') === '4th' ? 'selected' : ''; ?>>4th</option>
                                <option value="5th" <?php echo ($player['grade_level'] ?? '') === '5th' ? 'selected' : ''; ?>>5th</option>
                                <option value="6th" <?php echo ($player['grade_level'] ?? '') === '6th' ? 'selected' : ''; ?>>6th</option>
                                <option value="7th" <?php echo ($player['grade_level'] ?? '') === '7th' ? 'selected' : ''; ?>>7th</option>
                                <option value="8th" <?php echo ($player['grade_level'] ?? '') === '8th' ? 'selected' : ''; ?>>8th</option>
                                <option value="9th" <?php echo ($player['grade_level'] ?? '') === '9th' ? 'selected' : ''; ?>>9th</option>
                                <option value="10th" <?php echo ($player['grade_level'] ?? '') === '10th' ? 'selected' : ''; ?>>10th</option>
                                <option value="11th" <?php echo ($player['grade_level'] ?? '') === '11th' ? 'selected' : ''; ?>>11th</option>
                                <option value="12th" <?php echo ($player['grade_level'] ?? '') === '12th' ? 'selected' : ''; ?>>12th</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Registration Status Section -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Registration Status</h6>
                    </div>
                    <div class="card-body">
                        <label for="registration_status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="registration_status" name="registration_status" required>
                            <option value="">-- Select Status --</option>
                            <option value="tryout" <?php echo ($player['registration_status'] ?? '') === 'tryout' ? 'selected' : ''; ?>>Tryout</option>
                            <option value="committed" <?php echo ($player['registration_status'] ?? '') === 'committed' ? 'selected' : ''; ?>>Committed</option>
                            <option value="active" <?php echo ($player['registration_status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo ($player['registration_status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="/admin/players/view?id=<?php echo $player['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Player Summary -->
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Current Information</h6>
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

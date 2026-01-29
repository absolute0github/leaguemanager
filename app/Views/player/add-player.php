<?php
// Get data from controller
$csrfToken = $csrfToken ?? '';
$user = $user ?? [];
$errors = $errors ?? [];
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/player/players">My Players</a></li>
                    <li class="breadcrumb-item active">Add Player</li>
                </ol>
            </nav>
            <h2>Add a Player</h2>
            <p class="text-muted">Add a new player to your account</p>
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
                    <form method="POST" action="/player/add">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                       placeholder="Enter first name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name"
                                       placeholder="Enter last name" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="player@example.com">
                                <small class="text-muted">Optional - player's email address</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       placeholder="(555) 123-4567">
                                <small class="text-muted">Optional - player's phone number</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="birthdate" class="form-label">Birthdate</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate">
                                <small class="text-muted">Used to calculate age for age groups</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="age_group" class="form-label">Age Group</label>
                                <select class="form-select" id="age_group" name="age_group">
                                    <option value="">-- Select Age Group --</option>
                                    <option value="6U">6U (6 and Under)</option>
                                    <option value="8U">8U (8 and Under)</option>
                                    <option value="9U">9U (9 and Under)</option>
                                    <option value="10U">10U (10 and Under)</option>
                                    <option value="11U">11U (11 and Under)</option>
                                    <option value="12U">12U (12 and Under)</option>
                                    <option value="13U">13U (13 and Under)</option>
                                    <option value="14U">14U (14 and Under)</option>
                                    <option value="15U">15U (15 and Under)</option>
                                    <option value="16U">16U (16 and Under)</option>
                                    <option value="17U">17U (17 and Under)</option>
                                    <option value="18U">18U (18 and Under)</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                Add Player
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
                    <h6 class="mb-0">Tips</h6>
                </div>
                <div class="card-body small">
                    <ul class="ps-3 mb-0">
                        <li class="mb-2">First and last name are required</li>
                        <li class="mb-2">Enter the player's birthdate to help determine age group eligibility</li>
                        <li class="mb-2">You can add multiple players to your account</li>
                        <li>Player information can be edited later</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

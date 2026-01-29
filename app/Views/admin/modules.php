<?php
$modules = $modules ?? [];
$csrfToken = $csrfToken ?? '';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><i class="fas fa-puzzle-piece me-2"></i>Module Management</h2>
            <p class="text-muted mb-0">Install and manage system modules</p>
        </div>
    </div>

    <?php if (empty($modules)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No modules found. Place modules in the <code>app/Modules/</code> directory.
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i>Creating a Module</h5>
            </div>
            <div class="card-body">
                <p>To create a module, create a folder in <code>app/Modules/</code> with the following structure:</p>
                <pre class="bg-light p-3 rounded"><code>app/Modules/my-module/
├── module.json          # Module metadata
├── routes.php           # Module routes (optional)
├── Controllers/         # Module controllers
├── Models/              # Module models
├── Views/               # Module views
└── migrations/          # SQL migrations (optional)</code></pre>

                <p class="mt-3"><strong>Example module.json:</strong></p>
                <pre class="bg-light p-3 rounded"><code>{
    "name": "My Module",
    "version": "1.0.0",
    "description": "Description of my module",
    "author": "Your Name",
    "hooks": {
        "dashboard.admin": "MyController@dashboardWidget"
    }
}</code></pre>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($modules as $directory => $module): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card h-100 <?php echo $module['enabled'] ? 'border-success' : ''; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <?php if ($module['enabled']): ?>
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                <?php else: ?>
                                    <i class="fas fa-circle text-muted me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($module['name']); ?>
                            </h5>
                            <span class="badge bg-secondary">v<?php echo htmlspecialchars($module['version']); ?></span>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">
                                <?php echo htmlspecialchars($module['description'] ?: 'No description available.'); ?>
                            </p>

                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>
                                    Author: <?php echo htmlspecialchars($module['author']); ?>
                                </small>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-folder me-1"></i>
                                    Directory: <code><?php echo htmlspecialchars($directory); ?></code>
                                </small>
                            </div>

                            <?php if (!empty($module['config']['hooks'])): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1">
                                        <i class="fas fa-plug me-1"></i>Hooks:
                                    </small>
                                    <?php foreach ($module['config']['hooks'] as $hook => $handler): ?>
                                        <span class="badge bg-info me-1 mb-1"><?php echo htmlspecialchars($hook); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light">
                            <?php if ($module['enabled']): ?>
                                <form method="POST" action="/admin/modules/disable" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="module_name" value="<?php echo htmlspecialchars($directory); ?>">
                                    <button type="submit" class="btn btn-warning btn-sm"
                                            onclick="return confirm('Are you sure you want to disable this module?')">
                                        <i class="fas fa-power-off me-1"></i>Disable
                                    </button>
                                </form>
                                <span class="badge bg-success ms-2">Active</span>
                            <?php else: ?>
                                <form method="POST" action="/admin/modules/enable" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="module_name" value="<?php echo htmlspecialchars($directory); ?>">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-play me-1"></i>Enable
                                    </button>
                                </form>
                                <span class="badge bg-secondary ms-2">Inactive</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Available Hooks</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Authentication</h6>
                        <ul class="list-unstyled small">
                            <li><code>user.login</code></li>
                            <li><code>user.logout</code></li>
                            <li><code>user.registered</code></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Dashboard Widgets</h6>
                        <ul class="list-unstyled small">
                            <li><code>dashboard.superuser</code></li>
                            <li><code>dashboard.admin</code></li>
                            <li><code>dashboard.coach</code></li>
                            <li><code>dashboard.player</code></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6>Player/Team Events</h6>
                        <ul class="list-unstyled small">
                            <li><code>player.created</code></li>
                            <li><code>player.approved</code></li>
                            <li><code>team.player_added</code></li>
                            <li><code>tryout.registered</code></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

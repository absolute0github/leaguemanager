<?php

namespace App\Modules\tryouts\Controllers;

use App\Core\Controller;
use App\Modules\tryouts\Services\TryoutLocationService;
use App\Modules\tryouts\Services\TryoutService;

/**
 * Tryout Admin Controller
 * Handles admin CRUD for locations, tryouts, and CSV import
 */
class TryoutAdminController extends Controller
{
    private TryoutLocationService $locationService;
    private TryoutService $tryoutService;
    private string $modulePath;

    public function __construct()
    {
        parent::__construct();
        $this->locationService = new TryoutLocationService();
        $this->tryoutService = new TryoutService();
        $this->modulePath = dirname(__DIR__);
    }

    /**
     * Load view from module's Views directory and return as string
     */
    protected function moduleView(string $viewPath, array $data = []): string
    {
        $viewFile = $this->modulePath . '/Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: $viewFile");
        }

        extract($data);

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Require admin access for non-hook methods
     */
    private function requireAdminAccess(): void
    {
        if (!$this->session->get('is_admin')) {
            $this->addError('Access denied. Admin privileges required.');
            $this->redirect('/dashboard');
        }
    }

    /**
     * Dashboard widget hook
     */
    public function dashboardWidget(array $context): string
    {
        // Get upcoming tryouts (next 30 days)
        $upcomingTryouts = $this->tryoutService->getTryouts([
            'date_from' => date('Y-m-d'),
            'date_to' => date('Y-m-d', strtotime('+30 days'))
        ], 5);

        // Get pending payment count
        $pendingPayments = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM tryout_registrations WHERE payment_status = "pending"'
        );

        ob_start();
        ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check"></i> Tryouts Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h6>Upcoming Tryouts</h6>
                        <p class="h3 text-primary"><?= count($upcomingTryouts) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Pending Payments</h6>
                        <p class="h3 text-warning"><?= $pendingPayments['count'] ?? 0 ?></p>
                    </div>
                </div>
                <a href="/admin/tryouts" class="btn btn-primary btn-sm">Manage Tryouts</a>
                <a href="/admin/tryout-registrations" class="btn btn-outline-primary btn-sm">View Registrations</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Sidebar navigation hook
     */
    public function sidebarLink(array $context): string
    {
        $currentPath = $context['currentPath'] ?? '';
        $isActive = strpos($currentPath, '/admin/tryout') === 0;

        ob_start();
        ?>
        <!-- Tryouts Section -->
        <li class="nav-item">
            <a class="nav-link text-white-50 small text-uppercase fw-bold px-4 py-2" href="#tryoutsModuleMenu" data-bs-toggle="collapse">
                <i class="fas fa-clipboard-check me-2"></i> Tryouts
            </a>
        </li>
        <div class="collapse <?php echo $isActive ? 'show' : ''; ?>" id="tryoutsModuleMenu">
            <a href="/admin/tryouts" class="nav-link <?php echo $currentPath === '/admin/tryouts' ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-list me-2"></i> View Tryouts
            </a>
            <a href="/admin/tryout-locations" class="nav-link <?php echo strpos($currentPath, '/admin/tryout-locations') === 0 ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-map-marker-alt me-2"></i> Locations
            </a>
            <a href="/admin/tryout-registrations" class="nav-link <?php echo strpos($currentPath, '/admin/tryout-registrations') === 0 ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-user-check me-2"></i> Registrations
            </a>
            <a href="/admin/tryouts/import" class="nav-link <?php echo $currentPath === '/admin/tryouts/import' ? 'active bg-primary' : ''; ?> text-white-75 px-4 py-2 d-flex align-items-center">
                <i class="fas fa-upload me-2"></i> Import Tryouts
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    // ============================================
    // LOCATION MANAGEMENT
    // ============================================

    /**
     * List locations with pagination and filters
     */
    public function listLocations(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 25;
        $offset = ($page - 1) * $limit;

        // Build filters
        $filters = [];
        if (isset($_GET['active']) && $_GET['active'] !== '') {
            $filters['active'] = (int)$_GET['active'];
        }
        if (!empty($_GET['city'])) {
            $filters['city'] = $_GET['city'];
        }
        if (!empty($_GET['state'])) {
            $filters['state'] = $_GET['state'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $locations = $this->locationService->getLocations($filters, $limit, $offset);
        $totalCount = $this->locationService->getLocationCount($filters);
        $totalPages = ceil($totalCount / $limit);

        $content = $this->moduleView('admin/locations/index', [
            'locations' => $locations,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'filters' => $filters
        ]);

        echo $this->adminView('Tryout Locations', $content);
    }

    /**
     * Show create location form
     */
    public function createLocationForm(): void
    {
        $content = $this->moduleView('admin/locations/create', []);
        echo $this->adminView('Create Location', $content);
    }

    /**
     * Process create location
     */
    public function createLocation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-locations/create');
            exit;
        }

        $data = [
            'name' => $_POST['name'] ?? '',
            'street_address' => $_POST['street_address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'zip_code' => $_POST['zip_code'] ?? '',
            'map_link' => $_POST['map_link'] ?? null,
            'special_instructions' => $_POST['special_instructions'] ?? null,
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        $locationId = $this->locationService->createLocation($data, $this->session->get('user_id'));

        if ($locationId) {
            $this->addSuccess( 'Location created successfully.');
            header('Location: /admin/tryout-locations');
        } else {
            $this->addError( 'Failed to create location. Please check all required fields.');
            header('Location: /admin/tryout-locations/create');
        }
        exit;
    }

    /**
     * Show edit location form
     */
    public function editLocationForm(): void
    {
        $locationId = (int)($_GET['id'] ?? 0);
        $location = $this->locationService->getLocation($locationId);

        if (!$location) {
            $this->addError( 'Location not found.');
            header('Location: /admin/tryout-locations');
            exit;
        }

        $content = $this->moduleView('admin/locations/edit', [
            'location' => $location
        ]);

        echo $this->adminView('Edit Location', $content);
    }

    /**
     * Process update location
     */
    public function updateLocation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-locations');
            exit;
        }

        $locationId = (int)($_POST['id'] ?? 0);

        $data = [
            'name' => $_POST['name'] ?? '',
            'street_address' => $_POST['street_address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'state' => $_POST['state'] ?? '',
            'zip_code' => $_POST['zip_code'] ?? '',
            'map_link' => $_POST['map_link'] ?? null,
            'special_instructions' => $_POST['special_instructions'] ?? null,
            'active' => isset($_POST['active']) ? 1 : 0
        ];

        $success = $this->locationService->updateLocation($locationId, $data);

        if ($success) {
            $this->addSuccess( 'Location updated successfully.');
        } else {
            $this->addError( 'Failed to update location. Please check all required fields.');
        }

        header('Location: /admin/tryout-locations');
        exit;
    }

    /**
     * Delete location
     */
    public function deleteLocation(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-locations');
            exit;
        }

        $locationId = (int)($_POST['id'] ?? 0);
        $success = $this->locationService->deleteLocation($locationId);

        if ($success) {
            $this->addSuccess( 'Location deleted successfully.');
        } else {
            $this->addError( 'Cannot delete location. It may have associated tryouts.');
        }

        header('Location: /admin/tryout-locations');
        exit;
    }

    /**
     * Toggle location active status
     */
    public function toggleLocationActive(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-locations');
            exit;
        }

        $locationId = (int)($_POST['id'] ?? 0);
        $active = (int)($_POST['active'] ?? 0);

        $success = $this->locationService->toggleActive($locationId, (bool)$active);

        if ($success) {
            $this->addSuccess( 'Location status updated successfully.');
        } else {
            $this->addError( 'Failed to update location status.');
        }

        header('Location: /admin/tryout-locations');
        exit;
    }

    // ============================================
    // TRYOUT MANAGEMENT
    // ============================================

    /**
     * List tryouts with pagination and filters
     */
    public function listTryouts(): void
    {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 25;
        $offset = ($page - 1) * $limit;

        // Build filters
        $filters = [];
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['age_group'])) {
            $filters['age_group'] = $_GET['age_group'];
        }
        if (!empty($_GET['location_id'])) {
            $filters['location_id'] = (int)$_GET['location_id'];
        }
        if (!empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $tryouts = $this->tryoutService->getTryouts($filters, $limit, $offset);
        $totalCount = $this->tryoutService->getTryoutCount($filters);
        $totalPages = ceil($totalCount / $limit);

        // Get locations for filter dropdown
        $locations = $this->locationService->getActiveLocations();

        $content = $this->moduleView('admin/tryouts/index', [
            'tryouts' => $tryouts,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'filters' => $filters,
            'locations' => $locations
        ]);

        echo $this->adminView('Manage Tryouts', $content);
    }

    /**
     * Show create tryout form
     */
    public function createTryoutForm(): void
    {
        $locations = $this->locationService->getActiveLocations();

        $content = $this->moduleView('admin/tryouts/create', [
            'locations' => $locations
        ]);

        echo $this->adminView('Create Tryout', $content);
    }

    /**
     * Process create tryout
     */
    public function createTryout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryouts/create');
            exit;
        }

        $data = [
            'location_id' => (int)($_POST['location_id'] ?? 0),
            'age_group' => $_POST['age_group'] ?? '',
            'tryout_date' => $_POST['tryout_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'cost' => $_POST['cost'] ?? 0,
            'max_participants' => !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null,
            'status' => $_POST['status'] ?? 'scheduled'
        ];

        $tryoutId = $this->tryoutService->createTryout($data, $this->session->get('user_id'));

        if ($tryoutId) {
            $this->addSuccess( 'Tryout created successfully.');
            header('Location: /admin/tryouts');
        } else {
            $this->addError( 'Failed to create tryout. Please check all required fields.');
            header('Location: /admin/tryouts/create');
        }
        exit;
    }

    /**
     * Show edit tryout form
     */
    public function editTryoutForm(): void
    {
        $tryoutId = (int)($_GET['id'] ?? 0);
        $tryout = $this->tryoutService->getTryout($tryoutId);

        if (!$tryout) {
            $this->addError( 'Tryout not found.');
            header('Location: /admin/tryouts');
            exit;
        }

        $locations = $this->locationService->getActiveLocations();

        $content = $this->moduleView('admin/tryouts/edit', [
            'tryout' => $tryout,
            'locations' => $locations
        ]);

        echo $this->adminView('Edit Tryout', $content);
    }

    /**
     * Process update tryout
     */
    public function updateTryout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryouts');
            exit;
        }

        $tryoutId = (int)($_POST['id'] ?? 0);

        $data = [
            'location_id' => (int)($_POST['location_id'] ?? 0),
            'age_group' => $_POST['age_group'] ?? '',
            'tryout_date' => $_POST['tryout_date'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? '',
            'cost' => $_POST['cost'] ?? 0,
            'max_participants' => !empty($_POST['max_participants']) ? (int)$_POST['max_participants'] : null,
            'status' => $_POST['status'] ?? 'scheduled'
        ];

        $success = $this->tryoutService->updateTryout($tryoutId, $data);

        if ($success) {
            $this->addSuccess( 'Tryout updated successfully.');
        } else {
            $this->addError( 'Failed to update tryout. Cannot reduce capacity below current registrations.');
        }

        header('Location: /admin/tryouts');
        exit;
    }

    /**
     * Delete tryout
     */
    public function deleteTryout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryouts');
            exit;
        }

        $tryoutId = (int)($_POST['id'] ?? 0);
        $success = $this->tryoutService->deleteTryout($tryoutId);

        if ($success) {
            $this->addSuccess( 'Tryout deleted successfully.');
        } else {
            $this->addError( 'Failed to delete tryout.');
        }

        header('Location: /admin/tryouts');
        exit;
    }

    /**
     * Update tryout status
     */
    public function updateTryoutStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryouts');
            exit;
        }

        $tryoutId = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';

        $success = $this->tryoutService->updateStatus($tryoutId, $status);

        if ($success) {
            $this->addSuccess( 'Tryout status updated successfully.');
        } else {
            $this->addError( 'Failed to update tryout status.');
        }

        header('Location: /admin/tryouts');
        exit;
    }

    // ============================================
    // CSV IMPORT
    // ============================================

    /**
     * Show import form
     */
    public function importForm(): void
    {
        $content = $this->moduleView('admin/tryouts/import', []);
        echo $this->adminView('Import Tryouts', $content);
    }

    /**
     * Process CSV import
     */
    public function processImport(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryouts/import');
            exit;
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->addError( 'Please select a valid CSV file.');
            header('Location: /admin/tryouts/import');
            exit;
        }

        $filePath = $_FILES['csv_file']['tmp_name'];
        $result = $this->tryoutService->importFromCSV($filePath, $this->session->get('user_id'));

        if (!empty($result['errors'])) {
            $this->addError( 'Import completed with errors: ' . implode(', ', $result['errors']));
        } else {
            $this->addSuccess( "Import successful! Imported: {$result['imported']}, Skipped: {$result['skipped']}");
        }

        header('Location: /admin/tryouts');
        exit;
    }

    /**
     * adminView wrapper - wraps content in admin layout
     */
    private function adminView(string $title, string $content): string
    {
        $user = [
            'id' => $this->session->get('user_id'),
            'username' => $this->session->get('username'),
            'role' => $this->session->get('role')
        ];
        $pageTitle = $title;

        ob_start();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - IVL Baseball League</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding-left: 250px; background-color: #f8f9fa; }
        .main-content { padding: 20px; padding-top: 80px; }
        @media (max-width: 768px) {
            body { padding-left: 0; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>
        <?php
        require __DIR__ . '/../../../Views/partials/header.php';
        require __DIR__ . '/../../../Views/partials/admin-sidebar.php';
        ?>
        <div class="main-content">
            <div class="container-fluid">
                <h2 class="mb-4"><?= htmlspecialchars($title) ?></h2>

                <?php foreach ($this->getSuccess() as $msg): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>

                <?php foreach ($this->getErrors() as $msg): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($msg) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach; ?>

                <?= $content ?>
            </div>
        </div>
        <?php require __DIR__ . '/../../../Views/partials/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}

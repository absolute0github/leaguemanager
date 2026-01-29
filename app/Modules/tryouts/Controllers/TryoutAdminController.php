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

    public function __construct()
    {
        parent::__construct();
        $this->locationService = new TryoutLocationService();
        $this->tryoutService = new TryoutService();
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
        ob_start();
        ?>
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#tryoutsMenu">
                <i class="bi bi-calendar-check"></i> Tryouts
            </a>
            <ul id="tryoutsMenu" class="collapse nav flex-column ms-3">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/tryouts">
                        <i class="bi bi-list"></i> View Tryouts
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/tryout-locations">
                        <i class="bi bi-geo-alt"></i> Locations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/tryout-registrations">
                        <i class="bi bi-person-check"></i> Registrations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/tryouts/import">
                        <i class="bi bi-upload"></i> Import Tryouts
                    </a>
                </li>
            </ul>
        </li>
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

        $content = $this->view('admin/locations/index', [
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
        $content = $this->view('admin/locations/create', []);
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

        $content = $this->view('admin/locations/edit', [
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

        $content = $this->view('admin/tryouts/index', [
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

        $content = $this->view('admin/tryouts/create', [
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

        $content = $this->view('admin/tryouts/edit', [
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
        $content = $this->view('admin/tryouts/import', []);
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
        ob_start();
        require __DIR__ . '/../../../Views/layouts/header.php';
        require __DIR__ . '/../../../Views/layouts/admin-sidebar.php';
        ?>
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
        <?php
        require __DIR__ . '/../../../Views/layouts/footer.php';
        return ob_get_clean();
    }
}

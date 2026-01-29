<?php

namespace App\Modules\tryouts\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Modules\tryouts\Services\TryoutService;
use App\Modules\tryouts\Services\TryoutRegistrationService;

/**
 * Tryout Player Controller
 * Handles player-facing tryout registration and management
 */
class TryoutPlayerController extends Controller
{
    private TryoutService $tryoutService;
    private TryoutRegistrationService $registrationService;
    private Database $db;

    public function __construct()
    {
        parent::__construct();
        $this->tryoutService = new TryoutService();
        $this->registrationService = new TryoutRegistrationService();
        $this->db = Database::getInstance();
    }

    /**
     * Dashboard widget hook for players
     */
    public function dashboardWidget(array $context): string
    {
        $userId = $this->session->get('user_id');

        // Get user's players
        $players = $this->db->fetchAll(
            'SELECT id FROM players WHERE user_id = ?',
            [$userId]
        );

        if (empty($players)) {
            return '';
        }

        $playerIds = array_column($players, 'id');
        $placeholders = implode(',', array_fill(0, count($playerIds), '?'));

        // Get upcoming tryout registrations
        $registrations = $this->db->fetchAll(
            "SELECT tr.*, t.age_group, t.tryout_date, t.start_time,
                    tl.name as location_name, p.first_name, p.last_name
             FROM tryout_registrations tr
             JOIN tryouts t ON tr.tryout_id = t.id
             JOIN tryout_locations tl ON t.location_id = tl.id
             JOIN players p ON tr.player_id = p.id
             WHERE tr.player_id IN ($placeholders)
             AND t.tryout_date >= CURDATE()
             AND tr.attendance_status = 'registered'
             ORDER BY t.tryout_date ASC, t.start_time ASC
             LIMIT 5",
            $playerIds
        );

        ob_start();
        ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-calendar-check"></i> Upcoming Tryouts
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($registrations)): ?>
                    <p class="text-muted mb-3">No upcoming tryout registrations.</p>
                    <a href="/tryouts" class="btn btn-primary btn-sm">Browse Tryouts</a>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($registrations as $reg): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong><?= htmlspecialchars($reg['first_name'] . ' ' . $reg['last_name']) ?></strong>
                                        - <?= htmlspecialchars($reg['age_group']) ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= date('M j, Y', strtotime($reg['tryout_date'])) ?> at
                                            <?= date('g:i A', strtotime($reg['start_time'])) ?>
                                            <br><?= htmlspecialchars($reg['location_name']) ?>
                                        </small>
                                    </div>
                                    <?php if ($reg['waitlist_position'] !== null): ?>
                                        <span class="badge bg-warning">Waitlist #<?= $reg['waitlist_position'] ?></span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Confirmed</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <a href="/tryouts/my-registrations" class="btn btn-outline-primary btn-sm mt-3">View All</a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Sidebar navigation hook for players
     */
    public function sidebarLink(array $context): string
    {
        ob_start();
        ?>
        <li class="nav-item">
            <a class="nav-link" href="/tryouts">
                <i class="bi bi-calendar-check"></i> Tryouts
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/tryouts/my-registrations">
                <i class="bi bi-person-check"></i> My Registrations
            </a>
        </li>
        <?php
        return ob_get_clean();
    }

    /**
     * Browse open tryouts
     */
    public function browse(): void
    {
        // Build filters
        $filters = ['status' => 'open'];

        if (!empty($_GET['age_group'])) {
            $filters['age_group'] = $_GET['age_group'];
        }
        if (!empty($_GET['date_from'])) {
            $filters['date_from'] = $_GET['date_from'];
        } else {
            // Default: show future tryouts only
            $filters['date_from'] = date('Y-m-d');
        }
        if (!empty($_GET['date_to'])) {
            $filters['date_to'] = $_GET['date_to'];
        }
        if (!empty($_GET['location_id'])) {
            $filters['location_id'] = (int)$_GET['location_id'];
        }

        $tryouts = $this->tryoutService->getTryouts($filters, 50, 0);

        // Get locations for filter
        $locations = $this->db->fetchAll(
            'SELECT id, name, city, state FROM tryout_locations WHERE active = 1 ORDER BY name ASC'
        );

        $content = $this->view('player/browse', [
            'tryouts' => $tryouts,
            'filters' => $filters,
            'locations' => $locations
        ]);

        echo $this->playerView('Browse Tryouts', $content);
    }

    /**
     * View single tryout with details
     */
    public function viewTryout(): void
    {
        $tryoutId = (int)($_GET['id'] ?? 0);
        $tryout = $this->tryoutService->getTryout($tryoutId);

        if (!$tryout) {
            $this->addError( 'Tryout not found.');
            header('Location: /tryouts');
            exit;
        }

        // Check if user has already registered for this tryout
        $userId = $this->session->get('user_id');
        $userPlayers = $this->db->fetchAll(
            'SELECT id FROM players WHERE user_id = ?',
            [$userId]
        );

        $existingRegistrations = [];
        if (!empty($userPlayers)) {
            $playerIds = array_column($userPlayers, 'id');
            $placeholders = implode(',', array_fill(0, count($playerIds), '?'));

            $existingRegistrations = $this->db->fetchAll(
                "SELECT tr.*, p.first_name, p.last_name
                 FROM tryout_registrations tr
                 JOIN players p ON tr.player_id = p.id
                 WHERE tr.tryout_id = ?
                 AND tr.player_id IN ($placeholders)
                 AND tr.attendance_status != 'cancelled'",
                array_merge([$tryoutId], $playerIds)
            );
        }

        $content = $this->view('player/view', [
            'tryout' => $tryout,
            'existingRegistrations' => $existingRegistrations
        ]);

        echo $this->playerView('Tryout Details', $content);
    }

    /**
     * Show registration form
     */
    public function registerForm(): void
    {
        $tryoutId = (int)($_GET['id'] ?? 0);
        $tryout = $this->tryoutService->getTryout($tryoutId);

        if (!$tryout) {
            $this->addError( 'Tryout not found.');
            header('Location: /tryouts');
            exit;
        }

        // Check if available
        if (!$this->tryoutService->isAvailable($tryoutId)) {
            $this->addError( 'This tryout is not available for registration.');
            header('Location: /tryouts/view?id=' . $tryoutId);
            exit;
        }

        // Get user's players
        $userId = $this->session->get('user_id');
        $players = $this->db->fetchAll(
            'SELECT * FROM players WHERE user_id = ? ORDER BY first_name, last_name',
            [$userId]
        );

        if (empty($players)) {
            $this->addError( 'You must add a player before registering for tryouts. <a href="/player/add">Add player</a>');
            header('Location: /tryouts/view?id=' . $tryoutId);
            exit;
        }

        $content = $this->view('player/register', [
            'tryout' => $tryout,
            'players' => $players
        ]);

        echo $this->playerView('Register for Tryout', $content);
    }

    /**
     * Process registration
     */
    public function processRegistration(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tryouts');
            exit;
        }

        $tryoutId = (int)($_POST['tryout_id'] ?? 0);
        $playerId = (int)($_POST['player_id'] ?? 0);

        // Verify player belongs to user
        $userId = $this->session->get('user_id');
        $player = $this->db->fetchOne(
            'SELECT * FROM players WHERE id = ? AND user_id = ?',
            [$playerId, $userId]
        );

        if (!$player) {
            $this->addError( 'Invalid player selection.');
            header('Location: /tryouts');
            exit;
        }

        // Verify waiver
        if (!isset($_POST['waiver_signed']) || $_POST['waiver_signed'] != '1') {
            $this->addError( 'You must accept the waiver to register.');
            header('Location: /tryouts/register?id=' . $tryoutId);
            exit;
        }

        // Prepare registration data
        $data = [
            'waiver_signed' => true,
            'payment_method' => $_POST['payment_method'] ?? null,
            'payment_transaction_id' => $_POST['payment_transaction_id'] ?? null
        ];

        // Process registration
        $result = $this->registrationService->register($tryoutId, $playerId, $data);

        if ($result['success']) {
            $this->addSuccess( 'Registration successful!');
            header('Location: /tryouts/confirmation?id=' . $result['registration_id']);
        } else {
            $this->addError( 'Registration failed: ' . implode(', ', $result['errors']));
            header('Location: /tryouts/register?id=' . $tryoutId);
        }
        exit;
    }

    /**
     * Show registration confirmation
     */
    public function confirmation(): void
    {
        $registrationId = (int)($_GET['id'] ?? 0);
        $registration = $this->registrationService->getRegistration($registrationId);

        if (!$registration) {
            $this->addError( 'Registration not found.');
            header('Location: /tryouts');
            exit;
        }

        // Verify ownership
        $userId = $this->session->get('user_id');
        $player = $this->db->fetchOne(
            'SELECT user_id FROM players WHERE id = ?',
            [$registration['player_id']]
        );

        if (!$player || $player['user_id'] != $userId) {
            $this->addError( 'Access denied.');
            header('Location: /tryouts');
            exit;
        }

        $content = $this->view('player/confirmation', [
            'registration' => $registration
        ]);

        echo $this->playerView('Registration Confirmed', $content);
    }

    /**
     * View user's registrations
     */
    public function myRegistrations(): void
    {
        $userId = $this->session->get('user_id');
        $tab = $_GET['tab'] ?? 'upcoming';

        // Get user's players
        $players = $this->db->fetchAll(
            'SELECT id FROM players WHERE user_id = ?',
            [$userId]
        );

        if (empty($players)) {
            $content = '<p class="text-muted">You have no players added. <a href="/player/add">Add a player</a> to register for tryouts.</p>';
            echo $this->playerView('My Registrations', $content);
            return;
        }

        $playerIds = array_column($players, 'id');
        $placeholders = implode(',', array_fill(0, count($playerIds), '?'));

        // Build query based on tab
        $query = "SELECT tr.*, t.age_group, t.tryout_date, t.start_time, t.end_time, t.cost, t.status as tryout_status,
                         tl.name as location_name, tl.city, tl.state,
                         p.first_name, p.last_name
                  FROM tryout_registrations tr
                  JOIN tryouts t ON tr.tryout_id = t.id
                  LEFT JOIN tryout_locations tl ON t.location_id = tl.id
                  JOIN players p ON tr.player_id = p.id
                  WHERE tr.player_id IN ($placeholders)";

        $params = $playerIds;

        switch ($tab) {
            case 'past':
                $query .= " AND t.tryout_date < CURDATE() AND tr.attendance_status != 'cancelled'";
                break;
            case 'cancelled':
                $query .= " AND tr.attendance_status = 'cancelled'";
                break;
            case 'waitlist':
                $query .= " AND tr.waitlist_position IS NOT NULL AND tr.attendance_status != 'cancelled'";
                break;
            case 'upcoming':
            default:
                $query .= " AND t.tryout_date >= CURDATE() AND tr.attendance_status != 'cancelled' AND tr.waitlist_position IS NULL";
                break;
        }

        $query .= " ORDER BY t.tryout_date DESC, t.start_time DESC";

        $registrations = $this->db->fetchAll($query, $params);

        $content = $this->view('player/my-registrations', [
            'registrations' => $registrations,
            'tab' => $tab
        ]);

        echo $this->playerView('My Registrations', $content);
    }

    /**
     * Cancel player's own registration
     */
    public function cancelMyRegistration(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /tryouts/my-registrations');
            exit;
        }

        $registrationId = (int)($_POST['id'] ?? 0);
        $registration = $this->registrationService->getRegistration($registrationId);

        if (!$registration) {
            $this->addError( 'Registration not found.');
            header('Location: /tryouts/my-registrations');
            exit;
        }

        // Verify ownership
        $userId = $this->session->get('user_id');
        $player = $this->db->fetchOne(
            'SELECT user_id FROM players WHERE id = ?',
            [$registration['player_id']]
        );

        if (!$player || $player['user_id'] != $userId) {
            $this->addError( 'Access denied.');
            header('Location: /tryouts/my-registrations');
            exit;
        }

        // Cancel registration
        $success = $this->registrationService->cancelRegistration($registrationId, 'Cancelled by player');

        if ($success) {
            $this->addSuccess( 'Registration cancelled successfully. You will receive a confirmation email.');
        } else {
            $this->addError( 'Failed to cancel registration.');
        }

        header('Location: /tryouts/my-registrations');
        exit;
    }

    /**
     * playerView wrapper - wraps content in player layout
     */
    private function playerView(string $title, string $content): string
    {
        ob_start();
        require __DIR__ . '/../../../Views/layouts/header.php';
        require __DIR__ . '/../../../Views/layouts/player-sidebar.php';
        ?>
        <div class="container-fluid">
            <h2 class="mb-4"><?= htmlspecialchars($title) ?></h2>

            <?php foreach ($this->getSuccess() as $msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $msg ?>
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

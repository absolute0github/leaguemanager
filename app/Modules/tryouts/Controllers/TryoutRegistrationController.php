<?php

namespace App\Modules\tryouts\Controllers;

use App\Core\Controller;
use App\Modules\tryouts\Services\TryoutService;
use App\Modules\tryouts\Services\TryoutRegistrationService;

/**
 * Tryout Registration Controller
 * Handles admin management of tryout registrations
 */
class TryoutRegistrationController extends Controller
{
    private TryoutService $tryoutService;
    private TryoutRegistrationService $registrationService;
    private string $modulePath;

    public function __construct()
    {
        parent::__construct();
        $this->tryoutService = new TryoutService();
        $this->registrationService = new TryoutRegistrationService();
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
     * List registrations for a tryout or all registrations
     */
    public function listRegistrations(): void
    {
        $tryoutId = isset($_GET['tryout_id']) ? (int)$_GET['tryout_id'] : null;
        $tab = $_GET['tab'] ?? 'confirmed';

        if ($tryoutId) {
            $tryout = $this->tryoutService->getTryout($tryoutId);
            if (!$tryout) {
                $this->addError( 'Tryout not found.');
                header('Location: /admin/tryouts');
                exit;
            }

            // Get registrations with tab filter
            $filters = ['tryout_id' => $tryoutId];

            switch ($tab) {
                case 'waitlist':
                    $filters['waitlisted'] = true;
                    break;
                case 'cancelled':
                    $filters['attendance_status'] = 'cancelled';
                    break;
                case 'confirmed':
                default:
                    $filters['waitlisted'] = false;
                    $filters['attendance_status'] = 'registered';
                    break;
            }

            $registrations = $this->registrationService->getRegistrationsForTryout($tryoutId, $filters);

            $content = $this->moduleView('admin/registrations/index', [
                'tryout' => $tryout,
                'registrations' => $registrations,
                'tab' => $tab
            ]);

            echo $this->adminView('Tryout Registrations', $content);
        } else {
            // Show all registrations across all tryouts
            $content = '<p>Select a tryout to view registrations, or <a href="/admin/tryouts">view all tryouts</a>.</p>';
            echo $this->adminView('All Registrations', $content);
        }
    }

    /**
     * View single registration with full details
     */
    public function viewRegistration(): void
    {
        $registrationId = (int)($_GET['id'] ?? 0);
        $registration = $this->registrationService->getRegistration($registrationId);

        if (!$registration) {
            $this->addError( 'Registration not found.');
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $content = $this->moduleView('admin/registrations/view', [
            'registration' => $registration
        ]);

        echo $this->adminView('Registration Details', $content);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $registrationId = (int)($_POST['id'] ?? 0);
        $status = $_POST['payment_status'] ?? '';
        $transactionId = $_POST['payment_transaction_id'] ?? null;

        $success = $this->registrationService->updatePaymentStatus($registrationId, $status, $transactionId);

        if ($success) {
            $this->addSuccess( 'Payment status updated successfully.');
        } else {
            $this->addError( 'Failed to update payment status.');
        }

        // Redirect back to registration view
        header('Location: /admin/tryout-registrations/view?id=' . $registrationId);
        exit;
    }

    /**
     * Update attendance status
     */
    public function updateAttendanceStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $registrationId = (int)($_POST['id'] ?? 0);
        $status = $_POST['attendance_status'] ?? '';

        $success = $this->registrationService->updateAttendanceStatus($registrationId, $status);

        if ($success) {
            $this->addSuccess( 'Attendance status updated successfully.');
        } else {
            $this->addError( 'Failed to update attendance status.');
        }

        // Redirect back to registration view
        header('Location: /admin/tryout-registrations/view?id=' . $registrationId);
        exit;
    }

    /**
     * Add admin note
     */
    public function addNote(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $registrationId = (int)($_POST['id'] ?? 0);
        $note = trim($_POST['note'] ?? '');

        if (empty($note)) {
            $this->addError( 'Note cannot be empty.');
            header('Location: /admin/tryout-registrations/view?id=' . $registrationId);
            exit;
        }

        $success = $this->registrationService->addNote($registrationId, $note);

        if ($success) {
            $this->addSuccess( 'Note added successfully.');
        } else {
            $this->addError( 'Failed to add note.');
        }

        // Redirect back to registration view
        header('Location: /admin/tryout-registrations/view?id=' . $registrationId);
        exit;
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $registrationId = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? 'Cancelled by admin');

        $registration = $this->registrationService->getRegistration($registrationId);
        if (!$registration) {
            $this->addError( 'Registration not found.');
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $success = $this->registrationService->cancelRegistration($registrationId, $reason);

        if ($success) {
            $this->addSuccess( 'Registration cancelled successfully. Confirmation email sent.');

            // If this was a confirmed registration, check if we can auto-promote from waitlist
            if ($registration['waitlist_position'] === null) {
                // Get first person on waitlist
                $waitlist = $this->registrationService->getRegistrationsForTryout(
                    $registration['tryout_id'],
                    ['waitlisted' => true]
                );

                if (!empty($waitlist)) {
                    $firstWaitlist = $waitlist[0];
                    $this->addSuccess( 'There are ' . count($waitlist) . ' people on the waitlist. <a href="/admin/tryout-registrations/promote-waitlist?id=' . $firstWaitlist['id'] . '">Promote first person</a>?');
                }
            }

            // Redirect to tryout registrations list
            header('Location: /admin/tryout-registrations?tryout_id=' . $registration['tryout_id']);
        } else {
            $this->addError( 'Failed to cancel registration.');
            header('Location: /admin/tryout-registrations/view?id=' . $registrationId);
        }
        exit;
    }

    /**
     * Promote from waitlist
     */
    public function promoteFromWaitlist(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
            header('Location: /admin/tryout-registrations');
            exit;
        }

        $registrationId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
        $registration = $this->registrationService->getRegistration($registrationId);

        if (!$registration) {
            $this->addError( 'Registration not found.');
            header('Location: /admin/tryout-registrations');
            exit;
        }

        if ($registration['waitlist_position'] === null) {
            $this->addError( 'This registration is not on the waitlist.');
            header('Location: /admin/tryout-registrations/view?id=' . $registrationId);
            exit;
        }

        $success = $this->registrationService->promoteFromWaitlist($registrationId);

        if ($success) {
            $this->addSuccess( 'Registration promoted from waitlist successfully. Promotion email sent.');
        } else {
            $this->addError( 'Failed to promote from waitlist. Tryout may be full.');
        }

        // Redirect to tryout registrations list
        header('Location: /admin/tryout-registrations?tryout_id=' . $registration['tryout_id'] . '&tab=waitlist');
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
        </div>
        <?php require __DIR__ . '/../../../Views/partials/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
        <?php
        return ob_get_clean();
    }
}

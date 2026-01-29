<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\CoachService;
use App\Services\EmailService;

class CoachController extends Controller
{
    private CoachService $coachService;
    private EmailService $emailService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();

        // Verify user is a coach
        $user = $this->getUser();
        if ($user['role'] !== 'coach' && $user['role'] !== 'superuser' && $user['role'] !== 'admin') {
            $this->addError('Access denied. Coach privileges required.');
            $this->redirect('/dashboard');
        }

        $this->coachService = new CoachService();
        $this->emailService = new EmailService();
    }

    /**
     * Render a view within the coach layout
     */
    protected function coachView(string $viewPath, array $data = []): void
    {
        $viewFile = __DIR__ . '/../Views/' . str_replace('.', '/', $viewPath) . '.php';

        if (!file_exists($viewFile)) {
            die("View file not found: $viewFile");
        }

        extract($data);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $data['content'] = $content;
        $this->view('layouts.coach', $data);
    }

    /**
     * Coach Dashboard
     */
    public function dashboard(): void
    {
        $user = $this->getUser();
        $dashboardData = $this->coachService->getDashboardData($user['id']);

        $this->coachView('coach.dashboard', [
            'user' => $user,
            'coach' => $dashboardData['coach'],
            'teams' => $dashboardData['teams'],
            'teamStats' => $dashboardData['team_stats'],
            'totalPlayers' => $dashboardData['total_players'],
        ]);
    }

    /**
     * View Team Details
     */
    public function viewTeam(): void
    {
        $teamId = (int)$this->get('id');
        $user = $this->getUser();

        if (!$teamId) {
            // If no team specified, get coach's first team
            $teams = $this->coachService->getCoachTeams($user['id']);
            if (empty($teams)) {
                $this->addError('You are not assigned to any team.');
                $this->redirect('/coach/dashboard');
            }
            $teamId = $teams[0]['id'];
        }

        // Verify access
        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('You do not have access to this team.');
            $this->redirect('/coach/dashboard');
        }

        $team = $this->coachService->getTeam($teamId);
        $coaches = $this->coachService->getTeamCoaches($teamId);
        $stats = $this->coachService->getTeamStats($teamId);

        $this->coachView('coach.team', [
            'user' => $user,
            'team' => $team,
            'coaches' => $coaches,
            'stats' => $stats,
        ]);
    }

    /**
     * View Team Roster
     */
    public function viewRoster(): void
    {
        $teamId = (int)$this->get('id');
        $user = $this->getUser();

        if (!$teamId) {
            $teams = $this->coachService->getCoachTeams($user['id']);
            if (empty($teams)) {
                $this->addError('You are not assigned to any team.');
                $this->redirect('/coach/dashboard');
            }
            $teamId = $teams[0]['id'];
        }

        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('You do not have access to this team.');
            $this->redirect('/coach/dashboard');
        }

        $team = $this->coachService->getTeam($teamId);
        $roster = $this->coachService->getTeamRosterWithParents($teamId);
        $stats = $this->coachService->getTeamStats($teamId);

        $this->coachView('coach.roster', [
            'user' => $user,
            'team' => $team,
            'roster' => $roster,
            'stats' => $stats,
        ]);
    }

    /**
     * View Individual Player
     */
    public function viewPlayer(): void
    {
        $playerId = (int)$this->get('id');
        $teamId = (int)$this->get('team_id');
        $user = $this->getUser();

        if (!$playerId || !$teamId) {
            $this->addError('Invalid player or team.');
            $this->redirect('/coach/dashboard');
        }

        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('You do not have access to this player.');
            $this->redirect('/coach/dashboard');
        }

        $team = $this->coachService->getTeam($teamId);
        $player = $this->coachService->getPlayerWithParents($playerId);

        if (!$player) {
            $this->addError('Player not found.');
            $this->redirect('/coach/roster?id=' . $teamId);
        }

        $this->coachView('coach.player', [
            'user' => $user,
            'team' => $team,
            'player' => $player,
        ]);
    }

    /**
     * Show Message/Email Form
     */
    public function showMessageForm(): void
    {
        $teamId = (int)$this->get('id');
        $user = $this->getUser();

        if (!$teamId) {
            $teams = $this->coachService->getCoachTeams($user['id']);
            if (empty($teams)) {
                $this->addError('You are not assigned to any team.');
                $this->redirect('/coach/dashboard');
            }
            $teamId = $teams[0]['id'];
        }

        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('You do not have access to this team.');
            $this->redirect('/coach/dashboard');
        }

        $team = $this->coachService->getTeam($teamId);
        $parentEmails = $this->coachService->getTeamParentEmails($teamId);
        $playerEmails = $this->coachService->getTeamPlayerEmails($teamId);

        $this->coachView('coach.message', [
            'user' => $user,
            'team' => $team,
            'parentEmails' => $parentEmails,
            'playerEmails' => $playerEmails,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Send Team Message/Email
     */
    public function sendMessage(): void
    {
        $user = $this->getUser();

        if (!$this->validateCsrfToken($this->post('csrf_token'))) {
            $this->addError('Invalid security token. Please try again.');
            $this->redirect('/coach/dashboard');
        }

        $teamId = (int)$this->post('team_id');
        $recipients = $this->post('recipients', 'parents'); // parents, players, both
        $subject = trim($this->post('subject', ''));
        $message = trim($this->post('message', ''));

        // Validation
        if (!$teamId || !$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('Invalid team.');
            $this->redirect('/coach/dashboard');
        }

        if (empty($subject) || empty($message)) {
            $this->addError('Subject and message are required.');
            $this->redirect('/coach/message?id=' . $teamId);
        }

        $team = $this->coachService->getTeam($teamId);
        $emails = [];

        // Collect email addresses
        if ($recipients === 'parents' || $recipients === 'both') {
            $parentEmails = $this->coachService->getTeamParentEmails($teamId);
            foreach ($parentEmails as $parent) {
                $emails[$parent['email']] = $parent['full_name'];
            }
        }

        if ($recipients === 'players' || $recipients === 'both') {
            $playerEmails = $this->coachService->getTeamPlayerEmails($teamId);
            foreach ($playerEmails as $player) {
                $emails[$player['email']] = $player['first_name'] . ' ' . $player['last_name'];
            }
        }

        if (empty($emails)) {
            $this->addError('No email addresses found for the selected recipients.');
            $this->redirect('/coach/message?id=' . $teamId);
        }

        // Send emails
        $sentCount = 0;
        $failedCount = 0;

        foreach ($emails as $email => $name) {
            $success = $this->emailService->sendTeamMessage(
                $email,
                $name,
                $subject,
                $message,
                $team['name'],
                $user['username']
            );

            if ($success) {
                $sentCount++;
            } else {
                $failedCount++;
            }
        }

        if ($sentCount > 0) {
            $this->addSuccess("Message sent successfully to {$sentCount} recipient(s).");
        }

        if ($failedCount > 0) {
            $this->addError("Failed to send to {$failedCount} recipient(s).");
        }

        $this->redirect('/coach/message?id=' . $teamId);
    }

    /**
     * Update Player Jersey Number (AJAX)
     */
    public function updateJerseyNumber(): void
    {
        $user = $this->getUser();

        if (!$this->validateCsrfToken($this->post('csrf_token'))) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $teamId = (int)$this->post('team_id');
        $playerId = (int)$this->post('player_id');
        $jerseyNumber = $this->post('jersey_number');

        if ($jerseyNumber !== '' && $jerseyNumber !== null) {
            $jerseyNumber = (int)$jerseyNumber;
            if ($jerseyNumber < 0 || $jerseyNumber > 99) {
                $this->json(['success' => false, 'message' => 'Jersey number must be 0-99'], 400);
            }
        } else {
            $jerseyNumber = null;
        }

        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $success = $this->coachService->updateJerseyNumber($teamId, $playerId, $jerseyNumber);

        $this->json([
            'success' => $success,
            'message' => $success ? 'Jersey number updated' : 'Failed to update jersey number'
        ]);
    }

    /**
     * Update Player Roster Status (AJAX)
     */
    public function updatePlayerStatus(): void
    {
        $user = $this->getUser();

        if (!$this->validateCsrfToken($this->post('csrf_token'))) {
            $this->json(['success' => false, 'message' => 'Invalid security token'], 403);
        }

        $teamId = (int)$this->post('team_id');
        $playerId = (int)$this->post('player_id');
        $status = $this->post('status');

        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $success = $this->coachService->updatePlayerRosterStatus($teamId, $playerId, $status);

        $this->json([
            'success' => $success,
            'message' => $success ? 'Player status updated' : 'Failed to update status'
        ]);
    }

    /**
     * Contact Info Export (CSV)
     */
    public function exportContacts(): void
    {
        $teamId = (int)$this->get('id');
        $type = $this->get('type', 'all'); // all, parents, players
        $user = $this->getUser();

        if (!$teamId || !$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('Access denied.');
            $this->redirect('/coach/dashboard');
        }

        $team = $this->coachService->getTeam($teamId);
        $roster = $this->coachService->getTeamRosterWithParents($teamId);

        // Build CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9]/', '_', $team['name']) . '_contacts.csv"');

        $output = fopen('php://output', 'w');

        if ($type === 'parents' || $type === 'all') {
            fputcsv($output, ['Player Name', 'Parent/Guardian', 'Phone', 'Email', 'Relationship']);
            foreach ($roster as $player) {
                foreach ($player['parents'] as $parent) {
                    fputcsv($output, [
                        $player['first_name'] . ' ' . $player['last_name'],
                        $parent['full_name'],
                        $parent['phone'] ?? '',
                        $parent['email'] ?? '',
                        'Guardian ' . $parent['guardian_number']
                    ]);
                }
            }
        }

        if ($type === 'players' || $type === 'all') {
            if ($type === 'all') {
                fputcsv($output, []); // Empty row separator
            }
            fputcsv($output, ['Player Name', 'Phone', 'Email', 'Age Group', 'Position']);
            foreach ($roster as $player) {
                fputcsv($output, [
                    $player['first_name'] . ' ' . $player['last_name'],
                    $player['phone'] ?? '',
                    $player['email'] ?? '',
                    $player['age_group'] ?? '',
                    $player['primary_position'] ?? ''
                ]);
            }
        }

        fclose($output);
        exit;
    }
}

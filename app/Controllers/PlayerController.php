<?php

namespace App\Controllers;

use App\Core\Controller;

class PlayerController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->requireAuth();
    }

    /**
     * Render a view within the main layout
     */
    protected function playerView(string $viewPath, array $data = []): void
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
        $data['errors'] = $data['errors'] ?? $this->getErrors();
        $data['success'] = $data['success'] ?? $this->getSuccess();

        $this->view('layouts.main', $data);
    }

    /**
     * List all players linked to the current user
     */
    public function myPlayers(): void
    {
        $user = $this->getUser();

        $players = $this->db->fetchAll(
            'SELECT * FROM players WHERE user_id = ? ORDER BY first_name, last_name',
            [$user['id']]
        );

        $this->playerView('player.my-players', [
            'user' => $user,
            'players' => $players,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show the add player form
     */
    public function addPlayerForm(): void
    {
        $user = $this->getUser();

        $this->playerView('player.add-player', [
            'user' => $user,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle the add player form submission
     */
    public function addPlayer(): void
    {
        $user = $this->getUser();

        // Verify CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/player/add');
            return;
        }

        // Get form data
        $firstName = trim($this->post('first_name', ''));
        $lastName = trim($this->post('last_name', ''));
        $email = strtolower(trim($this->post('email', '')));
        $phone = trim($this->post('phone', ''));
        $birthdate = $this->post('birthdate', '');
        $ageGroup = trim($this->post('age_group', ''));

        // Validate required fields
        $errors = [];
        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }
        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }

        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        // Check if email already exists in players table (and belongs to another user)
        if (!empty($email)) {
            $existingPlayer = $this->db->fetchOne(
                'SELECT id, user_id FROM players WHERE email = ?',
                [$email]
            );
            if ($existingPlayer && $existingPlayer['user_id'] != $user['id']) {
                $errors[] = 'A player with this email already exists';
            }
        }

        // Validate birthdate if provided
        if (!empty($birthdate)) {
            $birthdateObj = \DateTime::createFromFormat('Y-m-d', $birthdate);
            if (!$birthdateObj) {
                $errors[] = 'Invalid birthdate format';
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addError($error);
            }
            $this->redirect('/player/add');
            return;
        }

        // Create player record linked to current user
        $result = $this->db->execute(
            'INSERT INTO players (user_id, first_name, last_name, email, phone, birthdate, age_group, registration_source, registration_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $user['id'],
                $firstName,
                $lastName,
                $email ?: null,
                $phone ?: null,
                $birthdate ?: null,
                $ageGroup ?: null,
                'self_registration',
                'active',
            ]
        );

        if ($result) {
            $this->addSuccess("Player {$firstName} {$lastName} has been added successfully!");
            $this->redirect('/player/players');
        } else {
            $this->addError('Failed to add player. Please try again.');
            $this->redirect('/player/add');
        }
    }

    /**
     * View a specific player's profile
     */
    public function viewProfile(): void
    {
        $user = $this->getUser();
        $playerId = (int)$this->get('id', 0);

        if ($playerId > 0) {
            // View specific player (must belong to current user or be admin)
            $player = $this->db->fetchOne(
                'SELECT * FROM players WHERE id = ?',
                [$playerId]
            );

            if (!$player) {
                $this->addError('Player not found');
                $this->redirect('/player/players');
                return;
            }

            // Check permission - must be owner or admin
            if ($player['user_id'] != $user['id'] && !in_array($user['role'], ['admin', 'superuser'])) {
                $this->addError('Unauthorized');
                $this->redirect('/player/players');
                return;
            }
        } else {
            // View first player linked to current user
            $player = $this->db->fetchOne(
                'SELECT * FROM players WHERE user_id = ? ORDER BY id LIMIT 1',
                [$user['id']]
            );
        }

        // Get parent information if player exists
        $parents = [];
        if ($player) {
            $parents = $this->db->fetchAll(
                'SELECT * FROM parents WHERE player_id = ? ORDER BY guardian_number',
                [$player['id']]
            );
        }

        $this->playerView('player.profile', [
            'user' => $user,
            'player' => $player,
            'parents' => $parents,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show edit player form
     */
    public function editPlayerForm(): void
    {
        $user = $this->getUser();
        $playerId = (int)$this->get('id', 0);

        if ($playerId === 0) {
            $this->addError('Invalid player ID');
            $this->redirect('/player/players');
            return;
        }

        $player = $this->db->fetchOne(
            'SELECT * FROM players WHERE id = ?',
            [$playerId]
        );

        if (!$player) {
            $this->addError('Player not found');
            $this->redirect('/player/players');
            return;
        }

        // Check permission - must be owner or admin
        if ($player['user_id'] != $user['id'] && !in_array($user['role'], ['admin', 'superuser'])) {
            $this->addError('Unauthorized');
            $this->redirect('/player/players');
            return;
        }

        $this->playerView('player.edit-player', [
            'user' => $user,
            'player' => $player,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Handle edit player form submission
     */
    public function updatePlayer(): void
    {
        $user = $this->getUser();
        $playerId = (int)$this->post('player_id', 0);

        // Verify CSRF token
        $csrfToken = $this->post('csrf_token');
        if (!$this->verifyCsrfToken($csrfToken)) {
            $this->addError('CSRF token validation failed');
            $this->redirect('/player/edit?id=' . $playerId);
            return;
        }

        if ($playerId === 0) {
            $this->addError('Invalid player ID');
            $this->redirect('/player/players');
            return;
        }

        // Get existing player
        $player = $this->db->fetchOne(
            'SELECT * FROM players WHERE id = ?',
            [$playerId]
        );

        if (!$player) {
            $this->addError('Player not found');
            $this->redirect('/player/players');
            return;
        }

        // Check permission - must be owner or admin
        if ($player['user_id'] != $user['id'] && !in_array($user['role'], ['admin', 'superuser'])) {
            $this->addError('Unauthorized');
            $this->redirect('/player/players');
            return;
        }

        // Get form data
        $firstName = trim($this->post('first_name', ''));
        $lastName = trim($this->post('last_name', ''));
        $email = strtolower(trim($this->post('email', '')));
        $phone = trim($this->post('phone', ''));
        $birthdate = $this->post('birthdate', '');
        $ageGroup = trim($this->post('age_group', ''));

        // Validate required fields
        $errors = [];
        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }
        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }

        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email address';
        }

        // Check if email already exists (and belongs to another player)
        if (!empty($email)) {
            $existingPlayer = $this->db->fetchOne(
                'SELECT id FROM players WHERE email = ? AND id != ?',
                [$email, $playerId]
            );
            if ($existingPlayer) {
                $errors[] = 'A player with this email already exists';
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addError($error);
            }
            $this->redirect('/player/edit?id=' . $playerId);
            return;
        }

        // Update player record
        $result = $this->db->execute(
            'UPDATE players SET first_name = ?, last_name = ?, email = ?, phone = ?, birthdate = ?, age_group = ? WHERE id = ?',
            [
                $firstName,
                $lastName,
                $email ?: null,
                $phone ?: null,
                $birthdate ?: null,
                $ageGroup ?: null,
                $playerId,
            ]
        );

        if ($result) {
            $this->addSuccess("Player {$firstName} {$lastName} has been updated successfully!");
            $this->redirect('/player/players');
        } else {
            $this->addError('Failed to update player. Please try again.');
            $this->redirect('/player/edit?id=' . $playerId);
        }
    }

    /**
     * List available tryouts for a player
     */
    public function listTryouts(): void
    {
        $user = $this->getUser();

        // Get upcoming tryouts
        $tryouts = $this->db->fetchAll(
            "SELECT t.*, tl.name as location_name, tl.street_address, tl.city, tl.state
             FROM tryouts t
             JOIN tryout_locations tl ON t.location_id = tl.id
             WHERE t.tryout_date >= CURDATE() AND t.status IN ('scheduled', 'open')
             ORDER BY t.tryout_date, t.start_time"
        );

        $this->playerView('player.tryouts', [
            'user' => $user,
            'tryouts' => $tryouts,
        ]);
    }
}

<?php

namespace App\Services;

use App\Core\Database;
use App\Models\User;
use App\Models\Player;

class RegistrationService
{
    private Database $db;
    private User $userModel;
    private Player $playerModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->playerModel = new Player();
    }

    /**
     * Check if email exists in players table
     * Returns player data if found, null otherwise
     */
    public function findPlayerByEmail(string $email): array|null
    {
        if (empty($email)) {
            return null;
        }

        $result = $this->db->fetchOne(
            'SELECT id, first_name, last_name, email, age_group, birthdate, registration_status FROM players WHERE email = ?',
            [strtolower(trim($email))]
        );

        return $result ?: null;
    }

    /**
     * Register a user with email lookup
     * Email becomes the username - no separate username field
     *
     * Returns array with:
     * - success: bool
     * - message: string
     * - user_id: int|null
     * - player_id: int|null (only if linked to existing player)
     */
    public function registerUser(array $data): array
    {
        $email = strtolower(trim($data['email'] ?? ''));
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';
        $ipAddress = $data['ip_address'] ?? '';
        $userAgent = $data['user_agent'] ?? '';

        // Validate email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Invalid email address',
                'user_id' => null,
                'player_id' => null,
            ];
        }

        // Validate password
        if (empty($password) || strlen($password) < 8) {
            return [
                'success' => false,
                'message' => 'Password must be at least 8 characters',
                'user_id' => null,
                'player_id' => null,
            ];
        }

        if ($password !== $passwordConfirm) {
            return [
                'success' => false,
                'message' => 'Passwords do not match',
                'user_id' => null,
                'player_id' => null,
            ];
        }

        // Check if user email already exists (email is used as username)
        $existingUser = $this->userModel->findByEmail($email);
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'An account with this email already exists',
                'user_id' => null,
                'player_id' => null,
            ];
        }

        // Look up player by email to see if we should link accounts
        $existingPlayer = $this->findPlayerByEmail($email);

        if ($existingPlayer) {
            // Player found in database - create user and link to player
            return $this->registerWithExistingPlayer($email, $password, $existingPlayer, $ipAddress, $userAgent);
        } else {
            // New user - create user account only (no player record)
            return $this->registerNewUser($email, $password, $ipAddress, $userAgent);
        }
    }

    /**
     * Register a user with existing player data
     * Creates user account (email as username) and links to existing player
     * All registrations require admin approval
     */
    private function registerWithExistingPlayer(
        string $email,
        string $password,
        array $player,
        string $ipAddress,
        string $userAgent
    ): array {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Create user account with pending status (email is username)
        // Requires admin approval before activation
        $this->db->execute(
            'INSERT INTO users (username, email, password_hash, role, status, email_verified)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $email, // Email is used as username
                $email,
                $passwordHash,
                'player',
                'pending', // Requires admin approval
                0, // email_verified = false
            ]
        );

        $userId = $this->db->lastInsertId();

        // Link user to existing player
        $this->db->execute(
            'UPDATE players SET user_id = ? WHERE id = ?',
            [$userId, $player['id']]
        );

        // Send admin notification
        try {
            EmailService::sendAdminNewPlayerNotification(
                $_ENV['ADMIN_EMAIL'] ?? 'admin@ivlbaseball.com',
                'IVL Administrator',
                [
                    'first_name' => $player['first_name'] ?? '',
                    'last_name' => $player['last_name'] ?? '',
                    'email' => $email,
                    'phone' => '',
                    'age_group' => $player['age_group'] ?? '',
                    'existing_player' => true,
                ]
            );
        } catch (\Exception $e) {
            error_log("Failed to send admin notification: " . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'Registration successful! Your account is pending admin approval.',
            'user_id' => $userId,
            'player_id' => $player['id'],
        ];
    }

    /**
     * Register a new user (no existing player)
     * Creates ONLY user account with pending status
     * Players are added separately after account approval
     * Requires admin approval
     */
    private function registerNewUser(
        string $email,
        string $password,
        string $ipAddress,
        string $userAgent
    ): array {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

        // Create user account with pending status (email is username)
        // Must be approved by admin before activation
        $result = $this->db->execute(
            'INSERT INTO users (username, email, password_hash, role, status, email_verified)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $email, // Email is used as username
                $email,
                $passwordHash,
                'player',
                'pending', // Requires admin approval
                0, // email_verified = false
            ]
        );

        if (!$result) {
            return [
                'success' => false,
                'message' => 'Failed to create account. Please try again.',
                'user_id' => null,
                'player_id' => null,
            ];
        }

        $userId = $this->db->lastInsertId();

        // Send admin notification email
        try {
            EmailService::sendAdminNewUserNotification(
                $_ENV['ADMIN_EMAIL'] ?? 'admin@ivlbaseball.com',
                'IVL Administrator',
                [
                    'email' => $email,
                    'user_id' => $userId,
                ]
            );
        } catch (\Exception $e) {
            // Log but don't fail registration
            error_log("Failed to send admin notification: " . $e->getMessage());
        }

        return [
            'success' => true,
            'message' => 'Registration successful! An administrator will review your account shortly.',
            'user_id' => $userId,
            'player_id' => null, // No player linked yet
        ];
    }

    /**
     * Get parent data for a player
     */
    public function getParentData(int $playerId): array
    {
        $parents = $this->db->fetchAll(
            'SELECT id, guardian_number, full_name, phone, email, coaching_interest FROM parents WHERE player_id = ? ORDER BY guardian_number',
            [$playerId]
        );

        return $parents ?: [];
    }

    /**
     * Save parent/guardian information
     */
    public function saveParent(int $playerId, int $guardianNumber, array $parentData): int|bool
    {
        // Check if parent already exists for this player and guardian number
        $existing = $this->db->fetchOne(
            'SELECT id FROM parents WHERE player_id = ? AND guardian_number = ?',
            [$playerId, $guardianNumber]
        );

        if ($existing) {
            // Update existing parent
            return $this->db->execute(
                'UPDATE parents SET full_name = ?, phone = ?, email = ?, coaching_interest = ?
                 WHERE id = ?',
                [
                    trim($parentData['full_name'] ?? ''),
                    trim($parentData['phone'] ?? ''),
                    trim($parentData['email'] ?? ''),
                    $parentData['coaching_interest'] ? 1 : 0,
                    $existing['id'],
                ]
            );
        } else {
            // Create new parent
            return $this->db->execute(
                'INSERT INTO parents (player_id, guardian_number, full_name, phone, email, coaching_interest)
                 VALUES (?, ?, ?, ?, ?, ?)',
                [
                    $playerId,
                    $guardianNumber,
                    trim($parentData['full_name'] ?? ''),
                    trim($parentData['phone'] ?? ''),
                    trim($parentData['email'] ?? ''),
                    $parentData['coaching_interest'] ? 1 : 0,
                ]
            );
        }
    }

    /**
     * Get pending registrations (user accounts awaiting admin approval)
     * Now queries users table directly since we don't create player records during registration
     */
    public function getPendingRegistrations(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            'SELECT u.id as user_id, u.username, u.email, u.created_at as registered_at,
                    p.id as player_id, p.first_name, p.last_name, p.age_group
             FROM users u
             LEFT JOIN players p ON p.user_id = u.id
             WHERE u.status = ? AND u.role = ?
             ORDER BY u.created_at DESC
             LIMIT ? OFFSET ?',
            ['pending', 'player', $limit, $offset]
        );
    }

    /**
     * Get count of pending registrations
     */
    public function getPendingRegistrationCount(): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM users WHERE status = ? AND role = ?',
            ['pending', 'player']
        );

        return $result['count'] ?? 0;
    }

    /**
     * Approve a user registration
     * Works for both user-only accounts and accounts linked to existing players
     */
    public function approveRegistration(int $userId): bool
    {
        // Update user status to active
        $result = $this->db->execute(
            'UPDATE users SET status = ? WHERE id = ?',
            ['active', $userId]
        );

        if ($result) {
            // Get user info
            $user = $this->db->fetchOne('SELECT email, username FROM users WHERE id = ?', [$userId]);

            // Check if there's a linked player
            $player = $this->db->fetchOne('SELECT id, first_name, last_name FROM players WHERE user_id = ?', [$userId]);

            if ($user) {
                // Send approval email - use player name if available, otherwise use email
                $name = $player ? $player['first_name'] : explode('@', $user['email'])[0];
                try {
                    EmailService::sendPlayerApprovalEmail($user['email'], $name);
                } catch (\Exception $e) {
                    error_log("Failed to send approval email: " . $e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * Reject a user registration
     * Works for both user-only accounts and accounts linked to existing players
     */
    public function rejectRegistration(int $userId, string $reason = ''): bool
    {
        // Get user info before deletion
        $user = $this->db->fetchOne('SELECT email, username FROM users WHERE id = ?', [$userId]);

        if (!$user) {
            return false;
        }

        // Check if there's a linked player
        $player = $this->db->fetchOne('SELECT id, first_name, last_name FROM players WHERE user_id = ?', [$userId]);

        // If player linked, remove the user_id link (don't delete the player)
        if ($player) {
            $this->db->execute('UPDATE players SET user_id = NULL WHERE id = ?', [$player['id']]);
        }

        // Delete user account (will cascade to sessions)
        $this->db->execute('DELETE FROM users WHERE id = ?', [$userId]);

        // Send rejection email - use player name if available, otherwise use email
        $name = $player ? $player['first_name'] : explode('@', $user['email'])[0];
        try {
            EmailService::sendPlayerRejectionEmail($user['email'], $name, $reason);
        } catch (\Exception $e) {
            error_log("Failed to send rejection email: " . $e->getMessage());
        }

        return true;
    }
}

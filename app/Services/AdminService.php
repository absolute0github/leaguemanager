<?php

namespace App\Services;

use App\Core\Database;

class AdminService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ============================================
     * USER MANAGEMENT
     * ============================================
     */

    /**
     * Get all users with optional filters
     */
    public function getUsers(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = 'SELECT * FROM users WHERE 1=1';
        $params = [];

        // Role filter
        if (!empty($filters['role'])) {
            $query .= ' AND role = ?';
            $params[] = $filters['role'];
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        // Search filter (username or email)
        if (!empty($filters['search'])) {
            $query .= ' AND (username LIKE ? OR email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $query .= ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get count of users with optional filters
     */
    public function getUserCount(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as count FROM users WHERE 1=1';
        $params = [];

        if (!empty($filters['role'])) {
            $query .= ' AND role = ?';
            $params[] = $filters['role'];
        }

        if (!empty($filters['status'])) {
            $query .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (username LIKE ? OR email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $result = $this->db->fetchOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Get user by ID
     */
    public function getUser(int $userId): array|null
    {
        return $this->db->fetchOne(
            'SELECT * FROM users WHERE id = ?',
            [$userId]
        );
    }

    /**
     * Create new user
     */
    public function createUser(array $data): int|bool
    {
        // Validate email doesn't exist
        $existing = $this->db->fetchOne(
            'SELECT id FROM users WHERE email = ?',
            [strtolower($data['email'] ?? '')]
        );
        if ($existing) {
            return false;
        }

        // Validate username doesn't exist
        $existing = $this->db->fetchOne(
            'SELECT id FROM users WHERE username = ?',
            [strtolower($data['username'] ?? '')]
        );
        if ($existing) {
            return false;
        }

        $passwordHash = password_hash($data['password'] ?? '', PASSWORD_BCRYPT, ['cost' => 12]);

        $success = $this->db->execute(
            'INSERT INTO users (username, email, password_hash, role, status, email_verified)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                strtolower($data['username'] ?? ''),
                strtolower($data['email'] ?? ''),
                $passwordHash,
                $data['role'] ?? 'player',
                $data['status'] ?? 'active',
                $data['email_verified'] ? 1 : 0,
            ]
        );

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update user
     */
    public function updateUser(int $userId, array $data): bool
    {
        $updates = [];
        $params = [];

        if (isset($data['email'])) {
            $updates[] = 'email = ?';
            $params[] = strtolower($data['email']);
        }

        if (isset($data['role'])) {
            $updates[] = 'role = ?';
            $params[] = $data['role'];
        }

        if (isset($data['status'])) {
            $updates[] = 'status = ?';
            $params[] = $data['status'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updates[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        if (empty($updates)) {
            return true;
        }

        $params[] = $userId;
        $updateStr = implode(', ', $updates);

        return $this->db->execute(
            "UPDATE users SET $updateStr WHERE id = ?",
            $params
        );
    }

    /**
     * ============================================
     * PLAYER MANAGEMENT
     * ============================================
     */

    /**
     * Get all players with optional filters
     */
    public function getPlayers(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = 'SELECT p.*, u.username, u.email as user_email FROM players p
                  LEFT JOIN users u ON p.user_id = u.id WHERE 1=1';
        $params = [];

        // Age group filter
        if (!empty($filters['age_group'])) {
            $query .= ' AND p.age_group = ?';
            $params[] = $filters['age_group'];
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query .= ' AND p.registration_status = ?';
            $params[] = $filters['status'];
        }

        // Search filter (name or email)
        if (!empty($filters['search'])) {
            $query .= ' AND (CONCAT(p.first_name, " ", p.last_name) LIKE ? OR p.email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $query .= ' ORDER BY p.last_name, p.first_name ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get count of players with optional filters
     */
    public function getPlayerCount(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as count FROM players WHERE 1=1';
        $params = [];

        if (!empty($filters['age_group'])) {
            $query .= ' AND age_group = ?';
            $params[] = $filters['age_group'];
        }

        if (!empty($filters['status'])) {
            $query .= ' AND registration_status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (CONCAT(first_name, " ", last_name) LIKE ? OR email LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $result = $this->db->fetchOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Get player by ID with full details
     */
    public function getPlayer(int $playerId): array|null
    {
        $player = $this->db->fetchOne(
            'SELECT p.*, u.id as user_id, u.username, u.email as user_email, u.status as user_status
             FROM players p
             LEFT JOIN users u ON p.user_id = u.id
             WHERE p.id = ?',
            [$playerId]
        );

        if (!$player) {
            return null;
        }

        // Get parents
        $player['parents'] = $this->db->fetchAll(
            'SELECT * FROM parents WHERE player_id = ? ORDER BY guardian_number',
            [$playerId]
        );

        return $player;
    }

    /**
     * Update player
     */
    public function updatePlayer(int $playerId, array $data): bool
    {
        $updates = [];
        $params = [];

        $fieldMap = [
            'first_name', 'last_name', 'phone', 'birthdate',
            'street_address', 'city', 'state', 'zip_code',
            'age_group', 'shirt_size', 'primary_position',
            'secondary_position', 'school_name', 'grade_level',
            'registration_status', 'previous_team'
        ];

        foreach ($fieldMap as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return true;
        }

        $params[] = $playerId;
        $updateStr = implode(', ', $updates);

        return $this->db->execute(
            "UPDATE players SET $updateStr WHERE id = ?",
            $params
        );
    }

    /**
     * Get age groups with player counts
     */
    public function getAgeGroupStats(): array
    {
        return $this->db->fetchAll(
            'SELECT age_group, COUNT(*) as count FROM players WHERE age_group IS NOT NULL
             GROUP BY age_group ORDER BY age_group'
        );
    }

    /**
     * Get registration status stats
     */
    public function getRegistrationStatusStats(): array
    {
        return $this->db->fetchAll(
            'SELECT registration_status, COUNT(*) as count FROM players
             GROUP BY registration_status'
        );
    }

    /**
     * ============================================
     * TEAM MANAGEMENT
     * ============================================
     */

    /**
     * Get all teams with optional filters
     */
    public function getTeams(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = 'SELECT t.*, l.name as league_name, COUNT(tp.id) as player_count
                  FROM teams t
                  LEFT JOIN leagues l ON t.league_id = l.id
                  LEFT JOIN team_players tp ON t.id = tp.team_id
                  WHERE 1=1';
        $params = [];

        if (!empty($filters['league_id'])) {
            $query .= ' AND t.league_id = ?';
            $params[] = $filters['league_id'];
        }

        if (!empty($filters['age_group'])) {
            $query .= ' AND t.age_group = ?';
            $params[] = $filters['age_group'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND t.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $query .= ' GROUP BY t.id ORDER BY t.name ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get count of teams with optional filters
     */
    public function getTeamCount(array $filters = []): int
    {
        $query = 'SELECT COUNT(DISTINCT t.id) as count FROM teams t WHERE 1=1';
        $params = [];

        if (!empty($filters['league_id'])) {
            $query .= ' AND t.league_id = ?';
            $params[] = $filters['league_id'];
        }

        if (!empty($filters['age_group'])) {
            $query .= ' AND t.age_group = ?';
            $params[] = $filters['age_group'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND t.name LIKE ?';
            $params[] = '%' . $filters['search'] . '%';
        }

        $result = $this->db->fetchOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Get team by ID with roster
     */
    public function getTeam(int $teamId): array|null
    {
        $team = $this->db->fetchOne(
            'SELECT t.*, l.name as league_name FROM teams t
             LEFT JOIN leagues l ON t.league_id = l.id
             WHERE t.id = ?',
            [$teamId]
        );

        if (!$team) {
            return null;
        }

        // Get roster
        $team['players'] = $this->db->fetchAll(
            'SELECT p.*, tp.jersey_number, tp.status
             FROM team_players tp
             JOIN players p ON tp.player_id = p.id
             WHERE tp.team_id = ?
             ORDER BY p.last_name, p.first_name',
            [$teamId]
        );

        // Get coaches
        $team['coaches'] = $this->db->fetchAll(
            'SELECT c.*, u.username, u.email
             FROM coaches c
             JOIN users u ON c.user_id = u.id
             WHERE c.team_id = ?
             ORDER BY c.coach_type',
            [$teamId]
        );

        return $team;
    }

    /**
     * Create new team
     */
    public function createTeam(array $data): int|bool
    {
        $success = $this->db->execute(
            'INSERT INTO teams (league_id, name, age_group, max_players, status)
             VALUES (?, ?, ?, ?, ?)',
            [
                $data['league_id'] ?? null,
                $data['name'] ?? '',
                $data['age_group'] ?? '',
                $data['max_players'] ?? 15,
                $data['status'] ?? 'active',
            ]
        );

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update team
     */
    public function updateTeam(int $teamId, array $data): bool
    {
        $updates = [];
        $params = [];

        $fieldMap = ['name', 'age_group', 'max_players', 'status', 'league_id'];

        foreach ($fieldMap as $field) {
            if (isset($data[$field])) {
                $updates[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($updates)) {
            return true;
        }

        $params[] = $teamId;
        $updateStr = implode(', ', $updates);

        return $this->db->execute(
            "UPDATE teams SET $updateStr WHERE id = ?",
            $params
        );
    }

    /**
     * Add player to team
     */
    public function addPlayerToTeam(int $teamId, int $playerId, int $jerseyNumber = 0): bool
    {
        // Check if already on team
        $existing = $this->db->fetchOne(
            'SELECT id FROM team_players WHERE team_id = ? AND player_id = ?',
            [$teamId, $playerId]
        );

        if ($existing) {
            return false;
        }

        // Check team capacity
        $team = $this->getTeam($teamId);
        if ($team && count($team['players']) >= $team['max_players']) {
            return false;
        }

        return $this->db->execute(
            'INSERT INTO team_players (team_id, player_id, jersey_number, status, joined_date)
             VALUES (?, ?, ?, ?, NOW())',
            [$teamId, $playerId, $jerseyNumber, 'active']
        );
    }

    /**
     * Remove player from team
     */
    public function removePlayerFromTeam(int $teamId, int $playerId): bool
    {
        return $this->db->execute(
            'DELETE FROM team_players WHERE team_id = ? AND player_id = ?',
            [$teamId, $playerId]
        );
    }

    /**
     * ============================================
     * COACH MANAGEMENT
     * ============================================
     */

    /**
     * Get all coaches with optional filters
     */
    public function getCoaches(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $query = 'SELECT c.*, u.username, u.email, t.name as team_name
                  FROM coaches c
                  JOIN users u ON c.user_id = u.id
                  LEFT JOIN teams t ON c.team_id = t.id
                  WHERE 1=1';
        $params = [];

        if (!empty($filters['team_id'])) {
            $query .= ' AND c.team_id = ?';
            $params[] = $filters['team_id'];
        }

        if (!empty($filters['coach_type'])) {
            $query .= ' AND c.coach_type = ?';
            $params[] = $filters['coach_type'];
        }

        $query .= ' ORDER BY u.username ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get count of coaches
     */
    public function getCoachCount(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as count FROM coaches WHERE 1=1';
        $params = [];

        if (!empty($filters['team_id'])) {
            $query .= ' AND team_id = ?';
            $params[] = $filters['team_id'];
        }

        if (!empty($filters['coach_type'])) {
            $query .= ' AND coach_type = ?';
            $params[] = $filters['coach_type'];
        }

        $result = $this->db->fetchOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Assign coach to team
     */
    public function assignCoachToTeam(int $userId, int $teamId, string $coachType = 'head'): bool
    {
        // Check if coach already assigned
        $existing = $this->db->fetchOne(
            'SELECT id FROM coaches WHERE user_id = ? AND team_id = ?',
            [$userId, $teamId]
        );

        if ($existing) {
            return $this->db->execute(
                'UPDATE coaches SET coach_type = ? WHERE user_id = ? AND team_id = ?',
                [$coachType, $userId, $teamId]
            );
        }

        return $this->db->execute(
            'INSERT INTO coaches (user_id, team_id, coach_type) VALUES (?, ?, ?)',
            [$userId, $teamId, $coachType]
        );
    }

    /**
     * Remove coach from team
     */
    public function removeCoachFromTeam(int $userId, int $teamId): bool
    {
        return $this->db->execute(
            'DELETE FROM coaches WHERE user_id = ? AND team_id = ?',
            [$userId, $teamId]
        );
    }

    /**
     * ============================================
     * LEAGUE MANAGEMENT
     * ============================================
     */

    /**
     * Get all leagues
     */
    public function getLeagues(int $limit = 50, int $offset = 0): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM leagues ORDER BY year DESC, name ASC LIMIT ? OFFSET ?',
            [$limit, $offset]
        );
    }

    /**
     * Get league count
     */
    public function getLeagueCount(): int
    {
        $result = $this->db->fetchOne('SELECT COUNT(*) as count FROM leagues');
        return $result['count'] ?? 0;
    }

    /**
     * Create new league
     */
    public function createLeague(array $data): int|bool
    {
        return $this->db->execute(
            'INSERT INTO leagues (name, season, year, start_date, end_date, status)
             VALUES (?, ?, ?, ?, ?, ?)',
            [
                $data['name'] ?? '',
                $data['season'] ?? '',
                $data['year'] ?? date('Y'),
                $data['start_date'] ?? null,
                $data['end_date'] ?? null,
                $data['status'] ?? 'planning',
            ]
        ) ? $this->db->lastInsertId() : false;
    }

    /**
     * ============================================
     * STATISTICS
     * ============================================
     */

    /**
     * Get admin dashboard statistics
     */
    public function getDashboardStats(): array
    {
        return [
            'total_users' => $this->db->fetchOne('SELECT COUNT(*) as count FROM users')['count'] ?? 0,
            'total_players' => $this->db->fetchOne('SELECT COUNT(*) as count FROM players')['count'] ?? 0,
            'total_teams' => $this->db->fetchOne('SELECT COUNT(*) as count FROM teams')['count'] ?? 0,
            'total_coaches' => $this->db->fetchOne('SELECT COUNT(*) as count FROM coaches')['count'] ?? 0,
            'total_leagues' => $this->db->fetchOne('SELECT COUNT(*) as count FROM leagues')['count'] ?? 0,
            'pending_users' => $this->db->fetchOne('SELECT COUNT(*) as count FROM users WHERE status = ?', ['pending'])['count'] ?? 0,
            'pending_registrations' => $this->db->fetchOne(
                'SELECT COUNT(*) as count FROM users WHERE status = ? AND role = ?',
                ['pending', 'player']
            )['count'] ?? 0,
        ];
    }
}

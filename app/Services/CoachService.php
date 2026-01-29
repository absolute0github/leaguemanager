<?php

namespace App\Services;

use App\Core\Database;

class CoachService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get coach record by user ID
     */
    public function getCoachByUserId(int $userId): ?array
    {
        return $this->db->fetchOne(
            "SELECT c.*, u.username, u.email as user_email
             FROM coaches c
             JOIN users u ON c.user_id = u.id
             WHERE c.user_id = ?",
            [$userId]
        );
    }

    /**
     * Get team(s) assigned to a coach
     */
    public function getCoachTeams(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, l.name as league_name, l.season, l.year,
                    (SELECT COUNT(*) FROM team_players tp WHERE tp.team_id = t.id AND tp.status = 'active') as player_count,
                    (SELECT COUNT(*) FROM coaches c2 WHERE c2.team_id = t.id) as coach_count
             FROM teams t
             JOIN leagues l ON t.league_id = l.id
             JOIN coaches c ON c.team_id = t.id
             WHERE c.user_id = ?
             ORDER BY l.year DESC, t.name",
            [$userId]
        );
    }

    /**
     * Get a specific team with details
     */
    public function getTeam(int $teamId): ?array
    {
        return $this->db->fetchOne(
            "SELECT t.*, l.name as league_name, l.season, l.year,
                    (SELECT COUNT(*) FROM team_players tp WHERE tp.team_id = t.id AND tp.status = 'active') as player_count
             FROM teams t
             JOIN leagues l ON t.league_id = l.id
             WHERE t.id = ?",
            [$teamId]
        );
    }

    /**
     * Check if coach has access to a team
     */
    public function coachHasAccessToTeam(int $userId, int $teamId): bool
    {
        $result = $this->db->fetchOne(
            "SELECT id FROM coaches WHERE user_id = ? AND team_id = ?",
            [$userId, $teamId]
        );
        return $result !== null;
    }

    /**
     * Get team roster with player details
     */
    public function getTeamRoster(int $teamId): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, tp.jersey_number, tp.status as roster_status, tp.joined_date
             FROM players p
             JOIN team_players tp ON p.id = tp.player_id
             WHERE tp.team_id = ?
             ORDER BY p.last_name, p.first_name",
            [$teamId]
        );
    }

    /**
     * Get team roster with parent contact info
     */
    public function getTeamRosterWithParents(int $teamId): array
    {
        $players = $this->getTeamRoster($teamId);

        foreach ($players as &$player) {
            $player['parents'] = $this->db->fetchAll(
                "SELECT * FROM parents WHERE player_id = ? ORDER BY guardian_number",
                [$player['id']]
            );
        }

        return $players;
    }

    /**
     * Get a single player with parent info
     */
    public function getPlayerWithParents(int $playerId): ?array
    {
        $player = $this->db->fetchOne(
            "SELECT * FROM players WHERE id = ?",
            [$playerId]
        );

        if ($player) {
            $player['parents'] = $this->db->fetchAll(
                "SELECT * FROM parents WHERE player_id = ? ORDER BY guardian_number",
                [$playerId]
            );
        }

        return $player;
    }

    /**
     * Get all coaches for a team
     */
    public function getTeamCoaches(int $teamId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.username, u.email
             FROM coaches c
             JOIN users u ON c.user_id = u.id
             WHERE c.team_id = ?
             ORDER BY c.coach_type, u.username",
            [$teamId]
        );
    }

    /**
     * Get all parent emails for a team
     */
    public function getTeamParentEmails(int $teamId): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT par.email, par.full_name, p.first_name as player_first_name, p.last_name as player_last_name
             FROM parents par
             JOIN players p ON par.player_id = p.id
             JOIN team_players tp ON p.id = tp.player_id
             WHERE tp.team_id = ? AND tp.status = 'active' AND par.email IS NOT NULL AND par.email != ''
             ORDER BY par.full_name",
            [$teamId]
        );
    }

    /**
     * Get all player emails for a team
     */
    public function getTeamPlayerEmails(int $teamId): array
    {
        return $this->db->fetchAll(
            "SELECT DISTINCT p.email, p.first_name, p.last_name
             FROM players p
             JOIN team_players tp ON p.id = tp.player_id
             WHERE tp.team_id = ? AND tp.status = 'active' AND p.email IS NOT NULL AND p.email != ''
             ORDER BY p.last_name, p.first_name",
            [$teamId]
        );
    }

    /**
     * Get team statistics
     */
    public function getTeamStats(int $teamId): array
    {
        $team = $this->getTeam($teamId);

        $stats = [
            'total_players' => 0,
            'active_players' => 0,
            'injured_players' => 0,
            'inactive_players' => 0,
            'positions' => [],
            'available_spots' => 0,
        ];

        if (!$team) {
            return $stats;
        }

        // Count by status
        $statusCounts = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count
             FROM team_players
             WHERE team_id = ?
             GROUP BY status",
            [$teamId]
        );

        foreach ($statusCounts as $row) {
            $stats['total_players'] += $row['count'];
            switch ($row['status']) {
                case 'active':
                    $stats['active_players'] = $row['count'];
                    break;
                case 'injured':
                    $stats['injured_players'] = $row['count'];
                    break;
                case 'inactive':
                    $stats['inactive_players'] = $row['count'];
                    break;
            }
        }

        $stats['available_spots'] = max(0, $team['max_players'] - $stats['active_players']);

        // Count by position
        $positionCounts = $this->db->fetchAll(
            "SELECT p.primary_position, COUNT(*) as count
             FROM players p
             JOIN team_players tp ON p.id = tp.player_id
             WHERE tp.team_id = ? AND tp.status = 'active' AND p.primary_position IS NOT NULL
             GROUP BY p.primary_position
             ORDER BY count DESC",
            [$teamId]
        );

        foreach ($positionCounts as $row) {
            $stats['positions'][$row['primary_position']] = $row['count'];
        }

        return $stats;
    }

    /**
     * Get coach dashboard data
     */
    public function getDashboardData(int $userId): array
    {
        $coach = $this->getCoachByUserId($userId);
        $teams = $this->getCoachTeams($userId);

        $data = [
            'coach' => $coach,
            'teams' => $teams,
            'total_players' => 0,
            'team_stats' => [],
        ];

        foreach ($teams as $team) {
            $stats = $this->getTeamStats($team['id']);
            $data['team_stats'][$team['id']] = $stats;
            $data['total_players'] += $stats['total_players'];
        }

        return $data;
    }

    /**
     * Update player jersey number
     */
    public function updateJerseyNumber(int $teamId, int $playerId, ?int $jerseyNumber): bool
    {
        return $this->db->execute(
            "UPDATE team_players SET jersey_number = ? WHERE team_id = ? AND player_id = ?",
            [$jerseyNumber, $teamId, $playerId]
        );
    }

    /**
     * Update player roster status
     */
    public function updatePlayerRosterStatus(int $teamId, int $playerId, string $status): bool
    {
        $validStatuses = ['active', 'inactive', 'injured'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        return $this->db->execute(
            "UPDATE team_players SET status = ? WHERE team_id = ? AND player_id = ?",
            [$status, $teamId, $playerId]
        );
    }
}

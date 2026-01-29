<?php

namespace App\Modules\attendance\Controllers;

use App\Modules\ModuleController;
use App\Core\Database;
use App\Services\CoachService;

class AttendanceController extends ModuleController
{
    private Database $db;
    private CoachService $coachService;

    public function __construct()
    {
        parent::__construct('attendance');
        $this->db = Database::getInstance();
        $this->coachService = new CoachService();
    }

    /**
     * Hook: Dashboard widget for coaches
     */
    public function coachWidget(array $context): string
    {
        $user = $context['user'] ?? null;
        if (!$user) {
            return '';
        }

        // Get recent attendance stats
        $stats = $this->getRecentStats($user['id']);

        ob_start();
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Attendance</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="h4 mb-0 text-success"><?php echo $stats['present'] ?? 0; ?></div>
                            <small class="text-muted">Present</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-danger"><?php echo $stats['absent'] ?? 0; ?></div>
                            <small class="text-muted">Absent</small>
                        </div>
                        <div class="col-4">
                            <div class="h4 mb-0 text-warning"><?php echo $stats['excused'] ?? 0; ?></div>
                            <small class="text-muted">Excused</small>
                        </div>
                    </div>
                    <a href="/coach/attendance" class="btn btn-info btn-sm w-100">
                        <i class="fas fa-clipboard-list me-1"></i> Manage Attendance
                    </a>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Hook: Sidebar link for coach navigation
     */
    public function sidebarLink(array $context): string
    {
        $currentPath = $context['current_path'] ?? '';
        $isActive = strpos($currentPath, '/coach/attendance') === 0;

        return sprintf(
            '<li class="nav-item">
                <a class="nav-link %s" href="/coach/attendance">
                    <i class="fas fa-clipboard-check me-2"></i> Attendance
                </a>
            </li>',
            $isActive ? 'active' : ''
        );
    }

    /**
     * Attendance index/dashboard
     */
    public function index(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        // Get coach's teams
        $teams = $this->coachService->getCoachTeams($user['id']);

        // Get recent events
        $recentEvents = $this->getRecentEvents($user['id']);

        $this->moduleViewWithLayout('index', 'layouts.coach', [
            'user' => $user,
            'teams' => $teams,
            'recentEvents' => $recentEvents,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Take attendance for a team/event
     */
    public function takeAttendance(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        $teamId = (int)$this->get('team_id', 0);
        $eventDate = $this->get('date', date('Y-m-d'));
        $eventType = $this->get('type', 'practice');

        if (!$teamId) {
            // Get first team
            $teams = $this->coachService->getCoachTeams($user['id']);
            if (!empty($teams)) {
                $teamId = $teams[0]['id'];
            }
        }

        // Verify access
        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('Access denied');
            $this->redirect('/coach/attendance');
        }

        $team = $this->coachService->getTeam($teamId);
        $roster = $this->coachService->getTeamRoster($teamId);

        // Get existing attendance for this date
        $existingAttendance = $this->getAttendanceForDate($teamId, $eventDate);

        $this->moduleViewWithLayout('take', 'layouts.coach', [
            'user' => $user,
            'team' => $team,
            'roster' => $roster,
            'eventDate' => $eventDate,
            'eventType' => $eventType,
            'existingAttendance' => $existingAttendance,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Save attendance
     */
    public function saveAttendance(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        if (!$this->validateCsrfToken($this->post('csrf_token'))) {
            $this->addError('Invalid security token');
            $this->redirect('/coach/attendance');
        }

        $teamId = (int)$this->post('team_id', 0);
        $eventDate = $this->post('event_date', date('Y-m-d'));
        $eventType = $this->post('event_type', 'practice');
        $eventName = $this->post('event_name', '');
        $attendance = $this->post('attendance', []);

        // Verify access
        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('Access denied');
            $this->redirect('/coach/attendance');
        }

        // Delete existing attendance for this date/team
        $this->db->execute(
            "DELETE FROM mod_attendance_records WHERE team_id = ? AND event_date = ?",
            [$teamId, $eventDate]
        );

        // Insert new attendance records
        foreach ($attendance as $playerId => $data) {
            $status = $data['status'] ?? 'present';
            $notes = $data['notes'] ?? '';

            $this->db->execute(
                "INSERT INTO mod_attendance_records (team_id, player_id, event_type, event_date, event_name, status, notes, marked_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$teamId, $playerId, $eventType, $eventDate, $eventName, $status, $notes, $user['id']]
            );
        }

        $this->addSuccess('Attendance saved successfully');
        $this->redirect('/coach/attendance?team_id=' . $teamId);
    }

    /**
     * View attendance history
     */
    public function history(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        $teamId = (int)$this->get('team_id', 0);
        $month = $this->get('month', date('Y-m'));

        if (!$teamId) {
            $teams = $this->coachService->getCoachTeams($user['id']);
            if (!empty($teams)) {
                $teamId = $teams[0]['id'];
            }
        }

        // Verify access
        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('Access denied');
            $this->redirect('/coach/attendance');
        }

        $team = $this->coachService->getTeam($teamId);
        $teams = $this->coachService->getCoachTeams($user['id']);
        $history = $this->getAttendanceHistory($teamId, $month);

        $this->moduleViewWithLayout('history', 'layouts.coach', [
            'user' => $user,
            'team' => $team,
            'teams' => $teams,
            'history' => $history,
            'month' => $month,
            'csrfToken' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Generate attendance report
     */
    public function report(): void
    {
        $this->requireAuth();
        $user = $this->getUser();

        $teamId = (int)$this->get('team_id', 0);

        if (!$this->coachService->coachHasAccessToTeam($user['id'], $teamId)) {
            $this->addError('Access denied');
            $this->redirect('/coach/attendance');
        }

        $team = $this->coachService->getTeam($teamId);
        $report = $this->generateAttendanceReport($teamId);

        $this->moduleViewWithLayout('report', 'layouts.coach', [
            'user' => $user,
            'team' => $team,
            'report' => $report,
        ]);
    }

    // ========== Helper Methods ==========

    private function getRecentStats(int $userId): array
    {
        $teams = $this->coachService->getCoachTeams($userId);
        if (empty($teams)) {
            return ['present' => 0, 'absent' => 0, 'excused' => 0];
        }

        $teamIds = array_column($teams, 'id');
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

        try {
            $stats = $this->db->fetchOne(
                "SELECT
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'excused' THEN 1 ELSE 0 END) as excused
                 FROM mod_attendance_records
                 WHERE team_id IN ($placeholders)
                   AND event_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)",
                $teamIds
            );

            return $stats ?: ['present' => 0, 'absent' => 0, 'excused' => 0];
        } catch (\Exception $e) {
            return ['present' => 0, 'absent' => 0, 'excused' => 0];
        }
    }

    private function getRecentEvents(int $userId): array
    {
        $teams = $this->coachService->getCoachTeams($userId);
        if (empty($teams)) {
            return [];
        }

        $teamIds = array_column($teams, 'id');
        $placeholders = implode(',', array_fill(0, count($teamIds), '?'));

        try {
            return $this->db->fetchAll(
                "SELECT DISTINCT event_date, event_type, team_id,
                        (SELECT name FROM teams WHERE id = team_id) as team_name,
                        COUNT(*) as player_count
                 FROM mod_attendance_records
                 WHERE team_id IN ($placeholders)
                 GROUP BY event_date, event_type, team_id
                 ORDER BY event_date DESC
                 LIMIT 10",
                $teamIds
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAttendanceForDate(int $teamId, string $date): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT player_id, status, notes FROM mod_attendance_records
                 WHERE team_id = ? AND event_date = ?",
                [$teamId, $date]
            );

            $result = [];
            foreach ($rows as $row) {
                $result[$row['player_id']] = $row;
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAttendanceHistory(int $teamId, string $month): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT ar.*, p.first_name, p.last_name
                 FROM mod_attendance_records ar
                 JOIN players p ON ar.player_id = p.id
                 WHERE ar.team_id = ? AND DATE_FORMAT(ar.event_date, '%Y-%m') = ?
                 ORDER BY ar.event_date DESC, p.last_name, p.first_name",
                [$teamId, $month]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function generateAttendanceReport(int $teamId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT p.id, p.first_name, p.last_name,
                        COUNT(*) as total_events,
                        SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present,
                        SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent,
                        SUM(CASE WHEN ar.status = 'excused' THEN 1 ELSE 0 END) as excused,
                        SUM(CASE WHEN ar.status = 'late' THEN 1 ELSE 0 END) as late,
                        ROUND(SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 1) as attendance_rate
                 FROM players p
                 JOIN team_players tp ON p.id = tp.player_id AND tp.team_id = ?
                 LEFT JOIN mod_attendance_records ar ON p.id = ar.player_id AND ar.team_id = ?
                 GROUP BY p.id, p.first_name, p.last_name
                 ORDER BY p.last_name, p.first_name",
                [$teamId, $teamId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }
}

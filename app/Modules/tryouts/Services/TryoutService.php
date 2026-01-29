<?php

namespace App\Modules\tryouts\Services;

use App\Core\Database;
use App\Services\EmailService;

/**
 * Tryout Service
 * Manages tryout events with atomic participant counting
 */
class TryoutService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get tryouts with filters and pagination
     */
    public function getTryouts(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $query = 'SELECT t.*, tl.name as location_name, tl.city as location_city, tl.state as location_state
                  FROM tryouts t
                  LEFT JOIN tryout_locations tl ON t.location_id = tl.id
                  WHERE 1=1';
        $params = [];

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query .= ' AND t.tryout_date >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= ' AND t.tryout_date <= ?';
            $params[] = $filters['date_to'];
        }

        // Filter by age group
        if (!empty($filters['age_group'])) {
            $query .= ' AND t.age_group = ?';
            $params[] = $filters['age_group'];
        }

        // Filter by location
        if (!empty($filters['location_id'])) {
            $query .= ' AND t.location_id = ?';
            $params[] = $filters['location_id'];
        }

        // Filter by status
        if (!empty($filters['status'])) {
            $query .= ' AND t.status = ?';
            $params[] = $filters['status'];
        }

        // Search by age group or location name
        if (!empty($filters['search'])) {
            $query .= ' AND (t.age_group LIKE ? OR tl.name LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $query .= ' ORDER BY t.tryout_date ASC, t.start_time ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get total count of tryouts with filters
     */
    public function getTryoutCount(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as count
                  FROM tryouts t
                  LEFT JOIN tryout_locations tl ON t.location_id = tl.id
                  WHERE 1=1';
        $params = [];

        // Apply same filters
        if (!empty($filters['date_from'])) {
            $query .= ' AND t.tryout_date >= ?';
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $query .= ' AND t.tryout_date <= ?';
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['age_group'])) {
            $query .= ' AND t.age_group = ?';
            $params[] = $filters['age_group'];
        }

        if (!empty($filters['location_id'])) {
            $query .= ' AND t.location_id = ?';
            $params[] = $filters['location_id'];
        }

        if (!empty($filters['status'])) {
            $query .= ' AND t.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (t.age_group LIKE ? OR tl.name LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }

        $result = $this->db->fetchOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Get single tryout with location details
     */
    public function getTryout(int $tryoutId): ?array
    {
        return $this->db->fetchOne(
            'SELECT t.*, tl.name as location_name, tl.street_address, tl.city, tl.state,
                    tl.zip_code, tl.map_link, tl.special_instructions
             FROM tryouts t
             LEFT JOIN tryout_locations tl ON t.location_id = tl.id
             WHERE t.id = ?',
            [$tryoutId]
        );
    }

    /**
     * Create new tryout
     */
    public function createTryout(array $data, int $createdBy): int|false
    {
        $result = $this->db->execute(
            'INSERT INTO tryouts (location_id, age_group, tryout_date, start_time, end_time,
                                  cost, max_participants, current_participants, status, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?)',
            [
                $data['location_id'],
                $data['age_group'],
                $data['tryout_date'],
                $data['start_time'],
                $data['end_time'],
                $data['cost'] ?? 0,
                $data['max_participants'] ?? null,
                $data['status'] ?? 'scheduled',
                $createdBy
            ]
        );

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update tryout
     */
    public function updateTryout(int $tryoutId, array $data): bool
    {
        // Prevent reducing max_participants below current_participants
        if (isset($data['max_participants'])) {
            $tryout = $this->getTryout($tryoutId);
            if ($tryout && $data['max_participants'] < $tryout['current_participants']) {
                return false; // Cannot reduce capacity below current registrations
            }
        }

        return $this->db->execute(
            'UPDATE tryouts
             SET location_id = ?, age_group = ?, tryout_date = ?, start_time = ?, end_time = ?,
                 cost = ?, max_participants = ?, status = ?
             WHERE id = ?',
            [
                $data['location_id'],
                $data['age_group'],
                $data['tryout_date'],
                $data['start_time'],
                $data['end_time'],
                $data['cost'] ?? 0,
                $data['max_participants'] ?? null,
                $data['status'] ?? 'scheduled',
                $tryoutId
            ]
        );
    }

    /**
     * Delete tryout
     */
    public function deleteTryout(int $tryoutId): bool
    {
        return $this->db->execute(
            'DELETE FROM tryouts WHERE id = ?',
            [$tryoutId]
        );
    }

    /**
     * Update tryout status
     */
    public function updateStatus(int $tryoutId, string $status): bool
    {
        return $this->db->execute(
            'UPDATE tryouts SET status = ? WHERE id = ?',
            [$status, $tryoutId]
        );
    }

    /**
     * Increment participant count atomically (CRITICAL - prevents race conditions)
     */
    public function incrementParticipants(int $tryoutId): bool
    {
        $this->db->beginTransaction();

        try {
            // Lock the row for update
            $tryout = $this->db->fetchOne(
                'SELECT id, max_participants, current_participants
                 FROM tryouts WHERE id = ? FOR UPDATE',
                [$tryoutId]
            );

            if (!$tryout) {
                $this->db->rollback();
                return false;
            }

            // Check if full
            if ($tryout['max_participants'] !== null &&
                $tryout['current_participants'] >= $tryout['max_participants']) {
                $this->db->rollback();
                return false; // Full
            }

            // Increment
            $this->db->execute(
                'UPDATE tryouts SET current_participants = current_participants + 1 WHERE id = ?',
                [$tryoutId]
            );

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Failed to increment participants: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrement participant count atomically
     */
    public function decrementParticipants(int $tryoutId): bool
    {
        $this->db->beginTransaction();

        try {
            // Lock the row for update
            $tryout = $this->db->fetchOne(
                'SELECT id, current_participants
                 FROM tryouts WHERE id = ? FOR UPDATE',
                [$tryoutId]
            );

            if (!$tryout || $tryout['current_participants'] <= 0) {
                $this->db->rollback();
                return false;
            }

            // Decrement
            $this->db->execute(
                'UPDATE tryouts SET current_participants = current_participants - 1 WHERE id = ?',
                [$tryoutId]
            );

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Failed to decrement participants: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if tryout is available for registration
     */
    public function isAvailable(int $tryoutId): bool
    {
        $tryout = $this->getTryout($tryoutId);

        if (!$tryout) {
            return false;
        }

        // Must be open status
        if ($tryout['status'] !== 'open') {
            return false;
        }

        // Must be in the future
        if (strtotime($tryout['tryout_date']) < strtotime('today')) {
            return false;
        }

        return true;
    }

    /**
     * Check if tryout is full
     */
    public function isFull(int $tryoutId): bool
    {
        $tryout = $this->getTryout($tryoutId);

        if (!$tryout) {
            return true; // Not found = treat as full
        }

        if ($tryout['max_participants'] === null) {
            return false; // No limit
        }

        return $tryout['current_participants'] >= $tryout['max_participants'];
    }

    /**
     * Get available spots
     */
    public function getAvailableSpots(int $tryoutId): int
    {
        $tryout = $this->getTryout($tryoutId);

        if (!$tryout) {
            return 0;
        }

        if ($tryout['max_participants'] === null) {
            return 999; // Unlimited
        }

        return max(0, $tryout['max_participants'] - $tryout['current_participants']);
    }

    /**
     * Import tryouts from CSV
     */
    public function importFromCSV(string $filePath, int $createdBy): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        if (!file_exists($filePath)) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['File not found']];
        }

        $file = fopen($filePath, 'r');
        $headers = fgetcsv($file);

        // Expected: location_name, age_group, tryout_date, start_time, end_time, cost, max_participants, status

        $locationService = new TryoutLocationService();

        while (($row = fgetcsv($file)) !== false) {
            try {
                $data = array_combine($headers, $row);

                // Look up or create location
                $locations = $locationService->getLocations(['search' => $data['location_name']]);

                if (empty($locations)) {
                    // Create location
                    $locationId = $locationService->createLocation([
                        'name' => $data['location_name'],
                        'street_address' => 'TBD',
                        'city' => 'TBD',
                        'state' => 'TBD',
                        'zip_code' => '00000',
                        'active' => true
                    ], $createdBy);
                } else {
                    $locationId = $locations[0]['id'];
                }

                // Check for duplicate
                $existing = $this->db->fetchOne(
                    'SELECT id FROM tryouts
                     WHERE location_id = ? AND age_group = ? AND tryout_date = ? AND start_time = ?',
                    [$locationId, $data['age_group'], $data['tryout_date'], $data['start_time']]
                );

                if ($existing) {
                    $skipped++;
                    continue;
                }

                // Create tryout
                $result = $this->createTryout([
                    'location_id' => $locationId,
                    'age_group' => $data['age_group'],
                    'tryout_date' => $data['tryout_date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'cost' => $data['cost'] ?? 0,
                    'max_participants' => !empty($data['max_participants']) ? $data['max_participants'] : null,
                    'status' => $data['status'] ?? 'scheduled'
                ], $createdBy);

                if ($result) {
                    $imported++;
                } else {
                    $skipped++;
                }

            } catch (\Exception $e) {
                $errors[] = "Row error: " . $e->getMessage();
                $skipped++;
            }
        }

        fclose($file);

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    /**
     * Get tryouts that need reminders (24h before, not yet reminded)
     */
    public function getTryoutsNeedingReminders(): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM tryouts
             WHERE tryout_date = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
             AND reminder_sent = FALSE
             AND status IN ("open", "scheduled")'
        );
    }

    /**
     * Send reminder emails for upcoming tryouts (called by cron)
     */
    public function sendReminders(): int
    {
        $tryouts = $this->getTryoutsNeedingReminders();
        $count = 0;

        foreach ($tryouts as $tryout) {
            // Get all confirmed registrations
            $registrations = $this->db->fetchAll(
                'SELECT tr.*, p.first_name, p.last_name, p.email
                 FROM tryout_registrations tr
                 JOIN players p ON tr.player_id = p.id
                 WHERE tr.tryout_id = ? AND tr.attendance_status = "registered"
                 AND tr.waitlist_position IS NULL',
                [$tryout['id']]
            );

            $tryoutDetails = $this->getTryout($tryout['id']);

            foreach ($registrations as $registration) {
                if (!empty($registration['email'])) {
                    // Send reminder email (will implement in Phase 7)
                    $count++;
                }
            }

            // Mark as reminded
            $this->db->execute(
                'UPDATE tryouts SET reminder_sent = TRUE, reminder_sent_at = NOW() WHERE id = ?',
                [$tryout['id']]
            );
        }

        return $count;
    }
}

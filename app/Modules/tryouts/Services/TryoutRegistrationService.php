<?php

namespace App\Modules\tryouts\Services;

use App\Core\Database;
use App\Services\EmailService;

/**
 * Tryout Registration Service
 * Manages registration flow, waitlist, payment/attendance tracking, and email notifications
 */
class TryoutRegistrationService
{
    private Database $db;
    private TryoutService $tryoutService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->tryoutService = new TryoutService();
    }

    /**
     * Main registration flow
     * Returns: ['success' => bool, 'waitlisted' => bool, 'registration_id' => int, 'errors' => []]
     */
    public function register(int $tryoutId, int $playerId, array $data): array
    {
        // Validate can register
        $canRegister = $this->canRegister($tryoutId, $playerId);
        if (!$canRegister['valid']) {
            return [
                'success' => false,
                'waitlisted' => false,
                'registration_id' => null,
                'errors' => $canRegister['errors']
            ];
        }

        // Try to increment participant count atomically
        $hasSpot = $this->tryoutService->incrementParticipants($tryoutId);

        if ($hasSpot) {
            // Create confirmed registration
            $registrationId = $this->createRegistration($tryoutId, $playerId, $data, 'registered', null);

            if ($registrationId) {
                // Send confirmation email
                $this->sendConfirmationEmail($registrationId);

                return [
                    'success' => true,
                    'waitlisted' => false,
                    'registration_id' => $registrationId,
                    'errors' => []
                ];
            }
        } else {
            // Tryout is full - add to waitlist
            $registrationId = $this->addToWaitlist($tryoutId, $playerId, $data);

            if ($registrationId) {
                return [
                    'success' => true,
                    'waitlisted' => true,
                    'registration_id' => $registrationId,
                    'errors' => []
                ];
            }
        }

        return [
            'success' => false,
            'waitlisted' => false,
            'registration_id' => null,
            'errors' => ['Failed to create registration']
        ];
    }

    /**
     * Validate if player can register for tryout
     */
    public function canRegister(int $tryoutId, int $playerId): array
    {
        $errors = [];

        // Check if tryout exists and is available
        if (!$this->tryoutService->isAvailable($tryoutId)) {
            $errors[] = 'This tryout is not available for registration';
        }

        // Check for duplicate registration
        if ($this->checkDuplicate($tryoutId, $playerId)) {
            $errors[] = 'You are already registered for this tryout';
        }

        // Check if player exists
        $player = $this->db->fetchOne('SELECT id FROM players WHERE id = ?', [$playerId]);
        if (!$player) {
            $errors[] = 'Player not found';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if player is already registered
     */
    public function checkDuplicate(int $tryoutId, int $playerId): bool
    {
        $existing = $this->db->fetchOne(
            'SELECT id FROM tryout_registrations
             WHERE tryout_id = ? AND player_id = ?
             AND attendance_status NOT IN ("cancelled")',
            [$tryoutId, $playerId]
        );

        return $existing !== null;
    }

    /**
     * Create registration record
     */
    private function createRegistration(
        int $tryoutId,
        int $playerId,
        array $data,
        string $attendanceStatus,
        ?int $waitlistPosition
    ): int|false {
        $tryout = $this->tryoutService->getTryout($tryoutId);

        // Determine payment status
        $paymentStatus = 'waived';
        if (($tryout['cost'] ?? 0) > 0) {
            $paymentStatus = 'pending';
        }

        $result = $this->db->execute(
            'INSERT INTO tryout_registrations
             (tryout_id, player_id, registration_date, payment_status, payment_method,
              payment_transaction_id, waiver_signed, attendance_status, waitlist_position)
             VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?)',
            [
                $tryoutId,
                $playerId,
                $paymentStatus,
                $data['payment_method'] ?? null,
                $data['payment_transaction_id'] ?? null,
                $data['waiver_signed'] ?? false,
                $attendanceStatus,
                $waitlistPosition
            ]
        );

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Add player to waitlist with position
     */
    public function addToWaitlist(int $tryoutId, int $playerId, array $data): int|false
    {
        // Get next waitlist position
        $position = $this->getNextWaitlistPosition($tryoutId);

        // Create registration with waitlist position
        $registrationId = $this->createRegistration(
            $tryoutId,
            $playerId,
            $data,
            'registered',
            $position
        );

        if ($registrationId) {
            // Send waitlist email
            $this->sendWaitlistEmail($registrationId);
        }

        return $registrationId;
    }

    /**
     * Get next waitlist position
     */
    private function getNextWaitlistPosition(int $tryoutId): int
    {
        $result = $this->db->fetchOne(
            'SELECT COUNT(*) + 1 as position
             FROM tryout_registrations
             WHERE tryout_id = ? AND waitlist_position IS NOT NULL',
            [$tryoutId]
        );

        return $result['position'] ?? 1;
    }

    /**
     * Get waitlist position for a player
     */
    public function getWaitlistPosition(int $tryoutId, int $playerId): ?int
    {
        $registration = $this->db->fetchOne(
            'SELECT waitlist_position
             FROM tryout_registrations
             WHERE tryout_id = ? AND player_id = ?
             AND attendance_status = "registered"',
            [$tryoutId, $playerId]
        );

        return $registration['waitlist_position'] ?? null;
    }

    /**
     * Promote player from waitlist to confirmed
     */
    public function promoteFromWaitlist(int $registrationId): bool
    {
        $this->db->beginTransaction();

        try {
            $registration = $this->db->fetchOne(
                'SELECT * FROM tryout_registrations WHERE id = ?',
                [$registrationId]
            );

            if (!$registration || $registration['waitlist_position'] === null) {
                $this->db->rollback();
                return false;
            }

            // Try to increment participant count
            $hasSpot = $this->tryoutService->incrementParticipants($registration['tryout_id']);

            if (!$hasSpot) {
                $this->db->rollback();
                return false;
            }

            // Update registration
            $this->db->execute(
                'UPDATE tryout_registrations
                 SET waitlist_position = NULL,
                     waitlist_notified_at = NOW(),
                     promoted_from_waitlist = TRUE
                 WHERE id = ?',
                [$registrationId]
            );

            // Reorder remaining waitlist
            $this->reorderWaitlist($registration['tryout_id']);

            $this->db->commit();

            // Send promotion email
            $this->sendPromotionEmail($registrationId);

            return true;

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Failed to promote from waitlist: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Reorder waitlist positions after promotion
     */
    private function reorderWaitlist(int $tryoutId): void
    {
        // Get all waitlist registrations ordered by registration date
        $waitlist = $this->db->fetchAll(
            'SELECT id FROM tryout_registrations
             WHERE tryout_id = ? AND waitlist_position IS NOT NULL
             ORDER BY registration_date ASC',
            [$tryoutId]
        );

        $position = 1;
        foreach ($waitlist as $registration) {
            $this->db->execute(
                'UPDATE tryout_registrations SET waitlist_position = ? WHERE id = ?',
                [$position, $registration['id']]
            );
            $position++;
        }
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(int $registrationId, string $status, ?string $transactionId = null): bool
    {
        $updates = ['payment_status = ?'];
        $params = [$status];

        if ($transactionId !== null) {
            $updates[] = 'payment_transaction_id = ?';
            $params[] = $transactionId;
        }

        $params[] = $registrationId;

        return $this->db->execute(
            'UPDATE tryout_registrations SET ' . implode(', ', $updates) . ' WHERE id = ?',
            $params
        );
    }

    /**
     * Update attendance status
     */
    public function updateAttendanceStatus(int $registrationId, string $status): bool
    {
        return $this->db->execute(
            'UPDATE tryout_registrations SET attendance_status = ? WHERE id = ?',
            [$status, $registrationId]
        );
    }

    /**
     * Add admin note to registration
     */
    public function addNote(int $registrationId, string $note): bool
    {
        $existing = $this->db->fetchOne(
            'SELECT admin_notes FROM tryout_registrations WHERE id = ?',
            [$registrationId]
        );

        $notes = $existing['admin_notes'] ?? '';
        $timestamp = date('Y-m-d H:i:s');
        $newNote = "[{$timestamp}] {$note}";

        $updatedNotes = $notes ? $notes . "\n" . $newNote : $newNote;

        return $this->db->execute(
            'UPDATE tryout_registrations SET admin_notes = ? WHERE id = ?',
            [$updatedNotes, $registrationId]
        );
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration(int $registrationId, string $reason): bool
    {
        $this->db->beginTransaction();

        try {
            $registration = $this->db->fetchOne(
                'SELECT * FROM tryout_registrations WHERE id = ?',
                [$registrationId]
            );

            if (!$registration) {
                $this->db->rollback();
                return false;
            }

            // Update registration status
            $this->db->execute(
                'UPDATE tryout_registrations
                 SET attendance_status = "cancelled", admin_notes = CONCAT(IFNULL(admin_notes, ""), "\n[Cancelled: ", ?, "]")
                 WHERE id = ?',
                [$reason, $registrationId]
            );

            // If was confirmed (not waitlisted), decrement participant count
            if ($registration['waitlist_position'] === null) {
                $this->tryoutService->decrementParticipants($registration['tryout_id']);
            } else {
                // Reorder waitlist
                $this->reorderWaitlist($registration['tryout_id']);
            }

            $this->db->commit();

            // Send cancellation email
            $this->sendCancellationEmail($registrationId, $reason);

            return true;

        } catch (\Exception $e) {
            $this->db->rollback();
            error_log("Failed to cancel registration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get registrations for a tryout (admin view)
     */
    public function getRegistrationsForTryout(int $tryoutId, array $filters = []): array
    {
        $query = 'SELECT tr.*, p.first_name, p.last_name, p.date_of_birth, p.email
                  FROM tryout_registrations tr
                  JOIN players p ON tr.player_id = p.id
                  WHERE tr.tryout_id = ?';
        $params = [$tryoutId];

        // Filter by attendance status
        if (!empty($filters['attendance_status'])) {
            $query .= ' AND tr.attendance_status = ?';
            $params[] = $filters['attendance_status'];
        }

        // Filter by payment status
        if (!empty($filters['payment_status'])) {
            $query .= ' AND tr.payment_status = ?';
            $params[] = $filters['payment_status'];
        }

        // Filter by waitlist status
        if (isset($filters['waitlisted'])) {
            if ($filters['waitlisted']) {
                $query .= ' AND tr.waitlist_position IS NOT NULL';
            } else {
                $query .= ' AND tr.waitlist_position IS NULL';
            }
        }

        $query .= ' ORDER BY tr.waitlist_position ASC, tr.registration_date ASC';

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get registrations for a player
     */
    public function getRegistrationsByPlayer(int $playerId): array
    {
        return $this->db->fetchAll(
            'SELECT tr.*, t.age_group, t.tryout_date, t.start_time, t.end_time, t.cost, t.status as tryout_status,
                    tl.name as location_name, tl.city, tl.state
             FROM tryout_registrations tr
             JOIN tryouts t ON tr.tryout_id = t.id
             LEFT JOIN tryout_locations tl ON t.location_id = tl.id
             WHERE tr.player_id = ?
             ORDER BY t.tryout_date DESC, t.start_time DESC',
            [$playerId]
        );
    }

    /**
     * Get single registration with full details
     */
    public function getRegistration(int $registrationId): ?array
    {
        return $this->db->fetchOne(
            'SELECT tr.*, p.first_name, p.last_name, p.date_of_birth, p.email,
                    t.age_group, t.tryout_date, t.start_time, t.end_time, t.cost,
                    tl.name as location_name, tl.street_address, tl.city, tl.state, tl.zip_code
             FROM tryout_registrations tr
             JOIN players p ON tr.player_id = p.id
             JOIN tryouts t ON tr.tryout_id = t.id
             LEFT JOIN tryout_locations tl ON t.location_id = tl.id
             WHERE tr.id = ?',
            [$registrationId]
        );
    }

    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail(int $registrationId): bool
    {
        $registration = $this->getRegistration($registrationId);

        if (!$registration || empty($registration['email'])) {
            return false;
        }

        $subject = "Tryout Registration Confirmed - {$registration['age_group']}";

        $data = [
            'player_name' => $registration['first_name'] . ' ' . $registration['last_name'],
            'age_group' => $registration['age_group'],
            'tryout_date' => date('F j, Y', strtotime($registration['tryout_date'])),
            'start_time' => date('g:i A', strtotime($registration['start_time'])),
            'end_time' => date('g:i A', strtotime($registration['end_time'])),
            'location_name' => $registration['location_name'],
            'location_address' => $registration['street_address'] . ', ' . $registration['city'] . ', ' . $registration['state'] . ' ' . $registration['zip_code'],
            'cost' => $registration['cost'],
            'payment_status' => $registration['payment_status']
        ];

        return EmailService::sendTryoutConfirmation($registration['email'], $subject, $data);
    }

    /**
     * Send waitlist email
     */
    private function sendWaitlistEmail(int $registrationId): bool
    {
        $registration = $this->getRegistration($registrationId);

        if (!$registration || empty($registration['email'])) {
            return false;
        }

        $subject = "Tryout Waitlist - {$registration['age_group']}";

        $data = [
            'player_name' => $registration['first_name'] . ' ' . $registration['last_name'],
            'age_group' => $registration['age_group'],
            'tryout_date' => date('F j, Y', strtotime($registration['tryout_date'])),
            'waitlist_position' => $registration['waitlist_position'],
            'location_name' => $registration['location_name']
        ];

        return EmailService::sendTryoutWaitlist($registration['email'], $subject, $data);
    }

    /**
     * Send promotion email
     */
    private function sendPromotionEmail(int $registrationId): bool
    {
        $registration = $this->getRegistration($registrationId);

        if (!$registration || empty($registration['email'])) {
            return false;
        }

        $subject = "Spot Available - Tryout Registration Confirmed";

        $data = [
            'player_name' => $registration['first_name'] . ' ' . $registration['last_name'],
            'age_group' => $registration['age_group'],
            'tryout_date' => date('F j, Y', strtotime($registration['tryout_date'])),
            'start_time' => date('g:i A', strtotime($registration['start_time'])),
            'location_name' => $registration['location_name']
        ];

        return EmailService::sendTryoutPromotion($registration['email'], $subject, $data);
    }

    /**
     * Send cancellation email
     */
    private function sendCancellationEmail(int $registrationId, string $reason): bool
    {
        $registration = $this->getRegistration($registrationId);

        if (!$registration || empty($registration['email'])) {
            return false;
        }

        $subject = "Tryout Registration Cancelled - {$registration['age_group']}";

        $data = [
            'player_name' => $registration['first_name'] . ' ' . $registration['last_name'],
            'age_group' => $registration['age_group'],
            'tryout_date' => date('F j, Y', strtotime($registration['tryout_date'])),
            'reason' => $reason,
            'refund_info' => $registration['payment_status'] === 'paid' ? 'A refund will be processed within 5-7 business days.' : ''
        ];

        return EmailService::sendTryoutCancellation($registration['email'], $subject, $data);
    }
}

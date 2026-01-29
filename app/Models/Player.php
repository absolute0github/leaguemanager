<?php

namespace App\Models;

use App\Core\Model;

class Player extends Model
{
    protected string $table = 'players';

    /**
     * Find player by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', strtolower(trim($email)));
    }

    /**
     * Find player by user ID
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->findBy('user_id', $userId);
    }

    /**
     * Get players by age group
     */
    public function getByAgeGroup(string $ageGroup): array
    {
        return $this->findAllBy('age_group', $ageGroup);
    }

    /**
     * Get players by registration status
     */
    public function getByStatus(string $status): array
    {
        return $this->findAllBy('registration_status', $status);
    }

    /**
     * Get active players
     */
    public function getActive(): array
    {
        return $this->findAllBy('registration_status', 'active');
    }

    /**
     * Link player to user account
     */
    public function linkToUser(int $playerId, int $userId): bool
    {
        return $this->update($playerId, ['user_id' => $userId]);
    }

    /**
     * Get players without user accounts
     */
    public function getUnlinkedPlayers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id IS NULL ORDER BY last_name, first_name";
        return $this->db->fetchAll($sql);
    }

    /**
     * Search players by name or email
     */
    public function search(string $query): array
    {
        $query = '%' . $query . '%';
        $sql = "SELECT * FROM {$this->table}
                WHERE first_name LIKE ?
                   OR last_name LIKE ?
                   OR email LIKE ?
                   OR CONCAT(first_name, ' ', last_name) LIKE ?
                ORDER BY last_name, first_name";
        return $this->db->fetchAll($sql, [$query, $query, $query, $query]);
    }

    /**
     * Get full name
     */
    public function getFullName(array $player): string
    {
        return trim(($player['first_name'] ?? '') . ' ' . ($player['last_name'] ?? ''));
    }

    /**
     * Create player
     */
    public function createPlayer(array $data): int|bool
    {
        $data['email'] = strtolower(trim($data['email'] ?? ''));
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['registration_status'] = $data['registration_status'] ?? 'pending';

        return $this->create($data);
    }

    /**
     * Update player registration status
     */
    public function updateStatus(int $playerId, string $status): bool
    {
        return $this->update($playerId, ['registration_status' => $status]);
    }
}

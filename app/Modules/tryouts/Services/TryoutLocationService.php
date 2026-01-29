<?php

namespace App\Modules\tryouts\Services;

use App\Core\Database;

/**
 * Tryout Location Service
 * Manages tryout venues/locations
 */
class TryoutLocationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get locations with filters and pagination
     */
    public function getLocations(array $filters = [], int $limit = 25, int $offset = 0): array
    {
        $query = 'SELECT * FROM tryout_locations WHERE 1=1';
        $params = [];

        // Filter by active status
        if (isset($filters['active']) && $filters['active'] !== '') {
            $query .= ' AND active = ?';
            $params[] = (int)$filters['active'];
        }

        // Filter by city
        if (!empty($filters['city'])) {
            $query .= ' AND city LIKE ?';
            $params[] = '%' . $filters['city'] . '%';
        }

        // Filter by state
        if (!empty($filters['state'])) {
            $query .= ' AND state = ?';
            $params[] = $filters['state'];
        }

        // Search by name or address
        if (!empty($filters['search'])) {
            $query .= ' AND (name LIKE ? OR street_address LIKE ? OR city LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $query .= ' ORDER BY name ASC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->fetchAll($query, $params);
    }

    /**
     * Get total count of locations with filters
     */
    public function getLocationCount(array $filters = []): int
    {
        $query = 'SELECT COUNT(*) as count FROM tryout_locations WHERE 1=1';
        $params = [];

        // Apply same filters as getLocations
        if (isset($filters['active']) && $filters['active'] !== '') {
            $query .= ' AND active = ?';
            $params[] = (int)$filters['active'];
        }

        if (!empty($filters['city'])) {
            $query .= ' AND city LIKE ?';
            $params[] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['state'])) {
            $query .= ' AND state = ?';
            $params[] = $filters['state'];
        }

        if (!empty($filters['search'])) {
            $query .= ' AND (name LIKE ? OR street_address LIKE ? OR city LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $result = $this->db->fetchOne($query, $params);
        return $result['count'] ?? 0;
    }

    /**
     * Get single location by ID
     */
    public function getLocation(int $locationId): ?array
    {
        return $this->db->fetchOne(
            'SELECT * FROM tryout_locations WHERE id = ?',
            [$locationId]
        );
    }

    /**
     * Create new location
     */
    public function createLocation(array $data, int $createdBy): int|false
    {
        $validation = $this->validateLocationData($data);
        if (!$validation['valid']) {
            return false;
        }

        $result = $this->db->execute(
            'INSERT INTO tryout_locations (name, street_address, city, state, zip_code, map_link, special_instructions, active, created_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $data['name'],
                $data['street_address'],
                $data['city'],
                $data['state'],
                $data['zip_code'],
                $data['map_link'] ?? null,
                $data['special_instructions'] ?? null,
                $data['active'] ?? true,
                $createdBy
            ]
        );

        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update location
     */
    public function updateLocation(int $locationId, array $data): bool
    {
        $validation = $this->validateLocationData($data);
        if (!$validation['valid']) {
            return false;
        }

        return $this->db->execute(
            'UPDATE tryout_locations
             SET name = ?, street_address = ?, city = ?, state = ?, zip_code = ?,
                 map_link = ?, special_instructions = ?, active = ?
             WHERE id = ?',
            [
                $data['name'],
                $data['street_address'],
                $data['city'],
                $data['state'],
                $data['zip_code'],
                $data['map_link'] ?? null,
                $data['special_instructions'] ?? null,
                $data['active'] ?? true,
                $locationId
            ]
        );
    }

    /**
     * Delete location (only if no tryouts exist)
     */
    public function deleteLocation(int $locationId): bool
    {
        // Check if location has tryouts
        $tryoutCount = $this->db->fetchOne(
            'SELECT COUNT(*) as count FROM tryouts WHERE location_id = ?',
            [$locationId]
        );

        if (($tryoutCount['count'] ?? 0) > 0) {
            return false; // Cannot delete location with existing tryouts
        }

        return $this->db->execute(
            'DELETE FROM tryout_locations WHERE id = ?',
            [$locationId]
        );
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $locationId, bool $active): bool
    {
        return $this->db->execute(
            'UPDATE tryout_locations SET active = ? WHERE id = ?',
            [$active ? 1 : 0, $locationId]
        );
    }

    /**
     * Get all active locations (for dropdowns)
     */
    public function getActiveLocations(): array
    {
        return $this->db->fetchAll(
            'SELECT id, name, city, state FROM tryout_locations WHERE active = 1 ORDER BY name ASC'
        );
    }

    /**
     * Validate location data
     */
    public function validateLocationData(array $data): array
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Location name is required';
        }

        if (empty($data['street_address'])) {
            $errors[] = 'Street address is required';
        }

        if (empty($data['city'])) {
            $errors[] = 'City is required';
        }

        if (empty($data['state'])) {
            $errors[] = 'State is required';
        }

        if (empty($data['zip_code'])) {
            $errors[] = 'ZIP code is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}

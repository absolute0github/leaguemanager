<?php

namespace App\Core;

class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();

        // Auto-detect table name from class name if not set
        if (empty($this->table)) {
            $className = class_basename(static::class);
            $this->table = strtolower($className . 's');
        }
    }

    /**
     * Find a record by ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Get all records
     */
    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    /**
     * Find by a specific column
     */
    public function findBy(string $column, mixed $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$value]);
    }

    /**
     * Find all records where column matches value
     */
    public function findAllBy(string $column, mixed $value): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE $column = ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    /**
     * Create a new record
     */
    public function create(array $data): int|bool
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        return $this->db->insert($sql, array_values($data));
    }

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool
    {
        $updates = [];
        foreach (array_keys($data) as $column) {
            $updates[] = "$column = ?";
        }

        $values = array_values($data);
        $values[] = $id;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, $values);
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Check if record exists
     */
    public function exists(int $id): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]) !== null;
    }

    /**
     * Count all records
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $result = $this->db->fetchOne($sql);
        return $result['count'] ?? 0;
    }

    /**
     * Count records where column matches value
     */
    public function countBy(string $column, mixed $value): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE $column = ?";
        $result = $this->db->fetchOne($sql, [$value]);
        return $result['count'] ?? 0;
    }

    /**
     * Get table name
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * Get primary key
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
}

/**
 * Helper function to get class name without namespace
 */
function class_basename(string $class): string
{
    return basename(str_replace('\\', '/', $class));
}

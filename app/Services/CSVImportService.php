<?php

namespace App\Services;

use App\Core\Database;

class CSVImportService
{
    public Database $db;
    private array $errors = [];
    private array $warnings = [];
    private int $importedCount = 0;
    private int $skippedCount = 0;
    private int $duplicateCount = 0;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Read CSV file and return array of rows
     */
    public function readCsv(string $filePath): array
    {
        if (!file_exists($filePath)) {
            $this->addError("File not found: $filePath");
            return [];
        }

        $rows = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            // Get header row
            $headers = fgetcsv($handle, 0, ',', '"');
            if ($headers === false) {
                $this->addError("Cannot read CSV headers from: $filePath");
                fclose($handle);
                return [];
            }

            // Clean BOM if present and trim/clean all headers
            foreach ($headers as $i => $header) {
                $header = str_replace("\xEF\xBB\xBF", "", $header);
                $header = trim($header);
                // Remove surrounding quotes if present
                $header = trim($header, '"\'');
                $headers[$i] = $header;
            }

            // Read data rows
            $rowNum = 1;
            while (($data = fgetcsv($handle, 0, ',', '"')) !== false) {
                $rowNum++;
                if (empty($data[0])) {
                    continue; // Skip empty rows
                }

                // Combine headers with data
                $row = array_combine($headers, $data);
                if ($row !== false) {
                    $rows[] = $row;
                } else {
                    $this->addWarning("Row $rowNum has mismatched column count");
                }
            }
            fclose($handle);
        } else {
            $this->addError("Cannot open file: $filePath");
        }

        return $rows;
    }

    /**
     * Check if player exists by email
     */
    public function playerExistsByEmail(string $email): array|null
    {
        if (empty($email)) {
            return null;
        }

        $result = $this->db->fetchOne(
            'SELECT * FROM players WHERE email = ?',
            [$email]
        );

        return $result ?: null;
    }

    /**
     * Check if player exists by name and birthdate
     */
    public function playerExistsByNameAndBirthdate(string $firstName, string $lastName, string $birthdate): array|null
    {
        if (empty($firstName) || empty($lastName) || empty($birthdate)) {
            return null;
        }

        $result = $this->db->fetchOne(
            'SELECT * FROM players WHERE first_name = ? AND last_name = ? AND birthdate = ?',
            [$firstName, $lastName, $birthdate]
        );

        return $result ?: null;
    }

    /**
     * Check if parent exists by email
     */
    public function parentExistsByEmail(string $email): array|null
    {
        if (empty($email)) {
            return null;
        }

        $result = $this->db->fetchOne(
            'SELECT * FROM parents WHERE email = ?',
            [$email]
        );

        return $result ?: null;
    }

    /**
     * Create or update player record
     */
    public function createOrUpdatePlayer(array $playerData): int|null
    {
        if (empty($playerData['email'])) {
            $this->addError('Player email is required');
            return null;
        }

        // Check if player exists
        $existing = $this->playerExistsByEmail($playerData['email']);

        if ($existing) {
            // Update existing player
            $updateData = [];
            if (!empty($playerData['first_name'])) $updateData['first_name'] = $playerData['first_name'];
            if (!empty($playerData['last_name'])) $updateData['last_name'] = $playerData['last_name'];
            if (!empty($playerData['phone'])) $updateData['phone'] = $playerData['phone'];
            if (!empty($playerData['birthdate'])) $updateData['birthdate'] = $playerData['birthdate'];
            if (!empty($playerData['street_address'])) $updateData['street_address'] = $playerData['street_address'];
            if (!empty($playerData['city'])) $updateData['city'] = $playerData['city'];
            if (!empty($playerData['state'])) $updateData['state'] = $playerData['state'];
            if (!empty($playerData['zip_code'])) $updateData['zip_code'] = $playerData['zip_code'];
            if (!empty($playerData['age_group'])) $updateData['age_group'] = $playerData['age_group'];
            if (!empty($playerData['shirt_size'])) $updateData['shirt_size'] = $playerData['shirt_size'];
            if (!empty($playerData['primary_position'])) $updateData['primary_position'] = $playerData['primary_position'];
            if (!empty($playerData['secondary_position'])) $updateData['secondary_position'] = $playerData['secondary_position'];
            if (!empty($playerData['school_name'])) $updateData['school_name'] = $playerData['school_name'];
            if (!empty($playerData['grade_level'])) $updateData['grade_level'] = $playerData['grade_level'];
            if (!empty($playerData['registration_source'])) $updateData['registration_source'] = $playerData['registration_source'];
            if (!empty($playerData['registration_status'])) $updateData['registration_status'] = $playerData['registration_status'];

            if (!empty($updateData)) {
                $placeholders = implode(', ', array_map(fn($k) => "$k = ?", array_keys($updateData)));
                $values = array_values($updateData);
                $values[] = $existing['id'];

                $this->db->execute(
                    "UPDATE players SET $placeholders WHERE id = ?",
                    $values
                );
            }

            return $existing['id'];
        } else {
            // Create new player
            $columns = ['email'];
            $values = [$playerData['email']];
            $placeholders = ['?'];

            foreach ($playerData as $key => $value) {
                if ($key !== 'email' && !empty($value)) {
                    $columns[] = $key;
                    $values[] = $value;
                    $placeholders[] = '?';
                }
            }

            $columnStr = implode(', ', $columns);
            $placeholderStr = implode(', ', $placeholders);

            $success = $this->db->execute(
                "INSERT INTO players ($columnStr) VALUES ($placeholderStr)",
                $values
            );

            if (!$success) {
                $this->addError("Failed to insert player: " . $playerData['email']);
                return null;
            }

            return $this->db->lastInsertId();
        }
    }

    /**
     * Create or update parent record
     */
    public function createOrUpdateParent(int $playerId, int $guardianNumber, array $parentData): int|null
    {
        if (empty($parentData['full_name'])) {
            return null;
        }

        // Check if parent exists for this player and guardian number
        $existing = $this->db->fetchOne(
            'SELECT * FROM parents WHERE player_id = ? AND guardian_number = ?',
            [$playerId, $guardianNumber]
        );

        if ($existing) {
            // Update existing parent
            $updateData = [];
            if (!empty($parentData['full_name'])) $updateData['full_name'] = $parentData['full_name'];
            if (!empty($parentData['phone'])) $updateData['phone'] = $parentData['phone'];
            if (!empty($parentData['email'])) $updateData['email'] = $parentData['email'];
            if (isset($parentData['coaching_interest'])) $updateData['coaching_interest'] = $parentData['coaching_interest'];
            if (!empty($parentData['baseball_level_played'])) $updateData['baseball_level_played'] = $parentData['baseball_level_played'];
            if (!empty($parentData['coaching_experience_years'])) $updateData['coaching_experience_years'] = $parentData['coaching_experience_years'];
            if (!empty($parentData['coaching_history'])) $updateData['coaching_history'] = $parentData['coaching_history'];

            if (!empty($updateData)) {
                $placeholders = implode(', ', array_map(fn($k) => "$k = ?", array_keys($updateData)));
                $updateValues = array_values($updateData);
                $updateValues[] = $existing['id'];

                $this->db->execute(
                    "UPDATE parents SET $placeholders WHERE id = ?",
                    $updateValues
                );
            }

            return $existing['id'];
        } else {
            // Create new parent
            $columns = ['player_id', 'guardian_number', 'full_name'];
            $values = [$playerId, $guardianNumber, $parentData['full_name']];
            $placeholders = ['?', '?', '?'];

            foreach ($parentData as $key => $value) {
                if ($key !== 'full_name' && !empty($value)) {
                    $columns[] = $key;
                    $values[] = $value;
                    $placeholders[] = '?';
                }
            }

            $columnStr = implode(', ', $columns);
            $placeholderStr = implode(', ', $placeholders);

            $this->db->execute(
                "INSERT INTO parents ($columnStr) VALUES ($placeholderStr)",
                $values
            );

            return $this->db->lastInsertId();
        }
    }

    /**
     * Normalize age group names (8U, 10U, 12U, etc.)
     */
    public function normalizeAgeGroup(string $ageGroup): string
    {
        $ageGroup = trim(strtoupper($ageGroup));

        // Map common variations
        $mapping = [
            '8U' => '8U',
            '9U' => '9U',
            '10U' => '10U',
            '11U' => '11U',
            '12U' => '12U',
            '13U' => '13U',
            '14U' => '14U',
            '15U' => '15U',
            '16U' => '16U',
            '17U' => '17U',
            '18U' => '18U',
            'U8' => '8U',
            'U9' => '9U',
            'U10' => '10U',
            'U11' => '11U',
            'U12' => '12U',
            'U13' => '13U',
            'U14' => '14U',
            'U15' => '15U',
            'U16' => '16U',
            'U17' => '17U',
            'U18' => '18U',
        ];

        return $mapping[$ageGroup] ?? $ageGroup;
    }

    /**
     * Parse date string to YYYY-MM-DD format
     */
    public function parseDate(string $dateStr): string|null
    {
        if (empty($dateStr)) {
            return null;
        }

        $dateStr = trim($dateStr);

        // Try multiple date formats
        $formats = [
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'M d, Y',
            'Y/m/d',
            'm-d-Y',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateStr);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        // Try parsing with php's strtotime
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    /**
     * Normalize phone number
     */
    public function normalizePhone(string $phone): string
    {
        return preg_replace('/[^0-9]/', '', $phone);
    }

    /**
     * Add error message
     */
    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Add warning message
     */
    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * Get all errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get all warnings
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get import statistics
     */
    public function getStats(): array
    {
        return [
            'imported' => $this->importedCount,
            'skipped' => $this->skippedCount,
            'duplicates' => $this->duplicateCount,
            'errors' => count($this->errors),
            'warnings' => count($this->warnings),
        ];
    }

    /**
     * Increment imported counter
     */
    public function incrementImported(): void
    {
        $this->importedCount++;
    }

    /**
     * Increment skipped counter
     */
    public function incrementSkipped(): void
    {
        $this->skippedCount++;
    }

    /**
     * Increment duplicate counter
     */
    public function incrementDuplicate(): void
    {
        $this->duplicateCount++;
    }

    /**
     * Reset all counters and messages
     */
    public function reset(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->importedCount = 0;
        $this->skippedCount = 0;
        $this->duplicateCount = 0;
    }
}

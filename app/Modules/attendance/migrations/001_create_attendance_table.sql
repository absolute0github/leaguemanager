-- Attendance Module Migration
-- Creates the attendance tracking table

CREATE TABLE IF NOT EXISTS mod_attendance_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    player_id INT UNSIGNED NOT NULL,
    event_type ENUM('practice', 'game', 'other') DEFAULT 'practice',
    event_date DATE NOT NULL,
    event_name VARCHAR(255) NULL,
    status ENUM('present', 'absent', 'excused', 'late') DEFAULT 'present',
    notes TEXT NULL,
    marked_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_team_date (team_id, event_date),
    INDEX idx_player (player_id),
    INDEX idx_event_date (event_date),

    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table for scheduling practices/games
CREATE TABLE IF NOT EXISTS mod_attendance_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    event_type ENUM('practice', 'game', 'other') DEFAULT 'practice',
    event_name VARCHAR(255) NULL,
    event_date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    location VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_team_date (team_id, event_date),

    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

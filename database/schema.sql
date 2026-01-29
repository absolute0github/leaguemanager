-- ============================================
-- USERS & AUTHENTICATION
-- ============================================

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('superuser', 'admin', 'coach', 'player') NOT NULL DEFAULT 'player',
    status ENUM('active', 'inactive', 'pending') NOT NULL DEFAULT 'pending',
    email_verified BOOLEAN DEFAULT FALSE,
    email_verification_token VARCHAR(64) NULL,
    password_reset_token VARCHAR(64) NULL,
    password_reset_expires DATETIME NULL,
    last_login DATETIME NULL,
    failed_login_attempts TINYINT UNSIGNED DEFAULT 0,
    lockout_until DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,

    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS two_factor_auth (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    secret VARCHAR(32) NOT NULL,
    enabled BOOLEAN DEFAULT FALSE,
    backup_codes JSON NULL COMMENT 'Array of hashed backup codes',
    last_used DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255) NULL,
    payload TEXT NOT NULL,
    last_activity INT UNSIGNED NOT NULL,
    two_factor_verified BOOLEAN DEFAULT FALSE,

    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PLAYERS & PARENTS
-- ============================================

CREATE TABLE IF NOT EXISTS players (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL COMMENT 'Link to user account if registered',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NULL,
    birthdate DATE NULL,
    age_group VARCHAR(10) NULL COMMENT 'e.g., 10U, 12U, 14U',

    -- Address
    street_address VARCHAR(255) NULL,
    address_line2 VARCHAR(100) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(50) NULL,
    zip_code VARCHAR(20) NULL,

    -- Player details
    shirt_size VARCHAR(20) NULL,
    primary_position VARCHAR(50) NULL,
    secondary_position VARCHAR(50) NULL,
    school_name VARCHAR(255) NULL,
    grade_level TINYINT UNSIGNED NULL,
    previous_team VARCHAR(255) NULL,

    -- Status
    registration_status ENUM('tryout', 'committed', 'active', 'inactive') DEFAULT 'tryout',
    registration_source ENUM('tryout_form', 'commitment_form', 'manual', 'self_registration') NULL,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,

    INDEX idx_email (email),
    INDEX idx_name (last_name, first_name),
    INDEX idx_age_group (age_group),
    INDEX idx_registration_status (registration_status),
    INDEX idx_birthdate (birthdate),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS parents (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNSIGNED NOT NULL,
    guardian_number TINYINT UNSIGNED NOT NULL COMMENT '1 or 2',
    full_name VARCHAR(200) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,

    -- Coaching interest
    coaching_interest BOOLEAN DEFAULT FALSE,
    baseball_level_played VARCHAR(100) NULL,
    coaching_experience_years TINYINT UNSIGNED NULL,
    coaching_history TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_player_id (player_id),
    INDEX idx_email (email),
    UNIQUE KEY unique_player_guardian (player_id, guardian_number),
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- LEAGUES, TEAMS & COACHES
-- ============================================

CREATE TABLE IF NOT EXISTS leagues (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    season VARCHAR(50) NOT NULL COMMENT 'e.g., Spring 2026, Fall 2026',
    year YEAR NOT NULL,
    description TEXT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    status ENUM('planning', 'registration_open', 'active', 'completed', 'cancelled') DEFAULT 'planning',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,

    INDEX idx_year (year),
    INDEX idx_season (season),
    INDEX idx_status (status),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS teams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    league_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    age_group VARCHAR(10) NOT NULL,
    max_players TINYINT UNSIGNED DEFAULT 15,
    status ENUM('forming', 'active', 'inactive') DEFAULT 'forming',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,

    INDEX idx_league_id (league_id),
    INDEX idx_age_group (age_group),
    INDEX idx_status (status),
    FOREIGN KEY (league_id) REFERENCES leagues(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coaches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    team_id INT UNSIGNED NULL COMMENT 'Current team assignment',
    coach_type ENUM('head', 'assistant', 'volunteer') DEFAULT 'head',
    certification_level VARCHAR(100) NULL,
    background_check_date DATE NULL,
    background_check_status ENUM('pending', 'approved', 'denied') NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_team_id (team_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS team_players (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_id INT UNSIGNED NOT NULL,
    player_id INT UNSIGNED NOT NULL,
    jersey_number TINYINT UNSIGNED NULL,
    status ENUM('active', 'inactive', 'injured') DEFAULT 'active',
    joined_date DATE NOT NULL,
    left_date DATE NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY unique_team_player (team_id, player_id),
    INDEX idx_team_id (team_id),
    INDEX idx_player_id (player_id),
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TRYOUTS
-- ============================================

CREATE TABLE IF NOT EXISTS tryout_locations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    street_address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    map_link VARCHAR(500) NULL,
    special_instructions TEXT NULL,
    active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,

    INDEX idx_active (active),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tryouts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    location_id INT UNSIGNED NOT NULL,
    age_group VARCHAR(10) NOT NULL,
    tryout_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_participants SMALLINT UNSIGNED NULL,
    current_participants SMALLINT UNSIGNED DEFAULT 0,
    status ENUM('scheduled', 'open', 'closed', 'completed', 'cancelled') DEFAULT 'scheduled',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NULL,

    INDEX idx_location_id (location_id),
    INDEX idx_age_group (age_group),
    INDEX idx_tryout_date (tryout_date),
    INDEX idx_status (status),
    FOREIGN KEY (location_id) REFERENCES tryout_locations(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tryout_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tryout_id INT UNSIGNED NOT NULL,
    player_id INT UNSIGNED NOT NULL,
    registration_date DATETIME NOT NULL,
    payment_status ENUM('pending', 'paid', 'waived', 'refunded') DEFAULT 'pending',
    payment_amount DECIMAL(10,2) NULL,
    payment_transaction_id VARCHAR(100) NULL,
    attendance_status ENUM('registered', 'attended', 'no_show', 'cancelled') DEFAULT 'registered',
    notes TEXT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_tryout_player (tryout_id, player_id),
    INDEX idx_tryout_id (tryout_id),
    INDEX idx_player_id (player_id),
    INDEX idx_payment_status (payment_status),
    FOREIGN KEY (tryout_id) REFERENCES tryouts(id) ON DELETE CASCADE,
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- WAIVERS & AGREEMENTS
-- ============================================

CREATE TABLE IF NOT EXISTS waiver_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    content TEXT NOT NULL COMMENT 'Full waiver text',
    version VARCHAR(20) NOT NULL DEFAULT '1.0',
    active BOOLEAN DEFAULT TRUE,
    effective_date DATE NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS signed_waivers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNSIGNED NOT NULL,
    waiver_type_id INT UNSIGNED NOT NULL,
    signer_name VARCHAR(200) NOT NULL,
    signer_type ENUM('player', 'parent_guardian') NOT NULL,
    signature_data TEXT NULL COMMENT 'Base64 encoded signature image',
    signature_date DATETIME NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_player_id (player_id),
    INDEX idx_waiver_type_id (waiver_type_id),
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (waiver_type_id) REFERENCES waiver_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PAYMENTS & COMMITMENTS
-- ============================================

CREATE TABLE IF NOT EXISTS payment_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    age_group VARCHAR(10) NOT NULL,
    plan_type ENUM('full', 'installment') NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    installment_count TINYINT UNSIGNED NULL,
    installment_amount DECIMAL(10,2) NULL,
    description TEXT NULL,
    active BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_age_group (age_group),
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS player_payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT UNSIGNED NOT NULL,
    payment_plan_id INT UNSIGNED NULL,
    payment_type ENUM('tryout_fee', 'registration', 'subscription', 'one_time', 'other') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    transaction_id VARCHAR(100) NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded', 'active') NOT NULL,
    payment_method VARCHAR(50) NULL COMMENT 'e.g., Stripe, check, cash',
    payment_date DATETIME NULL,
    notes TEXT NULL,

    -- Subscription tracking (for installment plans)
    subscription_id VARCHAR(100) NULL,
    subscription_status VARCHAR(50) NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_player_id (player_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_transaction_id (transaction_id),
    FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE,
    FOREIGN KEY (payment_plan_id) REFERENCES payment_plans(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SYSTEM & AUDIT
-- ============================================

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NULL,
    record_id INT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_table_name (table_name),
    INDEX idx_created_at (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(200) NULL,
    subject VARCHAR(500) NOT NULL,
    body TEXT NULL,
    template_name VARCHAR(100) NULL,
    status ENUM('pending', 'sent', 'failed') NOT NULL,
    error_message TEXT NULL,
    sent_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_recipient_email (recipient_email),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS modules (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    version VARCHAR(20) NOT NULL,
    enabled BOOLEAN DEFAULT FALSE,
    settings JSON NULL,
    installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_enabled (enabled)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

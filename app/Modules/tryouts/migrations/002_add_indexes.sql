-- Tryouts Module Migration: Add Performance Indexes
-- This migration adds composite indexes for common query patterns

-- Index for browsing available tryouts (player view)
CREATE INDEX idx_tryouts_browse ON tryouts(status, tryout_date, age_group);

-- Index for upcoming tryouts queries
CREATE INDEX idx_tryouts_upcoming ON tryouts(tryout_date, status);

-- Index for registration status queries
CREATE INDEX idx_registrations_status ON tryout_registrations(tryout_id, attendance_status, payment_status);

-- Index for active locations queries
CREATE INDEX idx_locations_active ON tryout_locations(active, city, state);

-- Tryouts Module Migration: Add Waitlist Support
-- This migration adds waitlist tracking columns to tryout_registrations
-- and reminder tracking to tryouts table

-- Add waitlist position tracking to registrations
ALTER TABLE tryout_registrations
ADD COLUMN waitlist_position SMALLINT UNSIGNED NULL AFTER attendance_status,
ADD COLUMN waitlist_notified_at DATETIME NULL AFTER waitlist_position,
ADD COLUMN promoted_from_waitlist BOOLEAN DEFAULT FALSE AFTER waitlist_notified_at;

-- Add index for waitlist ordering (FIFO by registration_date)
CREATE INDEX idx_waitlist ON tryout_registrations(tryout_id, waitlist_position, registration_date);

-- Add reminder tracking to tryouts
ALTER TABLE tryouts
ADD COLUMN reminder_sent BOOLEAN DEFAULT FALSE AFTER status,
ADD COLUMN reminder_sent_at DATETIME NULL AFTER reminder_sent;

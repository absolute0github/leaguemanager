-- Migration: Make birthdate nullable in players table
-- This fixes the issue where new self-registrations fail because birthdate is NOT NULL
-- but the registration form doesn't collect birthdate

ALTER TABLE players MODIFY COLUMN birthdate DATE NULL;

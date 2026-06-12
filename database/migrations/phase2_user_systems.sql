-- FarmConnect Kenya - Phase 2: User systems
-- Run once: mysql -u root farmconnect_kenya < database/migrations/phase2_user_systems.sql

USE farmconnect_kenya;

ALTER TABLE farmers
    ADD COLUMN farming_location VARCHAR(200) NULL AFTER county,
    ADD COLUMN profile_image VARCHAR(255) NULL AFTER farming_location;

-- Copy existing county into farming_location where empty (optional migration)
UPDATE farmers
SET farming_location = county
WHERE farming_location IS NULL AND county IS NOT NULL AND county != '';

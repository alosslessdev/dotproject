-- Add TOTP fields to users table
ALTER TABLE `dotp_users`
ADD COLUMN `user_totp_secret` VARCHAR(32) NULL DEFAULT NULL COMMENT 'TOTP secret key',
ADD COLUMN `user_totp_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Whether TOTP 2FA is enabled';

-- Add index for faster lookups
CREATE INDEX idx_user_totp ON `dotp_users` (user_id, user_totp_enabled);

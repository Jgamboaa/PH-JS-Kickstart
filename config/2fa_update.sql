ALTER TABLE `admin` 
ADD COLUMN `tfa_secret` VARCHAR(100) NULL AFTER `admin_estado`,
ADD COLUMN `tfa_enabled` TINYINT(1) NOT NULL DEFAULT 0 AFTER `tfa_secret`,
ADD COLUMN `tfa_backup_codes` TEXT NULL AFTER `tfa_enabled`;

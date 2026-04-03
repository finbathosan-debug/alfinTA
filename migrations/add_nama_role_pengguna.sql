-- Migration: add_nama_role_pengguna.sql
-- Adds nama_alfin and role_alfin to pengguna_alfin
-- For MySQL 8.0+ the IF NOT EXISTS form is supported. If using older MySQL, run the ALTER statements and ignore "Duplicate column" errors if columns already exist.

ALTER TABLE pengguna_alfin
  ADD COLUMN IF NOT EXISTS nama_alfin VARCHAR(255) NOT NULL AFTER id;

ALTER TABLE pengguna_alfin
  ADD COLUMN IF NOT EXISTS role_alfin ENUM('admin','kasir') NOT NULL DEFAULT 'kasir' AFTER password_alfin;

-- Verify
SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_DEFAULT
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME = 'pengguna_alfin'
  AND COLUMN_NAME IN ('nama_alfin','role_alfin');

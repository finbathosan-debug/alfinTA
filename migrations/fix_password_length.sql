-- Migration: fix_password_length.sql
-- Fixes password_alfin column length to support password hashing
-- Run this after importing db_taalfin.sql

ALTER TABLE pengguna_alfin
  MODIFY COLUMN password_alfin VARCHAR(255) NOT NULL;
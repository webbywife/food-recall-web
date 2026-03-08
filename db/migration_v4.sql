-- ============================================================
-- Migration v4 — Add household_size field
-- Number of household members (used for per-capita calculations)
-- ============================================================

USE food_recall_db;

ALTER TABLE households
  ADD COLUMN household_size INT DEFAULT NULL AFTER address;

-- Sprint 4 fixes: sub-notes, price_range homogenization
-- Run: mysql -u root lebonresto < database/sprint4_fixes.sql
-- NOTE: Already executed 2026-02-16. Safe to re-run (idempotent).

-- 1. Fill missing sub-notes with note_globale value
UPDATE reviews
SET note_nourriture = note_globale,
    note_service = note_globale,
    note_ambiance = note_globale,
    note_prix = note_globale
WHERE (note_nourriture IS NULL OR note_nourriture = 0)
  AND note_globale IS NOT NULL AND note_globale > 0;

-- 2. Homogenize price_range to EUR format
-- Fix any remaining $ signs (idempotent: won't match if already €)
SET NAMES utf8mb4;
UPDATE restaurants SET price_range = CONCAT(CHAR(0xE2, 0x82, 0xAC USING utf8mb4)) WHERE price_range = '$';
UPDATE restaurants SET price_range = CONCAT(CHAR(0xE2, 0x82, 0xAC USING utf8mb4), CHAR(0xE2, 0x82, 0xAC USING utf8mb4)) WHERE price_range = '$$';
UPDATE restaurants SET price_range = CONCAT(CHAR(0xE2, 0x82, 0xAC USING utf8mb4), CHAR(0xE2, 0x82, 0xAC USING utf8mb4), CHAR(0xE2, 0x82, 0xAC USING utf8mb4)) WHERE price_range = '$$$';

-- Fix double-encoded values (from previous bad migration)
UPDATE restaurants SET price_range = CONCAT(CHAR(0xE2, 0x82, 0xAC USING utf8mb4), CHAR(0xE2, 0x82, 0xAC USING utf8mb4), CHAR(0xE2, 0x82, 0xAC USING utf8mb4)) WHERE HEX(price_range) = 'C394C3A9C2BCC394C3A9C2BCC394C3A9C2BC';
UPDATE restaurants SET price_range = CONCAT(CHAR(0xE2, 0x82, 0xAC USING utf8mb4), CHAR(0xE2, 0x82, 0xAC USING utf8mb4)) WHERE HEX(price_range) = 'C394C3A9C2BCC394C3A9C2BC';
UPDATE restaurants SET price_range = CHAR(0xE2, 0x82, 0xAC USING utf8mb4) WHERE HEX(price_range) = 'C394C3A9C2BC';

-- 3. Clean up ?? placeholder
UPDATE restaurants SET price_range = NULL WHERE price_range = '??' OR HEX(price_range) = '3F3F';

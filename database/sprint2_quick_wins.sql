-- ═══════════════════════════════════════════════════════════════
-- Sprint 2 - Quick Wins Database Migration
-- Features: F1 (DZD prices), F3 (reactions), F5 (PMR already done),
--           F6 (pros/cons), F7 (VIP badge), F8 (double points)
-- ═══════════════════════════════════════════════════════════════

-- ── F1: Fourchette de prix en DZD (prep colonnes) ──
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS prix_min INT UNSIGNED NULL COMMENT 'Prix minimum en DZD';
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS prix_max INT UNSIGNED NULL COMMENT 'Prix maximum en DZD';

-- ── F3: Reactions multiples sur avis ──
-- Modify review_votes to support reaction types
ALTER TABLE review_votes ADD COLUMN IF NOT EXISTS reaction_type ENUM('useful', 'funny', 'love') NOT NULL DEFAULT 'useful';

-- Drop the old unique constraint and add new one with reaction_type
-- (safe: only adds if not exists)
ALTER TABLE review_votes DROP INDEX IF EXISTS idx_review_user;
ALTER TABLE review_votes ADD UNIQUE INDEX idx_review_user_reaction (review_id, user_id, reaction_type);

-- Add reaction counters to reviews table for fast display
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS votes_funny INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS votes_love INT UNSIGNED NOT NULL DEFAULT 0;

-- ── F6: Avis structure Pros/Cons ──
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS pros TEXT NULL COMMENT 'Points forts';
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS cons TEXT NULL COMMENT 'Points faibles';

-- ── F7: Badge VIP client fidele (par restaurant) ──
-- Track visit frequency per restaurant per user
CREATE TABLE IF NOT EXISTS user_restaurant_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    visit_count INT UNSIGNED NOT NULL DEFAULT 1,
    last_visit_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_user_resto (user_id, restaurant_id),
    INDEX idx_restaurant (restaurant_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Populate from existing reviews (each approved review = 1 visit)
INSERT IGNORE INTO user_restaurant_visits (user_id, restaurant_id, visit_count, last_visit_at)
SELECT user_id, restaurant_id, COUNT(*) as visit_count, MAX(created_at)
FROM reviews
WHERE status = 'approved'
GROUP BY user_id, restaurant_id;

-- ── F8: Double points temporaires ──
CREATE TABLE IF NOT EXISTS points_multipliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    multiplier DECIMAL(3,1) NOT NULL DEFAULT 2.0,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    description VARCHAR(255) NOT NULL,
    created_by INT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dates (start_date, end_date),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── F15: Galerie photos par categorie (no DB changes needed, uses existing ai_labels/category) ──

-- ═══════════════════════════════════════════════════════════════
-- Indexes for performance
-- ═══════════════════════════════════════════════════════════════
CREATE INDEX IF NOT EXISTS idx_reviews_pros_cons ON reviews(id) WHERE pros IS NOT NULL OR cons IS NOT NULL;

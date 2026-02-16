-- ================================================================
-- PHASE 17: AUDIT FIXES + PERFORMANCE + NEW FEATURES
-- LeBonResto - Migration SQL
-- Date: 2026-02-14
-- ================================================================

-- ================================================================
-- 1. MISSING INDEXES (Performance)
-- ================================================================

-- Reviews: Most common query pattern is (restaurant_id, status)
CREATE INDEX IF NOT EXISTS idx_reviews_restaurant_status ON reviews(restaurant_id, status);
CREATE INDEX IF NOT EXISTS idx_reviews_user_status ON reviews(user_id, status);
CREATE INDEX IF NOT EXISTS idx_reviews_created ON reviews(created_at);

-- Users: Login query
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Activity feed: Timeline queries
CREATE INDEX IF NOT EXISTS idx_activity_feed_created ON activity_feed(created_at);
CREATE INDEX IF NOT EXISTS idx_activity_feed_user ON activity_feed(user_id, created_at);

-- Checkins: Used in review verification batch query
CREATE INDEX IF NOT EXISTS idx_checkins_user_restaurant ON checkins(user_id, restaurant_id, created_at);

-- Notifications: User bell queries
CREATE INDEX IF NOT EXISTS idx_notifications_user ON notifications(user_id, is_read, created_at);

-- Restaurant horaires: Owner dashboard
CREATE INDEX IF NOT EXISTS idx_horaires_restaurant ON restaurant_horaires(restaurant_id, jour_semaine);

-- Reservations: Status queries
CREATE INDEX IF NOT EXISTS idx_reservations_restaurant_status ON reservations(restaurant_id, status);
CREATE INDEX IF NOT EXISTS idx_reservations_user ON reservations(user_id, status);

-- User follows: Feed queries
CREATE INDEX IF NOT EXISTS idx_follows_follower ON user_follows(follower_id);
CREATE INDEX IF NOT EXISTS idx_follows_followed ON user_follows(followed_id);

-- ================================================================
-- 2. RESTAURANT OFFERS TABLE (TheFork-style deals)
-- ================================================================

CREATE TABLE IF NOT EXISTS restaurant_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    discount_percent INT DEFAULT 0,
    offer_type ENUM('discount','happy_hour','special_menu','free_item') DEFAULT 'discount',
    valid_from DATE,
    valid_to DATE,
    days_of_week VARCHAR(50) DEFAULT NULL COMMENT 'JSON: [0,1,2,3,4,5,6]',
    time_start TIME DEFAULT NULL,
    time_end TIME DEFAULT NULL,
    conditions TEXT,
    max_uses INT DEFAULT NULL,
    current_uses INT DEFAULT 0,
    status ENUM('active','paused','expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_offers_restaurant (restaurant_id, status),
    INDEX idx_offers_active (status, valid_from, valid_to)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
-- 3. RECENTLY VIEWED (server-side for logged-in users)
-- ================================================================

CREATE TABLE IF NOT EXISTS user_recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_resto (user_id, restaurant_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    INDEX idx_recent_user (user_id, viewed_at DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
-- 4. REVIEW SUMMARIES (AI-generated keyword extraction)
-- ================================================================

CREATE TABLE IF NOT EXISTS restaurant_review_summaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL UNIQUE,
    positive_keywords TEXT COMMENT 'JSON array of top praised aspects',
    negative_keywords TEXT COMMENT 'JSON array of top criticisms',
    cuisine_score DECIMAL(3,1) DEFAULT NULL,
    service_score DECIMAL(3,1) DEFAULT NULL,
    ambiance_score DECIMAL(3,1) DEFAULT NULL,
    price_score DECIMAL(3,1) DEFAULT NULL,
    summary_text TEXT,
    review_count INT DEFAULT 0,
    last_computed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================================
-- 5. USER PREFERENCES (for onboarding + match score)
-- ================================================================

ALTER TABLE users ADD COLUMN IF NOT EXISTS preferred_cuisines TEXT DEFAULT NULL COMMENT 'JSON array';
ALTER TABLE users ADD COLUMN IF NOT EXISTS preferred_price_range VARCHAR(20) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS onboarding_completed TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS dark_mode TINYINT(1) DEFAULT 0;

-- ================================================================
-- 6. DIETARY OPTIONS (restaurant-level)
-- ================================================================

-- Add dietary options to restaurant_options if not exists
INSERT IGNORE INTO restaurant_options (restaurant_id, option_name)
SELECT r.id, 'vegetarien' FROM restaurants r WHERE 0; -- Template only, no actual insert

-- ================================================================
-- 7. RESERVATION REMINDERS TRACKING
-- ================================================================

ALTER TABLE reservations ADD COLUMN IF NOT EXISTS reminder_24h_sent TINYINT(1) DEFAULT 0;
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS reminder_2h_sent TINYINT(1) DEFAULT 0;

-- ================================================================
-- 8. MENU ITEM PHOTOS
-- ================================================================

ALTER TABLE restaurant_menu_items ADD COLUMN IF NOT EXISTS photo_path VARCHAR(500) DEFAULT NULL;

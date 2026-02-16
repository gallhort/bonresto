-- ═══════════════════════════════════════════════════════════════
-- PHASE 13 - FEATURES NICE-TO-HAVE
-- Tags avis, Follow, Recherche avis, Plats populaires,
-- Programme parrainage, QR Code, Quick Tips
-- ═══════════════════════════════════════════════════════════════

-- 1. REVIEW TAGS (tags sur les avis)
CREATE TABLE IF NOT EXISTS review_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    INDEX idx_review (review_id),
    INDEX idx_tag (tag),
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. RESTAURANT CONTEXT TAGS (aggregated from review_tags)
CREATE TABLE IF NOT EXISTS restaurant_context_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    tag VARCHAR(50) NOT NULL,
    vote_count INT DEFAULT 1,
    UNIQUE KEY idx_resto_tag (restaurant_id, tag),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_count (vote_count),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. USER FOLLOWS
CREATE TABLE IF NOT EXISTS user_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    followed_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_pair (follower_id, followed_id),
    INDEX idx_follower (follower_id),
    INDEX idx_followed (followed_id),
    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (followed_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. REFERRALS (programme parrainage)
CREATE TABLE IF NOT EXISTS referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    status ENUM('pending','completed') DEFAULT 'pending',
    points_awarded INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    UNIQUE KEY idx_referred (referred_id),
    INDEX idx_referrer (referrer_id),
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add referral_code to users
ALTER TABLE users ADD COLUMN referral_code VARCHAR(20) DEFAULT NULL AFTER bio;
ALTER TABLE users ADD UNIQUE KEY idx_referral_code (referral_code);

-- 5. QUICK TIPS (conseils courts)
CREATE TABLE IF NOT EXISTS restaurant_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    message VARCHAR(200) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'approved',
    votes INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant (restaurant_id, status),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. FULLTEXT INDEX on reviews for search
ALTER TABLE reviews ADD FULLTEXT INDEX idx_fulltext_message (message);

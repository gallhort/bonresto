-- ═══════════════════════════════════════════════════════════════
-- PHASE 9 - AMÉLIORATIONS TRIPADVISOR-LEVEL
-- Contact, Awards, Moderation Log, Cleanup
-- ═══════════════════════════════════════════════════════════════

-- 1. Table messages de contact
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    admin_notes TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    replied_at DATETIME DEFAULT NULL,
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table awards/badges restaurants (Traveler's Choice, Top de la ville, etc.)
CREATE TABLE IF NOT EXISTS restaurant_awards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    award_type ENUM('travelers_choice', 'top_city', 'best_cuisine', 'trending', 'newcomer') NOT NULL,
    award_year YEAR NOT NULL,
    city VARCHAR(100) DEFAULT NULL,
    cuisine_type VARCHAR(100) DEFAULT NULL,
    rank_position INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_restaurant_award (restaurant_id, award_type, award_year),
    INDEX idx_award_type_year (award_type, award_year),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table journal de modération (audit log)
CREATE TABLE IF NOT EXISTS moderation_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action ENUM('approve_review', 'reject_review', 'delete_review', 'approve_restaurant', 'reject_restaurant', 'ban_user', 'unban_user', 'edit_restaurant', 'respond_report') NOT NULL,
    target_type ENUM('review', 'restaurant', 'user', 'report') NOT NULL,
    target_id INT NOT NULL,
    reason TEXT DEFAULT NULL,
    details JSON DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin (admin_id),
    INDEX idx_target (target_type, target_id),
    INDEX idx_created (created_at),
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table signalements améliorée (si n'existe pas)
CREATE TABLE IF NOT EXISTS review_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    reporter_id INT DEFAULT NULL,
    reason ENUM('spam', 'offensive', 'fake', 'harassment', 'personal', 'copyright', 'other') NOT NULL,
    details TEXT DEFAULT NULL,
    status ENUM('pending', 'reviewed', 'action_taken', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME DEFAULT NULL,
    INDEX idx_review (review_id),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Attribution automatique des awards
-- Travelers' Choice: restaurants avec note >= 4.5 et >= 10 avis
INSERT IGNORE INTO restaurant_awards (restaurant_id, award_type, award_year, city)
SELECT r.id, 'travelers_choice', YEAR(CURDATE()), r.ville
FROM restaurants r
WHERE r.status = 'validated'
  AND r.note_moyenne >= 4.5
  AND (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') >= 10;

-- Top City: top 3 par ville (note >= 4.0 et >= 5 avis)
INSERT IGNORE INTO restaurant_awards (restaurant_id, award_type, award_year, city, rank_position)
SELECT r.id, 'top_city', YEAR(CURDATE()), r.ville, ranking
FROM (
    SELECT r.id, r.ville,
           ROW_NUMBER() OVER (PARTITION BY r.ville ORDER BY r.note_moyenne DESC) as ranking
    FROM restaurants r
    WHERE r.status = 'validated'
      AND r.note_moyenne >= 4.0
      AND (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') >= 5
) AS r
WHERE ranking <= 3;

-- Best Cuisine: top par type de cuisine
INSERT IGNORE INTO restaurant_awards (restaurant_id, award_type, award_year, cuisine_type, rank_position)
SELECT r.id, 'best_cuisine', YEAR(CURDATE()), r.type_cuisine, ranking
FROM (
    SELECT r.id, r.type_cuisine,
           ROW_NUMBER() OVER (PARTITION BY r.type_cuisine ORDER BY r.note_moyenne DESC) as ranking
    FROM restaurants r
    WHERE r.status = 'validated'
      AND r.note_moyenne >= 4.0
      AND r.type_cuisine IS NOT NULL
      AND (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') >= 5
) AS r
WHERE ranking = 1;

-- Newcomer: restaurants ajoutés dans les 3 derniers mois avec bonne note
INSERT IGNORE INTO restaurant_awards (restaurant_id, award_type, award_year)
SELECT r.id, 'newcomer', YEAR(CURDATE())
FROM restaurants r
WHERE r.status = 'validated'
  AND r.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
  AND r.note_moyenne >= 4.0
  AND (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') >= 3;

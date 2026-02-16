-- ═══════════════════════════════════════════════════════════════════════════
-- MIGRATION COMPLÈTE - Phases 9 + 10
-- Exécuter ce fichier pour créer toutes les tables manquantes
-- ═══════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────
-- PHASE 9 - Tables
-- ─────────────────────────────────────────────────────────────────────────

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

-- ─────────────────────────────────────────────────────────────────────────
-- PHASE 10 - Tables et colonnes
-- ─────────────────────────────────────────────────────────────────────────

-- Fix horaires (SKIP si déjà en 0-6 — exécuter seulement si jour_semaine contient 7)
-- Pour vérifier : SELECT DISTINCT jour_semaine FROM restaurant_horaires ORDER BY jour_semaine;
-- Si résultat = 0,1,2,3,4,5,6 → déjà OK, ne rien faire
-- Si résultat = 1,2,3,4,5,6,7 → exécuter les 4 lignes ci-dessous :
-- UPDATE restaurant_horaires SET jour_semaine = jour_semaine - 1 WHERE jour_semaine BETWEEN 1 AND 7;
-- DELETE FROM restaurant_horaires WHERE jour_semaine < 0 OR jour_semaine > 6;
-- DELETE rh1 FROM restaurant_horaires rh1
-- INNER JOIN restaurant_horaires rh2
-- ON rh1.restaurant_id = rh2.restaurant_id AND rh1.jour_semaine = rh2.jour_semaine AND rh1.id > rh2.id;
-- UPDATE restaurant_horaires SET ouverture_matin = NULL, fermeture_matin = NULL, ouverture_soir = NULL, fermeture_soir = NULL WHERE ouverture_matin = '00:00:00' AND fermeture_soir = '23:59:00';

-- Email verification
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verification_token VARCHAR(64) NULL;
UPDATE users SET email_verified = 1 WHERE email_verified = 0;

CREATE TABLE IF NOT EXISTS email_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    verified_at DATETIME NULL,
    INDEX idx_token (token),
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Q&A
CREATE TABLE IF NOT EXISTS restaurant_qa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    question TEXT NOT NULL,
    status ENUM('active', 'hidden', 'reported') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_user (user_id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS restaurant_qa_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    answer TEXT NOT NULL,
    is_owner_answer TINYINT(1) NOT NULL DEFAULT 0,
    votes INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_question (question_id),
    FOREIGN KEY (question_id) REFERENCES restaurant_qa(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Review edit tracking
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS edited_at DATETIME NULL;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS edit_count INT NOT NULL DEFAULT 0;

-- Notifications (si pas déjà créée)
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT DEFAULT NULL,
    data JSON DEFAULT NULL,
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_read (user_id, read_at),
    INDEX idx_type (type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Index amenities
-- ALTER TABLE restaurant_options ADD INDEX IF NOT EXISTS idx_resto_option (restaurant_id, option_name);

-- Awards auto-attribution
INSERT IGNORE INTO restaurant_awards (restaurant_id, award_type, award_year, city)
SELECT r.id, 'travelers_choice', YEAR(CURDATE()), r.ville
FROM restaurants r
WHERE r.status = 'validated'
  AND r.note_moyenne >= 4.5
  AND (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') >= 10;

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

INSERT IGNORE INTO restaurant_awards (restaurant_id, award_type, award_year)
SELECT r.id, 'newcomer', YEAR(CURDATE())
FROM restaurants r
WHERE r.status = 'validated'
  AND r.created_at >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
  AND r.note_moyenne >= 4.0
  AND (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') >= 3;

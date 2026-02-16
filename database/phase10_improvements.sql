-- ═══════════════════════════════════════════════════════════════════════════
-- PHASE 10 - MIGRATION SQL
-- Horaires fix, Email verification, Review edit, Q&A, Amenities filter
-- ═══════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────
-- 1. FIX HORAIRES - Normaliser jour_semaine (1-7 → 0-6)
-- ─────────────────────────────────────────────────────────────────────────
UPDATE restaurant_horaires SET jour_semaine = jour_semaine - 1 WHERE jour_semaine BETWEEN 1 AND 7;

-- Supprimer les lignes avec jour_semaine invalide
DELETE FROM restaurant_horaires WHERE jour_semaine < 0 OR jour_semaine > 6;

-- Supprimer les doublons (même restaurant, même jour) - garder le plus récent
DELETE rh1 FROM restaurant_horaires rh1
INNER JOIN restaurant_horaires rh2
ON rh1.restaurant_id = rh2.restaurant_id AND rh1.jour_semaine = rh2.jour_semaine AND rh1.id > rh2.id;

-- Nettoyer les horaires placeholder (00:00 - 23:59 = pas de vraies données)
UPDATE restaurant_horaires
SET ouverture_matin = NULL, fermeture_matin = NULL,
    ouverture_soir = NULL, fermeture_soir = NULL
WHERE ouverture_matin = '00:00:00' AND fermeture_soir = '23:59:00';

-- ─────────────────────────────────────────────────────────────────────────
-- 2. EMAIL VERIFICATION
-- ─────────────────────────────────────────────────────────────────────────
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verification_token VARCHAR(64) NULL;

-- Marquer les utilisateurs existants comme vérifiés (pour ne pas les bloquer)
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

-- ─────────────────────────────────────────────────────────────────────────
-- 3. Q&A SECTION (Questions & Réponses)
-- ─────────────────────────────────────────────────────────────────────────
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

-- ─────────────────────────────────────────────────────────────────────────
-- 4. REVIEW EDIT TRACKING
-- ─────────────────────────────────────────────────────────────────────────
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS edited_at DATETIME NULL;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS edit_count INT NOT NULL DEFAULT 0;

-- ─────────────────────────────────────────────────────────────────────────
-- 5. INDEX pour les filtres amenities (restaurant_options)
-- ─────────────────────────────────────────────────────────────────────────
-- La table restaurant_options existe déjà, ajouter un index composite
ALTER TABLE restaurant_options ADD INDEX IF NOT EXISTS idx_resto_option (restaurant_id, option_name);

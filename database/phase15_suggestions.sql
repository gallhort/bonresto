-- ═══════════════════════════════════════════════════════════════
-- PHASE 15 - SUGGESTIONS DE RESTAURANTS (Proposer un resto)
-- Permet aux utilisateurs authentifiés de suggérer de nouveaux
-- restaurants à ajouter sur la plateforme
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS restaurant_suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom VARCHAR(255) NOT NULL,
    adresse VARCHAR(500) DEFAULT NULL,
    ville VARCHAR(100) NOT NULL,
    type_cuisine VARCHAR(100) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    pourquoi TEXT DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected', 'duplicate') DEFAULT 'pending',
    admin_note TEXT DEFAULT NULL,
    restaurant_id INT DEFAULT NULL COMMENT 'Linked restaurant if approved and created',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL,
    INDEX idx_suggestions_user (user_id),
    INDEX idx_suggestions_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

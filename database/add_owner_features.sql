-- ═══════════════════════════════════════════════════════════════
-- PHASE 6 - Espace propriétaire : tables claim + notifications
-- ═══════════════════════════════════════════════════════════════

-- Table pour les demandes de revendication de restaurant
CREATE TABLE IF NOT EXISTS restaurant_claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    proof_path VARCHAR(500) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    email_pro VARCHAR(255) DEFAULT NULL,
    admin_notes TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table pour les notifications (propriétaires + tous les users)
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

-- Ajouter banned_at pour le système de ban (Phase 4.5)
ALTER TABLE users ADD COLUMN IF NOT EXISTS banned_at DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS ban_reason VARCHAR(500) DEFAULT NULL;

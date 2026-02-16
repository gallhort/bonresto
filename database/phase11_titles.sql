-- ═══════════════════════════════════════════════════════════════
-- PHASE 11 - TITRES PERSONNALISÉS
-- Badges dynamiques basés sur l'activité réelle de l'utilisateur
-- ═══════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS user_titles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title_type VARCHAR(50) NOT NULL,
    title_label VARCHAR(150) NOT NULL,
    title_icon VARCHAR(10) DEFAULT NULL,
    title_color VARCHAR(20) DEFAULT '#6b7280',
    context VARCHAR(100) DEFAULT NULL,
    earned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY idx_user_title_ctx (user_id, title_type, context),
    INDEX idx_user (user_id),
    INDEX idx_type (title_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

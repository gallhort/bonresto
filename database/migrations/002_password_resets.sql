-- ═══════════════════════════════════════════════════════════════════════════
-- MIGRATION - TABLE PASSWORD_RESETS
-- Gestion des tokens de réinitialisation de mot de passe
-- ═══════════════════════════════════════════════════════════════════════════

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `email` (`email`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour nettoyer automatiquement les vieux tokens
CREATE EVENT IF NOT EXISTS `cleanup_password_resets`
ON SCHEDULE EVERY 1 DAY
DO
  DELETE FROM `password_resets` WHERE `created_at` < DATE_SUB(NOW(), INTERVAL 24 HOUR);

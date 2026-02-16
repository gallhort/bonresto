-- ═══════════════════════════════════════════════════════════════════
-- Scoring Enrichment v2 : 14 nouveaux scores + données externes
-- Date : 2026-02-16
-- ═══════════════════════════════════════════════════════════════════

-- 1. Nouvelles colonnes score sur restaurants
ALTER TABLE restaurants
    ADD COLUMN IF NOT EXISTS score_brunch FLOAT DEFAULT 0 COMMENT 'Brunch/ftour/petit-dejeuner (0-1)',
    ADD COLUMN IF NOT EXISTS score_livraison FLOAT DEFAULT 0 COMMENT 'Qualite livraison (0-1)',
    ADD COLUMN IF NOT EXISTS score_vue FLOAT DEFAULT 0 COMMENT 'Vue mer/panoramique/rooftop (0-1)',
    ADD COLUMN IF NOT EXISTS score_healthy FLOAT DEFAULT 0 COMMENT 'Healthy/salade/regime (0-1)',
    ADD COLUMN IF NOT EXISTS score_ouvert_tard FLOAT DEFAULT 0 COMMENT 'Ouvert tard le soir (0-1)',
    ADD COLUMN IF NOT EXISTS score_instagrammable FLOAT DEFAULT 0 COMMENT 'Photogenique/deco/esthetique (0-1)',
    ADD COLUMN IF NOT EXISTS score_calme FLOAT DEFAULT 0 COMMENT 'Calme/paisible/cosy (0-1)',
    ADD COLUMN IF NOT EXISTS score_nouveau FLOAT DEFAULT 0 COMMENT 'Recemment ouvert/tendance (0-1)',
    ADD COLUMN IF NOT EXISTS score_parking FLOAT DEFAULT 0 COMMENT 'Facilite parking (0-1)',
    ADD COLUMN IF NOT EXISTS score_ramadan FLOAT DEFAULT 0 COMMENT 'Iftar/ftour/ramadan (0-1)',
    ADD COLUMN IF NOT EXISTS score_groupe FLOAT DEFAULT 0 COMMENT 'Grands groupes/salle privee (0-1)',
    ADD COLUMN IF NOT EXISTS score_wifi_travail FLOAT DEFAULT 0 COMMENT 'Cafe wifi pour travailler (0-1)',
    ADD COLUMN IF NOT EXISTS score_enfants FLOAT DEFAULT 0 COMMENT 'Specifiquement kid-friendly (0-1)',
    ADD COLUMN IF NOT EXISTS score_traditionnel FLOAT DEFAULT 0 COMMENT 'Cuisine algerienne authentique (0-1)';

-- 2. Index sur les scores les plus requêtés
CREATE INDEX IF NOT EXISTS idx_score_vue ON restaurants(score_vue);
CREATE INDEX IF NOT EXISTS idx_score_ouvert_tard ON restaurants(score_ouvert_tard);
CREATE INDEX IF NOT EXISTS idx_score_calme ON restaurants(score_calme);
CREATE INDEX IF NOT EXISTS idx_score_parking ON restaurants(score_parking);

-- 3. Table données externes (Phase 2 — backend-only, invisible pour les clients)
CREATE TABLE IF NOT EXISTS restaurant_external_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    source ENUM('google_places','facebook','yassir','jumia','instagram','website','manual') NOT NULL,
    data_key VARCHAR(100) NOT NULL COMMENT 'e.g. google_rating, fb_likes, ig_hashtag_count',
    data_value TEXT DEFAULT NULL COMMENT 'Scalar value or JSON',
    data_numeric FLOAT DEFAULT NULL COMMENT 'Numeric value for scoring queries',
    confidence FLOAT DEFAULT 1.0 COMMENT 'Source reliability 0-1',
    fetched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME DEFAULT NULL COMMENT 'When to re-fetch',
    raw_json JSON DEFAULT NULL COMMENT 'Full API response for audit',
    UNIQUE KEY uq_resto_source_key (restaurant_id, source, data_key),
    INDEX idx_source (source),
    INDEX idx_expires (expires_at),
    INDEX idx_key_value (data_key, data_numeric),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Poids adaptatifs pour les 14 nouveaux intents
INSERT IGNORE INTO concierge_weights (intent, w_note, w_popularite, w_occasion, w_proximite, w_prix, w_fraicheur) VALUES
('occasion_brunch',         0.20, 0.15, 0.35, 0.10, 0.10, 0.10),
('occasion_livraison',      0.15, 0.15, 0.30, 0.20, 0.10, 0.10),
('occasion_vue',            0.25, 0.15, 0.35, 0.10, 0.05, 0.10),
('occasion_healthy',        0.20, 0.15, 0.35, 0.10, 0.10, 0.10),
('occasion_ouvert_tard',    0.20, 0.15, 0.30, 0.15, 0.10, 0.10),
('occasion_instagrammable', 0.25, 0.20, 0.30, 0.10, 0.05, 0.10),
('occasion_calme',          0.25, 0.15, 0.35, 0.10, 0.05, 0.10),
('occasion_nouveau',        0.15, 0.20, 0.25, 0.10, 0.10, 0.20),
('occasion_parking',        0.20, 0.15, 0.35, 0.15, 0.05, 0.10),
('occasion_ramadan',        0.20, 0.15, 0.35, 0.10, 0.10, 0.10),
('occasion_groupe',         0.20, 0.15, 0.30, 0.10, 0.10, 0.15),
('occasion_wifi_travail',   0.20, 0.15, 0.35, 0.15, 0.05, 0.10),
('occasion_enfants',        0.20, 0.15, 0.35, 0.10, 0.10, 0.10),
('occasion_traditionnel',   0.20, 0.15, 0.35, 0.10, 0.10, 0.10);

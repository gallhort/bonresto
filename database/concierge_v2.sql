-- Concierge v2: Intent-based contextual discovery engine
-- Date: 2026-02-16

-- ═══════════════════════════════════════════════════════════════════
-- 1. Pre-indexed occasion/ambiance scores on restaurants
-- Computed via batch job, values 0.0 to 1.0
-- ═══════════════════════════════════════════════════════════════════
ALTER TABLE restaurants
    ADD COLUMN IF NOT EXISTS score_familial FLOAT DEFAULT 0 COMMENT 'Ambiance familiale (0-1)',
    ADD COLUMN IF NOT EXISTS score_romantique FLOAT DEFAULT 0 COMMENT 'Ambiance romantique (0-1)',
    ADD COLUMN IF NOT EXISTS score_business FLOAT DEFAULT 0 COMMENT 'Business/pro (0-1)',
    ADD COLUMN IF NOT EXISTS score_rapide FLOAT DEFAULT 0 COMMENT 'Service rapide (0-1)',
    ADD COLUMN IF NOT EXISTS score_festif FLOAT DEFAULT 0 COMMENT 'Festif/sortie (0-1)',
    ADD COLUMN IF NOT EXISTS score_terrasse FLOAT DEFAULT 0 COMMENT 'Experience terrasse (0-1)',
    ADD COLUMN IF NOT EXISTS score_budget FLOAT DEFAULT 0 COMMENT 'Rapport qualite-prix (0-1)',
    ADD COLUMN IF NOT EXISTS score_gastronomique FLOAT DEFAULT 0 COMMENT 'Fine dining (0-1)',
    ADD COLUMN IF NOT EXISTS score_updated_at DATETIME DEFAULT NULL COMMENT 'Derniere mise a jour des scores';

CREATE INDEX IF NOT EXISTS idx_score_familial ON restaurants(score_familial DESC);
CREATE INDEX IF NOT EXISTS idx_score_romantique ON restaurants(score_romantique DESC);
CREATE INDEX IF NOT EXISTS idx_score_rapide ON restaurants(score_rapide DESC);
CREATE INDEX IF NOT EXISTS idx_score_budget ON restaurants(score_budget DESC);

-- ═══════════════════════════════════════════════════════════════════
-- 2. Concierge recommendation tracking (feedback loop)
-- Tracks: query → shown results → clicks → conversions
-- ═══════════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS concierge_recommendations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL,
    conversation_id INT DEFAULT NULL COMMENT 'FK to concierge_conversations.id',
    user_id INT DEFAULT NULL,
    restaurant_id INT NOT NULL,
    position TINYINT NOT NULL COMMENT 'Position in results (1,2,3)',
    intent VARCHAR(50) NOT NULL,
    query_text VARCHAR(500) NOT NULL,
    context_score FLOAT DEFAULT 0 COMMENT 'Score contextuel calcule',
    explanation VARCHAR(255) DEFAULT NULL COMMENT 'Pourquoi ce restaurant (~15 mots)',
    -- Feedback signals
    clicked TINYINT(1) DEFAULT 0,
    clicked_at DATETIME DEFAULT NULL,
    booked TINYINT(1) DEFAULT 0 COMMENT 'Reservation apres clic',
    booked_at DATETIME DEFAULT NULL,
    ordered TINYINT(1) DEFAULT 0 COMMENT 'Commande apres clic',
    ordered_at DATETIME DEFAULT NULL,
    dwell_time INT DEFAULT NULL COMMENT 'Secondes sur la fiche apres clic',
    -- Metadata
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_session (session_id),
    KEY idx_user (user_id),
    KEY idx_restaurant (restaurant_id),
    KEY idx_intent (intent, created_at),
    KEY idx_clicked (clicked, created_at),
    KEY idx_conversion (booked, ordered, created_at),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ═══════════════════════════════════════════════════════════════════
-- 3. Adaptive weights (lightweight ML)
-- One row per intent, weights evolve over time
-- ═══════════════════════════════════════════════════════════════════
CREATE TABLE IF NOT EXISTS concierge_weights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    intent VARCHAR(50) NOT NULL UNIQUE,
    w_note FLOAT DEFAULT 0.30 COMMENT 'Poids note_moyenne',
    w_popularite FLOAT DEFAULT 0.20 COMMENT 'Poids popularity_score',
    w_occasion FLOAT DEFAULT 0.25 COMMENT 'Poids score occasion (familial/romantique/etc)',
    w_proximite FLOAT DEFAULT 0.10 COMMENT 'Poids distance geo',
    w_prix FLOAT DEFAULT 0.10 COMMENT 'Poids budget',
    w_fraicheur FLOAT DEFAULT 0.05 COMMENT 'Poids activite recente (avis, commandes)',
    impressions INT DEFAULT 0 COMMENT 'Nombre de fois cet intent a ete servi',
    clicks INT DEFAULT 0 COMMENT 'Clics totaux',
    conversions INT DEFAULT 0 COMMENT 'Reservations + commandes',
    ctr FLOAT DEFAULT 0 COMMENT 'Click-through rate calcule',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default weights per intent
INSERT IGNORE INTO concierge_weights (intent, w_note, w_popularite, w_occasion, w_proximite, w_prix, w_fraicheur) VALUES
('search_restaurant', 0.25, 0.20, 0.20, 0.15, 0.10, 0.10),
('recommendation', 0.30, 0.25, 0.15, 0.10, 0.10, 0.10),
('price', 0.15, 0.10, 0.10, 0.15, 0.40, 0.10),
('open_now', 0.25, 0.20, 0.10, 0.25, 0.10, 0.10),
('amenity_search', 0.20, 0.20, 0.25, 0.15, 0.10, 0.10),
('direct_search', 0.15, 0.15, 0.10, 0.10, 0.10, 0.40),
('occasion_familial', 0.20, 0.15, 0.35, 0.10, 0.10, 0.10),
('occasion_romantique', 0.25, 0.15, 0.35, 0.10, 0.05, 0.10),
('occasion_business', 0.25, 0.15, 0.30, 0.15, 0.05, 0.10),
('occasion_rapide', 0.15, 0.15, 0.30, 0.20, 0.10, 0.10),
('occasion_festif', 0.20, 0.20, 0.30, 0.10, 0.10, 0.10);

-- ═══════════════════════════════════════════════════════════════════
-- 4. Add concierge event types to analytics_events
-- ═══════════════════════════════════════════════════════════════════
ALTER TABLE analytics_events
    MODIFY COLUMN event_type ENUM(
        'view','click_phone','click_directions','click_website','click_menu',
        'click_booking','wishlist_add','wishlist_remove','share','gallery_open',
        'photo_view','search_impression','search_click','review_form_open',
        'review_submitted',
        'concierge_impression','concierge_click','concierge_book','concierge_order'
    ) NOT NULL;

-- ═══════════════════════════════════════════════════════════════════
-- 5. Initial batch computation of occasion scores
-- Based on: type_cuisine, price_range, amenities, reviews, nb_avis
-- ═══════════════════════════════════════════════════════════════════

-- score_familial: game_zone, baby_chair, prix bas/moyen, cuisine populaire
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_familial = LEAST(1.0,
    COALESCE(ro.game_zone, 0) * 0.25 +
    COALESCE(ro.baby_chair, 0) * 0.20 +
    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 3 THEN 0.20 ELSE 0.05 END +
    CASE WHEN r.type_cuisine LIKE '%pizza%' OR r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%Fast%' THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.note_moyenne >= 4.0 THEN 0.10 ELSE 0.05 END +
    CASE WHEN r.nb_avis >= 5 THEN 0.10 ELSE 0.03 END
)
WHERE r.status = 'validated';

-- score_romantique: private_room, prix eleve, gastronomique, bonne note
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_romantique = LEAST(1.0,
    COALESCE(ro.private_room, 0) * 0.25 +
    COALESCE(ro.terrace, 0) * 0.15 +
    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 3 THEN 0.20 ELSE 0.05 END +
    CASE WHEN r.type_cuisine LIKE '%gastro%' OR r.type_cuisine LIKE '%franc%' OR r.type_cuisine LIKE '%ital%' THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.note_moyenne >= 4.2 THEN 0.15 ELSE 0.05 END +
    CASE WHEN COALESCE(ro.air_conditioning, 0) = 1 THEN 0.10 ELSE 0.02 END
)
WHERE r.status = 'validated';

-- score_business: wifi, private_room, parking, prix moyen+, bonne note
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_business = LEAST(1.0,
    COALESCE(ro.wifi, 0) * 0.25 +
    COALESCE(ro.private_room, 0) * 0.20 +
    COALESCE(ro.parking, 0) * 0.15 +
    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 2 THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.note_moyenne >= 4.0 THEN 0.15 ELSE 0.05 END +
    CASE WHEN COALESCE(ro.air_conditioning, 0) = 1 THEN 0.10 ELSE 0.02 END
)
WHERE r.status = 'validated';

-- score_rapide: fast food, prix bas, delivery/takeaway
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_rapide = LEAST(1.0,
    CASE WHEN r.type_cuisine LIKE '%Fast%' OR r.type_cuisine LIKE '%burger%' OR r.type_cuisine LIKE '%pizza%' OR r.type_cuisine LIKE '%kebab%' THEN 0.30 ELSE 0.05 END +
    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 2 THEN 0.20 ELSE 0.05 END +
    COALESCE(ro.delivery, 0) * 0.15 +
    COALESCE(ro.takeaway, 0) * 0.15 +
    CASE WHEN r.orders_enabled = 1 THEN 0.10 ELSE 0.02 END +
    CASE WHEN r.nb_avis >= 3 THEN 0.10 ELSE 0.03 END
)
WHERE r.status = 'validated';

-- score_festif: terrasse, bonne note, populaire, prix moyen
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_festif = LEAST(1.0,
    COALESCE(ro.terrace, 0) * 0.25 +
    CASE WHEN r.note_moyenne >= 4.0 THEN 0.20 ELSE 0.05 END +
    CASE WHEN r.popularity_score >= 50 THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.nb_avis >= 10 THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.events_enabled = 1 THEN 0.15 ELSE 0.02 END +
    CASE WHEN COALESCE(ro.air_conditioning, 0) = 1 THEN 0.10 ELSE 0.02 END
)
WHERE r.status = 'validated';

-- score_terrasse: terrasse obvieusement, mais aussi note, ambiance
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_terrasse = LEAST(1.0,
    COALESCE(ro.terrace, 0) * 0.45 +
    CASE WHEN r.note_moyenne >= 4.0 THEN 0.20 ELSE 0.05 END +
    CASE WHEN r.nb_avis >= 5 THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.popularity_score >= 30 THEN 0.10 ELSE 0.03 END +
    CASE WHEN COALESCE(ro.parking, 0) = 1 THEN 0.10 ELSE 0.02 END
)
WHERE r.status = 'validated';

-- score_budget: prix bas, bonne note pour le prix, popularite
UPDATE restaurants r
SET r.score_budget = LEAST(1.0,
    CASE
        WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) <= 2 THEN 0.40
        WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 3 THEN 0.25
        WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) = 4 THEN 0.10
        ELSE 0.05
    END +
    CASE WHEN r.note_moyenne >= 4.0 THEN 0.25 ELSE COALESCE(r.note_moyenne,0) * 0.05 END +
    CASE WHEN r.nb_avis >= 5 THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.popularity_score >= 30 THEN 0.10 ELSE 0.03 END +
    0.10
)
WHERE r.status = 'validated';

-- score_gastronomique: prix eleve, haute note, peu de volume mais qualite
UPDATE restaurants r
LEFT JOIN restaurant_options ro ON ro.restaurant_id = r.id
SET r.score_gastronomique = LEAST(1.0,
    CASE WHEN CHAR_LENGTH(COALESCE(r.price_range,'')) >= 3 THEN 0.30 ELSE 0.05 END +
    CASE WHEN r.note_moyenne >= 4.5 THEN 0.25 WHEN r.note_moyenne >= 4.0 THEN 0.15 ELSE 0.05 END +
    COALESCE(ro.private_room, 0) * 0.10 +
    COALESCE(ro.valet_service, 0) * 0.10 +
    CASE WHEN r.type_cuisine LIKE '%gastro%' OR r.type_cuisine LIKE '%franc%' THEN 0.15 ELSE 0.05 END +
    CASE WHEN r.reservations_enabled = 1 THEN 0.10 ELSE 0.02 END
)
WHERE r.status = 'validated';

-- Mark all scores as freshly computed
UPDATE restaurants SET score_updated_at = NOW() WHERE status = 'validated';

-- ═══════════════════════════════════════════════════════════════════
-- 6. Recalculate popularity_score with temporal decay
-- ═══════════════════════════════════════════════════════════════════
UPDATE restaurants r SET popularity_score = (
    COALESCE(r.note_moyenne, 0) * 20 +
    COALESCE(r.nb_avis, 0) * 5 +
    COALESCE(r.vues_total, 0) * 0.01 +
    (SELECT COUNT(*) FROM orders o WHERE o.restaurant_id = r.id AND o.status = 'delivered') * 10 +
    (SELECT IF(COUNT(*) > 0, 15, 0) FROM restaurant_awards ra WHERE ra.restaurant_id = r.id) +
    -- Temporal decay: recent reviews worth more
    (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 8 +
    -- Recent orders boost
    (SELECT COUNT(*) FROM orders o2 WHERE o2.restaurant_id = r.id AND o2.status = 'delivered' AND o2.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) * 12
)
WHERE r.status = 'validated';

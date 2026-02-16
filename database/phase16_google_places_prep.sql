-- ═══════════════════════════════════════════════════════════════════════════
-- PHASE 16 - Préparation BDD pour Google Places API
-- Garantit que toutes les colonnes/tables nécessaires existent
-- avant l'exécution de populate_google_places.py
-- ═══════════════════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────────────────
-- 1. TABLE restaurants — colonnes nécessaires
-- ─────────────────────────────────────────────────────────────────────────

-- google_place_id : identifiant unique Google Places pour déduplication
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS google_place_id VARCHAR(255) NULL;

-- Index pour recherche rapide par google_place_id (déduplication)
-- Utilise CREATE INDEX classique car IF NOT EXISTS n'est pas supporté partout
-- Si l'index existe déjà, l'erreur sera ignorée
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'restaurants' AND index_name = 'idx_restaurants_google_place_id');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_restaurants_google_place_id ON restaurants(google_place_id)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- verified_halal : 0 si sert alcool (bière/vin/cocktails), 1 sinon
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS verified_halal TINYINT(1) NOT NULL DEFAULT 1;

-- nb_avis : nombre total d'avis (importé depuis userRatingCount Google)
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS nb_avis INT NOT NULL DEFAULT 0;

-- note_moyenne : note moyenne (importée depuis rating Google, max 5.0)
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS note_moyenne DECIMAL(3,2) NULL;

-- website : URL du site web du restaurant
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS website VARCHAR(500) NULL;

-- reservations_enabled : le restaurant accepte les réservations (places.reservable)
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS reservations_enabled TINYINT(1) NOT NULL DEFAULT 0;

-- slug : URL-friendly name
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS slug VARCHAR(255) NULL;

-- Index sur slug pour les URL SEO
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'restaurants' AND index_name = 'idx_restaurants_slug');
SET @sql = IF(@idx_exists = 0,
    'CREATE UNIQUE INDEX idx_restaurants_slug ON restaurants(slug)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Index composite pour la recherche par ville + status (utilisé par _count_nearby)
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'restaurants' AND index_name = 'idx_restaurants_ville_status_gps');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_restaurants_ville_status_gps ON restaurants(status, gps_latitude, gps_longitude)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────────────────
-- 2. TABLE restaurant_horaires — vérifier la structure
-- ─────────────────────────────────────────────────────────────────────────

-- La table doit exister avec les bonnes colonnes
-- (créée si elle n'existe pas encore, sinon skip)
CREATE TABLE IF NOT EXISTS restaurant_horaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    jour_semaine TINYINT NOT NULL COMMENT '0=Lundi, 1=Mardi...6=Dimanche',
    ferme TINYINT(1) NOT NULL DEFAULT 0,
    service_continu TINYINT(1) NOT NULL DEFAULT 0,
    ouverture_matin TIME NULL,
    fermeture_matin TIME NULL,
    ouverture_soir TIME NULL,
    fermeture_soir TIME NULL,
    INDEX idx_restaurant (restaurant_id),
    UNIQUE KEY uk_restaurant_jour (restaurant_id, jour_semaine),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────
-- 3. TABLE restaurant_options — vérifier les colonnes amenities
-- ─────────────────────────────────────────────────────────────────────────

-- La table doit exister avec les colonnes booléennes
CREATE TABLE IF NOT EXISTS restaurant_options (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    wifi TINYINT(1) NOT NULL DEFAULT 0,
    parking TINYINT(1) NOT NULL DEFAULT 0,
    terrace TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'places.outdoorSeating',
    delivery TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'places.delivery',
    takeaway TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'places.takeout',
    pets_allowed TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'places.allowsDogs',
    baby_chair TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'places.goodForChildren',
    handicap_access TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'places.accessibilityOptions',
    air_conditioning TINYINT(1) NOT NULL DEFAULT 0,
    private_room TINYINT(1) NOT NULL DEFAULT 0,
    prayer_room TINYINT(1) NOT NULL DEFAULT 0,
    valet_service TINYINT(1) NOT NULL DEFAULT 0,
    game_zone TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uk_restaurant (restaurant_id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajouter les colonnes si elles manquent (pour les BDD existantes)
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS delivery TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS takeaway TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS terrace TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS pets_allowed TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS baby_chair TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS handicap_access TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS parking TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE restaurant_options ADD COLUMN IF NOT EXISTS air_conditioning TINYINT(1) NOT NULL DEFAULT 0;

-- ─────────────────────────────────────────────────────────────────────────
-- 4. TABLE restaurant_photos — vérifier la structure
-- ─────────────────────────────────────────────────────────────────────────

CREATE TABLE IF NOT EXISTS restaurant_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    photo_url VARCHAR(500) NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    category VARCHAR(50) NULL,
    ai_labels TEXT NULL,
    ai_processed TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant (restaurant_id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────────────────
-- 5. TABLE reviews — vérifier compatibilité Google reviews
-- ─────────────────────────────────────────────────────────────────────────

-- user_id doit accepter NULL (avis Google sans compte utilisateur)
ALTER TABLE reviews MODIFY COLUMN user_id INT NULL;

-- author_name pour les avis sans user_id
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS author_name VARCHAR(255) NULL;

-- title pour les avis
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS title VARCHAR(255) NULL;

-- source doit pouvoir contenir 'google'
-- (si c'est un VARCHAR, c'est déjà OK ; si c'est un ENUM, il faut l'étendre)
-- Vérification : la colonne est VARCHAR(20) donc 'google' (6 chars) est accepté

-- spam_score : score anti-spam (100 = légitime pour les avis Google importés)
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS spam_score INT NOT NULL DEFAULT 100;

-- ─────────────────────────────────────────────────────────────────────────
-- 6. INDEX pour les requêtes du populate script
-- ─────────────────────────────────────────────────────────────────────────

-- Index pour déduplication par nom+ville (normalize_name dans le script)
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics
    WHERE table_schema = DATABASE() AND table_name = 'restaurants' AND index_name = 'idx_restaurants_nom_ville');
SET @sql = IF(@idx_exists = 0,
    'CREATE INDEX idx_restaurants_nom_ville ON restaurants(nom(100), ville(50))',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ─────────────────────────────────────────────────────────────────────────
-- 7. VÉRIFICATION FINALE
-- ─────────────────────────────────────────────────────────────────────────

-- Afficher un résumé des tables prêtes
SELECT 'Phase 16 - Google Places Prep' AS migration;
SELECT 'restaurants' AS table_name, COUNT(*) AS row_count FROM restaurants
UNION ALL
SELECT 'restaurant_horaires', COUNT(*) FROM restaurant_horaires
UNION ALL
SELECT 'restaurant_options', COUNT(*) FROM restaurant_options
UNION ALL
SELECT 'restaurant_photos', COUNT(*) FROM restaurant_photos
UNION ALL
SELECT 'reviews', COUNT(*) FROM reviews;

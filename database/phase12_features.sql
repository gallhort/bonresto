-- ═══════════════════════════════════════════════════════════════
-- PHASE 12 - FEATURES COMPLETES
-- Photos IA, Check-in, Collections, Reservations, Menu, Feed
-- ═══════════════════════════════════════════════════════════════

-- ─────────────────────────────────────────────────────────────
-- 1. PHOTO AI CATEGORIZATION
-- ─────────────────────────────────────────────────────────────
ALTER TABLE review_photos
    ADD COLUMN category VARCHAR(30) DEFAULT 'other' AFTER photo_path,
    ADD COLUMN ai_labels JSON DEFAULT NULL AFTER category,
    ADD COLUMN ai_processed TINYINT(1) DEFAULT 0 AFTER ai_labels;

ALTER TABLE restaurant_photos
    ADD COLUMN ai_category VARCHAR(30) DEFAULT NULL AFTER type,
    ADD COLUMN ai_labels JSON DEFAULT NULL AFTER ai_category;

CREATE TABLE IF NOT EXISTS ai_usage_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    month_year VARCHAR(7) NOT NULL,
    api_calls INT DEFAULT 0,
    last_call_at DATETIME DEFAULT NULL,
    UNIQUE KEY idx_month (month_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 2. CHECK-IN GEO
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS checkins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    user_lat DECIMAL(10,7) NOT NULL,
    user_lng DECIMAL(10,7) NOT NULL,
    distance_m INT NOT NULL,
    points_earned INT DEFAULT 20,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_user_restaurant (user_id, restaurant_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- 3. COLLECTIONS PUBLIQUES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS collections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    slug VARCHAR(180) NOT NULL,
    cover_photo VARCHAR(500) DEFAULT NULL,
    is_public TINYINT(1) DEFAULT 1,
    views_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_slug (slug),
    INDEX idx_user (user_id),
    INDEX idx_public (is_public, views_count),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS collection_restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    collection_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    note_perso TEXT DEFAULT NULL,
    position INT DEFAULT 0,
    added_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_col_resto (collection_id, restaurant_id),
    INDEX idx_collection (collection_id),
    INDEX idx_restaurant (restaurant_id),
    FOREIGN KEY (collection_id) REFERENCES collections(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 4. RESERVATIONS EN LIGNE
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    date_souhaitee DATE NOT NULL,
    heure VARCHAR(5) NOT NULL,
    nb_personnes INT NOT NULL DEFAULT 2,
    telephone VARCHAR(20) DEFAULT NULL,
    message TEXT DEFAULT NULL,
    status ENUM('pending','accepted','refused','cancelled') DEFAULT 'pending',
    owner_note TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_status (status),
    INDEX idx_date (restaurant_id, date_souhaitee),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 5. MENU AVEC PRIX
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS restaurant_menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    category VARCHAR(80) NOT NULL DEFAULT 'Plats',
    name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    price DECIMAL(8,2) DEFAULT NULL,
    photo_path VARCHAR(500) DEFAULT NULL,
    is_available TINYINT(1) DEFAULT 1,
    position INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_category (restaurant_id, category),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 6. FIL D'ACTUALITE SOCIAL (Activity Feed)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_feed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action_type ENUM('review','checkin','collection','photo','badge','reservation') NOT NULL,
    target_type VARCHAR(30) DEFAULT NULL,
    target_id INT DEFAULT NULL,
    metadata JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_created (created_at),
    INDEX idx_action (action_type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- 7. OWNER SETTINGS (reservation toggle, etc.)
-- ─────────────────────────────────────────────────────────────
ALTER TABLE restaurants
    ADD COLUMN reservations_enabled TINYINT(1) DEFAULT 0 AFTER owner_id,
    ADD COLUMN menu_enabled TINYINT(1) DEFAULT 0 AFTER reservations_enabled;

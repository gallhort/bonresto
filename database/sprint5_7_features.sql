-- =====================================================
-- LeBonResto - Sprints 5, 6, 7 Migration
-- Features: F20-F23, F10-F11, F14, F27, F17-F18, F28-F35
-- Date: 2026-02-16
-- =====================================================

-- ─────────────────────────────────────────────────────
-- SPRINT 5: F20 Newsletter, F21 Push, F22 Share, F23 Widget
-- ─────────────────────────────────────────────────────

-- F20: Newsletter subscriptions
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    user_id INT DEFAULT NULL,
    ville VARCHAR(100) DEFAULT NULL,
    frequency ENUM('weekly','monthly') DEFAULT 'weekly',
    is_active TINYINT(1) DEFAULT 1,
    token VARCHAR(64) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at DATETIME DEFAULT NULL,
    UNIQUE KEY idx_email (email),
    KEY idx_ville (ville),
    KEY idx_token (token),
    KEY idx_active (is_active),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F21: Push notification subscriptions
CREATE TABLE IF NOT EXISTS push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    endpoint TEXT NOT NULL,
    p256dh VARCHAR(255) NOT NULL,
    auth_key VARCHAR(255) NOT NULL,
    device_type VARCHAR(20) DEFAULT 'web',
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_active (is_active),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F22: Share tracking
CREATE TABLE IF NOT EXISTS share_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    shareable_type ENUM('restaurant','review','collection','activity') NOT NULL,
    shareable_id INT NOT NULL,
    platform VARCHAR(30) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_shareable (shareable_type, shareable_id),
    KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F23: Widgets for restaurants
CREATE TABLE IF NOT EXISTS restaurant_widgets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    widget_token VARCHAR(32) NOT NULL,
    theme ENUM('light','dark','auto') DEFAULT 'light',
    show_reviews TINYINT(1) DEFAULT 1,
    show_rating TINYINT(1) DEFAULT 1,
    show_photos TINYINT(1) DEFAULT 0,
    max_reviews INT DEFAULT 3,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_token (widget_token),
    KEY idx_restaurant (restaurant_id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────
-- SPRINT 6: F10 Rappels, F11 No-show, F14 Allergènes, F27 Préférences
-- ─────────────────────────────────────────────────────

-- F10: Reservation reminders (columns already exist from phase17)
-- reminder_24h_sent and reminder_2h_sent already in reservations table

-- F11: No-show tracking
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS no_show TINYINT(1) DEFAULT 0 AFTER owner_note;
ALTER TABLE reservations ADD COLUMN IF NOT EXISTS no_show_at DATETIME DEFAULT NULL AFTER no_show;

-- No-show stats per user
CREATE TABLE IF NOT EXISTS user_no_show_stats (
    user_id INT PRIMARY KEY,
    total_no_shows INT DEFAULT 0,
    total_reservations INT DEFAULT 0,
    last_no_show_at DATETIME DEFAULT NULL,
    reliability_score DECIMAL(3,2) DEFAULT 1.00,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F14: Menu item allergens
CREATE TABLE IF NOT EXISTS menu_item_allergens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_item_id INT NOT NULL,
    allergen VARCHAR(50) NOT NULL,
    KEY idx_item (menu_item_id),
    KEY idx_allergen (allergen),
    FOREIGN KEY (menu_item_id) REFERENCES restaurant_menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F27: User preferences
ALTER TABLE users ADD COLUMN IF NOT EXISTS preferences JSON DEFAULT NULL AFTER photo_profil;
-- JSON structure: {"cuisines":["italien","japonais"],"diet":["vegetarien"],"allergies":["gluten","arachides"],"price_range":"$$","notifications":{"email":true,"push":true,"newsletter":true}}

-- ─────────────────────────────────────────────────────
-- SPRINT 7: F17, F18, F28-F35
-- ─────────────────────────────────────────────────────

-- F17: Post-visit questionnaire
CREATE TABLE IF NOT EXISTS post_visit_surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    food_rating TINYINT DEFAULT NULL,
    service_rating TINYINT DEFAULT NULL,
    ambiance_rating TINYINT DEFAULT NULL,
    value_rating TINYINT DEFAULT NULL,
    would_recommend TINYINT(1) DEFAULT NULL,
    feedback TEXT DEFAULT NULL,
    completed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_reservation (reservation_id),
    KEY idx_user (user_id),
    KEY idx_restaurant (restaurant_id),
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F18: Availability alerts
CREATE TABLE IF NOT EXISTS availability_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    desired_date DATE NOT NULL,
    desired_time VARCHAR(5) DEFAULT NULL,
    nb_personnes INT DEFAULT 2,
    notified TINYINT(1) DEFAULT 0,
    notified_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_restaurant_date (restaurant_id, desired_date),
    KEY idx_notified (notified),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F29: Virtual waitlist
CREATE TABLE IF NOT EXISTS waitlist_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    guest_name VARCHAR(100) DEFAULT NULL,
    guest_phone VARCHAR(20) DEFAULT NULL,
    nb_personnes INT DEFAULT 2,
    position INT NOT NULL,
    status ENUM('waiting','notified','seated','cancelled','expired') DEFAULT 'waiting',
    estimated_wait INT DEFAULT NULL,
    notified_at DATETIME DEFAULT NULL,
    seated_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_restaurant_status (restaurant_id, status),
    KEY idx_user (user_id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F30: Peak hours (uses existing analytics_events, no new table needed)

-- F34: Culinary events
CREATE TABLE IF NOT EXISTS restaurant_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT DEFAULT NULL,
    event_type ENUM('tasting','workshop','live_music','theme_night','brunch','promotion','other') DEFAULT 'other',
    event_date DATE NOT NULL,
    start_time VARCHAR(5) NOT NULL,
    end_time VARCHAR(5) DEFAULT NULL,
    max_participants INT DEFAULT NULL,
    current_participants INT DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0,
    photo_path VARCHAR(255) DEFAULT NULL,
    status ENUM('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_restaurant (restaurant_id),
    KEY idx_date (event_date),
    KEY idx_status (status),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    nb_places INT DEFAULT 1,
    status ENUM('registered','cancelled','attended') DEFAULT 'registered',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY idx_event_user (event_id, user_id),
    KEY idx_user (user_id),
    FOREIGN KEY (event_id) REFERENCES restaurant_events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F35: Premium subscriptions
CREATE TABLE IF NOT EXISTS premium_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    price_monthly DECIMAL(10,2) NOT NULL,
    price_yearly DECIMAL(10,2) NOT NULL,
    features JSON NOT NULL,
    max_restaurants INT DEFAULT 1,
    max_photos INT DEFAULT 10,
    analytics_access TINYINT(1) DEFAULT 0,
    priority_support TINYINT(1) DEFAULT 0,
    badge_type VARCHAR(30) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    restaurant_id INT DEFAULT NULL,
    billing_cycle ENUM('monthly','yearly') DEFAULT 'monthly',
    status ENUM('active','cancelled','expired','trial') DEFAULT 'trial',
    trial_ends_at DATETIME DEFAULT NULL,
    current_period_start DATETIME NOT NULL,
    current_period_end DATETIME NOT NULL,
    cancelled_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_user (user_id),
    KEY idx_status (status),
    KEY idx_restaurant (restaurant_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES premium_plans(id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed premium plans
INSERT INTO premium_plans (name, slug, price_monthly, price_yearly, features, max_restaurants, max_photos, analytics_access, priority_support, badge_type) VALUES
('Essentiel', 'essentiel', 2000, 20000, '["Profil vérifié","Badge Premium","10 photos max","Statistiques de base"]', 1, 10, 0, 0, 'verified'),
('Pro', 'pro', 5000, 50000, '["Tout Essentiel","30 photos max","Analytics complet","Offres promotionnelles","Réponses IA","Support prioritaire"]', 3, 30, 1, 1, 'pro'),
('Elite', 'elite', 10000, 100000, '["Tout Pro","Photos illimitées","Widget site web","Mise en avant recherche","Événements","Commandes prioritaires","Account manager"]', 10, 999, 1, 1, 'elite')
ON DUPLICATE KEY UPDATE name=name;

-- F28: i18n - translations table
CREATE TABLE IF NOT EXISTS translations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    locale VARCHAR(5) NOT NULL DEFAULT 'fr',
    translation_key VARCHAR(255) NOT NULL,
    translation_value TEXT NOT NULL,
    UNIQUE KEY idx_locale_key (locale, translation_key),
    KEY idx_key (translation_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- F31: Video/audio reviews
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS media_path VARCHAR(255) DEFAULT NULL AFTER message;
ALTER TABLE reviews ADD COLUMN IF NOT EXISTS media_type ENUM('none','video','audio') DEFAULT 'none' AFTER media_path;

-- F32: AI Concierge conversation log
CREATE TABLE IF NOT EXISTS concierge_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(64) NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    intent VARCHAR(50) DEFAULT NULL,
    entities JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_session (session_id),
    KEY idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Restaurants: premium flag + waitlist
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS is_premium TINYINT(1) DEFAULT 0 AFTER orders_enabled;
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS waitlist_enabled TINYINT(1) DEFAULT 0 AFTER is_premium;
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS events_enabled TINYINT(1) DEFAULT 0 AFTER waitlist_enabled;

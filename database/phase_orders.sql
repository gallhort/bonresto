-- ═══════════════════════════════════════════════════════════════════════════
-- F33 - COMMANDE EN LIGNE
-- Migration SQL - Tables orders + order_items + colonnes restaurants
-- ═══════════════════════════════════════════════════════════════════════════

-- Colonnes ajoutees a restaurants
ALTER TABLE restaurants
    ADD COLUMN IF NOT EXISTS orders_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS delivery_enabled TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS delivery_fee DECIMAL(8,2) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS delivery_min_order DECIMAL(8,2) DEFAULT NULL;

-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,

    -- Type et statut
    order_type ENUM('pickup','delivery') NOT NULL DEFAULT 'pickup',
    status ENUM('pending','confirmed','preparing','ready','delivering','delivered','cancelled','refused') NOT NULL DEFAULT 'pending',

    -- Montants (snapshot au moment de la commande)
    items_total DECIMAL(10,2) NOT NULL,
    delivery_fee DECIMAL(8,2) NOT NULL DEFAULT 0,
    grand_total DECIMAL(10,2) NOT NULL,

    -- Infos client
    client_name VARCHAR(100) NOT NULL,
    client_phone VARCHAR(20) NOT NULL,
    delivery_address VARCHAR(255) DEFAULT NULL,
    delivery_city VARCHAR(80) DEFAULT NULL,
    special_instructions TEXT DEFAULT NULL,

    -- Traitement
    estimated_minutes INT DEFAULT NULL,
    cancel_reason TEXT DEFAULT NULL,

    -- Timestamps
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    confirmed_at DATETIME DEFAULT NULL,
    ready_at DATETIME DEFAULT NULL,
    delivered_at DATETIME DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes
    INDEX idx_restaurant_status (restaurant_id, status),
    INDEX idx_user_created (user_id, created_at DESC),
    INDEX idx_status_created (status, created_at DESC),
    INDEX idx_restaurant_created (restaurant_id, created_at DESC),

    -- Foreign keys
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Items de commande
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,

    -- Snapshot au moment de la commande (le prix peut changer apres)
    item_name VARCHAR(150) NOT NULL,
    item_price DECIMAL(8,2) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    special_requests TEXT DEFAULT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_order (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES restaurant_menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index supplementaire pour le polling rapide
CREATE INDEX idx_orders_pending ON orders(restaurant_id, status, created_at DESC);

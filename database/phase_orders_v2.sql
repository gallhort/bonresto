-- Phase Orders V2 - Améliorations commandes
-- Date: 2026-02-15

-- Distance max de livraison (en km)
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS delivery_max_km DECIMAL(5,1) DEFAULT NULL AFTER delivery_min_order;

-- Photo plat: colonne deja existante (photo_path), rien a faire
-- Index pour stats commandes
CREATE INDEX IF NOT EXISTS idx_orders_restaurant_status ON orders(restaurant_id, status);
CREATE INDEX IF NOT EXISTS idx_orders_restaurant_created ON orders(restaurant_id, created_at);

-- ═══════════════════════════════════════════════════════════════
-- INDEX MANQUANTS — Le Bon Resto
-- Exécuter ce script sur la base de données pour améliorer les performances
-- ═══════════════════════════════════════════════════════════════

-- Restaurants
ALTER TABLE restaurants ADD INDEX idx_status (status);
ALTER TABLE restaurants ADD INDEX idx_featured (featured);
ALTER TABLE restaurants ADD INDEX idx_owner (owner_id);
ALTER TABLE restaurants ADD INDEX idx_ville_status (ville, status);
ALTER TABLE restaurants ADD INDEX idx_type_cuisine_status (type_cuisine, status);

-- Reviews
ALTER TABLE reviews ADD INDEX idx_restaurant_status (restaurant_id, status);
ALTER TABLE reviews ADD INDEX idx_user (user_id);

-- Users
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_username (username);

-- Wishlist
ALTER TABLE wishlist ADD UNIQUE INDEX idx_user_restaurant (user_id, restaurant_id);

-- Analytics
ALTER TABLE analytics_events ADD INDEX idx_restaurant_date (restaurant_id, created_at);
ALTER TABLE analytics_events ADD INDEX idx_event_type (event_type);
ALTER TABLE analytics_events ADD INDEX idx_restaurant_event_date (restaurant_id, event_type, created_at);

-- Review photos
ALTER TABLE review_photos ADD INDEX idx_review (review_id);

-- Review votes
ALTER TABLE review_votes ADD UNIQUE INDEX idx_review_user (review_id, user_id);

-- Review reports
ALTER TABLE review_reports ADD INDEX idx_review_status (review_id, status);

-- Password resets
ALTER TABLE password_resets ADD INDEX idx_token (token);
ALTER TABLE password_resets ADD INDEX idx_email_created (email, created_at);

-- Migration: Add google_place_id column to restaurants table
ALTER TABLE restaurants ADD COLUMN google_place_id VARCHAR(255) NULL AFTER menu_enabled;
CREATE INDEX idx_restaurants_google_place_id ON restaurants(google_place_id);

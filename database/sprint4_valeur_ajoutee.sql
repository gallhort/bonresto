-- Sprint 4: Valeur Ajoutee - F9 Classement popularite, F19 Comparateur, F24 Stats publiques
-- Date: 2026-02-15

-- F9: Colonne popularity_score pour le classement
ALTER TABLE restaurants ADD COLUMN IF NOT EXISTS popularity_score FLOAT DEFAULT 0 AFTER vues_total;
CREATE INDEX idx_restaurants_popularity ON restaurants(popularity_score DESC);

-- Calcul initial du score de popularite
-- Formule: (note_moyenne * 20) + (nb_avis * 5) + (vues_total * 0.01) + (orders_count * 10) + (has_award * 15)
UPDATE restaurants r SET popularity_score = (
  COALESCE(r.note_moyenne, 0) * 20 +
  COALESCE(r.nb_avis, 0) * 5 +
  COALESCE(r.vues_total, 0) * 0.01 +
  (SELECT COUNT(*) FROM orders o WHERE o.restaurant_id = r.id AND o.status = 'delivered') * 10 +
  (SELECT IF(COUNT(*) > 0, 15, 0) FROM restaurant_awards ra WHERE ra.restaurant_id = r.id)
) WHERE r.status = 'validated';

-- ===================================================================
-- Fix horaires: reset complet + horaires standards pour tous les restos
-- Exécuté le 2026-02-07
-- ===================================================================

-- 1. Vider la table (données précédentes corrompues: jour_semaine 1-7 au lieu de 0-6, placeholders 00:00-23:59)
DELETE FROM restaurant_horaires;

-- 2. Insérer des horaires standards pour tous les restos validés
-- Horaires typiques algériens:
--   Lun-Jeu + Sam-Dim: Midi 12:00-15:00, Soir 19:00-23:00
--   Vendredi (jour_semaine=4): Fermé

-- Jours ouverts (0=Lundi, 1=Mardi, 2=Mercredi, 3=Jeudi, 5=Samedi, 6=Dimanche)
INSERT INTO restaurant_horaires (restaurant_id, jour_semaine, ferme, service_continu, ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir)
SELECT id, j.jour, 0, 0, '12:00:00', '15:00:00', '19:00:00', '23:00:00'
FROM restaurants r
CROSS JOIN (SELECT 0 AS jour UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 5 UNION SELECT 6) j
WHERE r.status = 'validated';

-- Vendredi fermé (jour_semaine=4)
INSERT INTO restaurant_horaires (restaurant_id, jour_semaine, ferme, service_continu, ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir)
SELECT id, 4, 1, 0, NULL, NULL, NULL, NULL
FROM restaurants r
WHERE r.status = 'validated';

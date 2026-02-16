-- Fix notes > 5 dans reviews (legacy /10 scale)
-- Les notes /10 sont divisées par 2 pour obtenir /5
UPDATE reviews SET
    note_globale = LEAST(5, GREATEST(0.5, IF(note_globale > 5, ROUND(note_globale / 2, 1), note_globale))),
    note_nourriture = IF(note_nourriture IS NULL, NULL, LEAST(5, GREATEST(0.5, IF(note_nourriture > 5, ROUND(note_nourriture / 2, 1), note_nourriture)))),
    note_service = IF(note_service IS NULL, NULL, LEAST(5, GREATEST(0.5, IF(note_service > 5, ROUND(note_service / 2, 1), note_service)))),
    note_ambiance = IF(note_ambiance IS NULL, NULL, LEAST(5, GREATEST(0.5, IF(note_ambiance > 5, ROUND(note_ambiance / 2, 1), note_ambiance)))),
    note_prix = IF(note_prix IS NULL, NULL, LEAST(5, GREATEST(0.5, IF(note_prix > 5, ROUND(note_prix / 2, 1), note_prix))))
WHERE note_globale > 5 OR note_nourriture > 5 OR note_service > 5 OR note_ambiance > 5 OR note_prix > 5;

-- Unifier la collation des tables comments et restaurants (évite les erreurs COLLATE lors des JOINs)
ALTER TABLE comments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

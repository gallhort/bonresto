-- ═══════════════════════════════════════════════════════════════
-- LOYALTY SYSTEM REDESIGN - Badge updates
-- ═══════════════════════════════════════════════════════════════

-- Update existing badges with better points thresholds
UPDATE badges SET points_required = 0,    icon = '🔍', color = '#6b7280', discount_percent = 0,  description = 'Bienvenue sur LeBonResto !' WHERE slug = 'debutant';
UPDATE badges SET points_required = 100,  icon = '🍽️', color = '#94a3b8', discount_percent = 0,  description = 'Vous commencez a connaitre les bonnes adresses' WHERE slug = 'gourmet';
UPDATE badges SET points_required = 300,  icon = '🥇', color = '#f59e0b', discount_percent = 5,  description = 'Expert en cuisine, -5% chez nos partenaires' WHERE slug = 'connaisseur';
UPDATE badges SET points_required = 700,  icon = '⭐', color = '#8b5cf6', discount_percent = 10, description = 'Votre avis compte ! -10% chez nos partenaires' WHERE slug = 'expert';
UPDATE badges SET points_required = 1200, icon = '👑', color = '#eab308', discount_percent = 15, description = 'VIP LeBonResto, -15% et acces exclusif' WHERE slug = 'ambassadeur';

-- Rename Debutant to Explorateur
UPDATE badges SET name = 'Explorateur', slug = 'explorateur' WHERE slug = 'debutant';
UPDATE users SET badge = 'Explorateur' WHERE badge = 'Débutant';

-- Add new Legendaire badge
INSERT IGNORE INTO badges (name, slug, points_required, icon, color, discount_percent, description)
VALUES ('Legendaire', 'legendaire', 2000, '🔱', '#dc2626', 20, 'Elite LeBonResto, -20% et avantages exclusifs');

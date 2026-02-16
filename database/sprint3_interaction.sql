-- ============================================================
-- Sprint 3 — Interaction & Engagement
-- Features: F25 Messagerie interne, F13 Posts restaurateur, F26 Réponse IA
-- Date: 2026-02-15
-- ============================================================

-- -------------------------------------------------
-- F25: Internal messaging between users
-- -------------------------------------------------
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(200) DEFAULT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    deleted_by_sender TINYINT(1) NOT NULL DEFAULT 0,
    deleted_by_receiver TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_receiver_read (receiver_id, is_read),
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------
-- F13: Restaurant feed / posts by owners
-- -------------------------------------------------
CREATE TABLE IF NOT EXISTS restaurant_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('news', 'promo', 'event', 'photo', 'menu_update') NOT NULL DEFAULT 'news',
    title VARCHAR(200) NOT NULL,
    content TEXT DEFAULT NULL,
    photo_path VARCHAR(255) DEFAULT NULL,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    likes_count INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_restaurant_pinned (restaurant_id, is_pinned, created_at),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS restaurant_post_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES restaurant_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------
-- F26: AI-assisted review response templates
-- -------------------------------------------------
CREATE TABLE IF NOT EXISTS review_response_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category ENUM('positive', 'neutral', 'negative') NOT NULL,
    template_fr TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed templates
INSERT INTO review_response_templates (category, template_fr, sort_order) VALUES
-- Positive (4-5 stars)
('positive', 'Merci beaucoup pour votre avis ! Nous sommes ravis que vous ayez passé un excellent moment chez nous. Au plaisir de vous revoir bientôt !', 1),
('positive', 'Votre retour nous fait chaud au cœur ! Toute l''équipe vous remercie et sera heureuse de vous accueillir à nouveau.', 2),
('positive', 'Merci pour ces mots qui nous motivent à continuer. N''hésitez pas à revenir, nous vous réservons toujours le meilleur accueil !', 3),
('positive', 'Quel plaisir de lire votre avis ! Merci de votre confiance, nous mettons tout en œuvre pour que chaque visite soit un moment de bonheur.', 4),
-- Neutral (3 stars)
('neutral', 'Merci pour votre retour. Nous prenons bonne note de vos remarques et allons travailler pour améliorer votre expérience. N''hésitez pas à nous donner une nouvelle chance !', 5),
('neutral', 'Nous apprécions votre avis et vos suggestions. Notre équipe s''efforce constamment de s''améliorer. Nous espérons vous satisfaire pleinement lors de votre prochaine visite.', 6),
('neutral', 'Merci d''avoir pris le temps de partager votre expérience. Vos remarques sont précieuses et nous aideront à progresser.', 7),
-- Negative (1-2 stars)
('negative', 'Nous sommes sincèrement désolés de votre expérience. Ce n''est pas le niveau de qualité que nous visons. Pourriez-vous nous contacter pour que nous puissions en discuter ?', 8),
('negative', 'Merci pour votre retour honnête. Nous prenons vos remarques très au sérieux et allons immédiatement corriger les points soulevés. Nous espérons pouvoir nous rattraper.', 9),
('negative', 'Nous regrettons sincèrement cette mauvaise expérience. Nous aimerions comprendre ce qui s''est passé et vous offrir une meilleure visite. N''hésitez pas à nous contacter directement.', 10);

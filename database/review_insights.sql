-- Migration: review_insights — Analyse NLP/IA des avis pour enrichir le concierge
-- Date: 2026-02-16

CREATE TABLE IF NOT EXISTS review_insights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    restaurant_id INT NOT NULL,

    -- Trip type détecté depuis le texte
    detected_trip_type VARCHAR(50) DEFAULT NULL COMMENT 'En famille, En couple, Entre amis, Solo, Business',
    trip_type_confidence FLOAT DEFAULT 0 COMMENT '0.0-1.0 confiance de la détection',

    -- Scores occasion détectés (0.0 à 1.0)
    occasion_romantique FLOAT DEFAULT 0,
    occasion_familial FLOAT DEFAULT 0,
    occasion_festif FLOAT DEFAULT 0,
    occasion_business FLOAT DEFAULT 0,

    -- Sentiment extrait du texte (1.0-5.0 scale, NULL si non détecté)
    sentiment_ambiance FLOAT DEFAULT NULL,
    sentiment_service FLOAT DEFAULT NULL,
    sentiment_food FLOAT DEFAULT NULL,
    sentiment_price FLOAT DEFAULT NULL,

    -- Mots-clés détectés (JSON array: ["intime","chaleureux","vue mer"])
    keywords JSON DEFAULT NULL,

    -- Méta
    analyzed_by ENUM('nlp', 'groq') DEFAULT 'nlp',
    analyzed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uq_review (review_id),
    INDEX idx_restaurant (restaurant_id),
    INDEX idx_trip_type (detected_trip_type),
    INDEX idx_analyzed (analyzed_by),
    FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

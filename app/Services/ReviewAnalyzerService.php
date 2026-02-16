<?php

namespace App\Services;

use PDO;

/**
 * ReviewAnalyzerService — Analyse NLP + Groq des avis pour enrichir le concierge
 *
 * Extrait depuis le texte des avis :
 * - trip_type (En famille, En couple, Entre amis, Solo, Business)
 * - occasions (romantique, familial, festif, business) scores 0-1
 * - sentiment ambiance/service/food/price (1-5)
 * - mots-clés pertinents
 *
 * Deux moteurs : NLP regex (toujours dispo) + Groq IA (si API key, plus précis)
 */
class ReviewAnalyzerService
{
    private PDO $db;
    private GroqService $groq;

    /** NLP regex patterns for trip_type detection */
    private const TRIP_PATTERNS = [
        'En famille' => [
            'high'   => '/\b(en famille|avec (mes|nos|les) enfants?|avec (mon|ma|mes) (fils|fille|bébé|bebe)|repas familial|sortie familiale|avec (ma |mon )?(mère|mere|père|pere|papa|mama|maman)|avec mes parents|avec ma famille|mon mariage|pour les familles|idéal.{0,10}famille|parfait.{0,10}famille)\b/iu',
            'medium' => '/\b(famille|enfants?|bébé|bebe|kids?|fils|fille|familial|familiale|ma mère|mon père|ma mere|mon pere|maman|papa|mes parents|ma soeur|mon frère|mon fr[eè]re|ma s[oœ]ur|grand[- ]?(mère|mere|père|pere)|ma tante|mon oncle|mes cousins?|ma cousine|salle.{0,10}familles?|espace.{0,10}familles?|ramadan|iftar|ftour|menu enfant|chaise haute|aire de jeu)\b/iu',
        ],
        'En couple' => [
            'high'   => '/\b(en couple|en amoureux|avec (mon|ma) (copain|copine|petit copain|petite copine|mari|femme|conjoint|fiancé|fiancée|chéri|chérie|moitié)|tête[- ]à[- ]tête|tete[- ]a[- ]tete|dîner romantique|diner romantique|ma petite copine|mon petit copain|notre anniversaire|anniversaire de mariage)\b/iu',
            'medium' => '/\b(couple|amoureux|romantique|saint[- ]valentin|rendez[- ]vous|mon mari|ma femme|ma copine|mon copain|mon fiancé|ma fiancée)\b/iu',
        ],
        'Entre amis' => [
            'high'   => '/\b(entre ami(s|es)?|avec (mes|des|les) ami(s|es)?|sortie entre (pote|ami)|soirée entre (pote|ami)|en groupe|entre potes?)\b/iu',
            'medium' => '/\b(amis?|potes?|soirée|soiree|fête|fete|groupe|retrouvailles|bande|copains?|entre nous)\b/iu',
        ],
        'Business' => [
            'high'   => '/\b(repas d\'affaires?|déjeuner (d\'affaires?|professionnel)|diner (d\'affaires?|professionnel)|avec (mes|des|les) collègues?|réunion|reunion|repas (de |d\')équipe)\b/iu',
            'medium' => '/\b(collègues?|collegues?|affaires?|business)\b/iu',
        ],
        'Solo' => [
            'high'   => '/\b(seul(e)?|tout(e)? seul(e)?|en solo)\b/iu',
            'medium' => '/\b(solo|solitaire)\b/iu',
        ],
    ];

    /** Occasion detection patterns */
    private const OCCASION_PATTERNS = [
        'romantique' => [
            'positive' => '/\b(romantique|intime|chaleureux|chaleureuse|tamisé|tamisée|bougie|chandelle|amoureux|amoureuse|tête[- ]à[- ]tête|elegant|élégant|raffiné|raffinée|cosy|belle déco|joli cadre|vue (magnifique|superbe|mer|mer))\b/iu',
            'negative' => '/\b(bruyant|bruit|bondé|froid|impersonnel|salle (pleine|bondée)|fast[- ]food|rapide)\b/iu',
        ],
        'familial' => [
            'positive' => '/\b(enfants?|famille|familial|familiale|menu enfant|chaise (haute|bébé)|aire de jeu|espace (jeux|enfant)|accueil.*enfant|kids? friendly|terrain|jardin)\b/iu',
            'negative' => '/\b(pas (pour|adapté|recommandé).*enfant|interdit.*enfant|pas familial)\b/iu',
        ],
        'festif' => [
            'positive' => '/\b(fête|fete|soirée|soiree|ambiance|musique|dj|animation|anniversaire|célébration|celebration|événement|evenement|bonne humeur|convivial|jovial|super ambiance|fêter|f[eê]t[eé])\b/iu',
            'negative' => '/\b(calme|silencieux|pas d\'ambiance|ennuyeux|mort|vide)\b/iu',
        ],
        'business' => [
            'positive' => '/\b(professionnel|calme|discret|service impeccable|wifi|salle (privée|prive)|réunion|reunion|affaires?|conférence|présentation)\b/iu',
            'negative' => '/\b(bruyant|bruit|bondé|désorganisé|lent|attente)\b/iu',
        ],
    ];

    /** Sentiment ambiance patterns */
    private const AMBIANCE_POSITIVE = '/\b(chaleureux|chaleureuse|intime|cosy|agréable|agreable|magnifique|superbe|belle déco|beau cadre|joli|propre|élégant|elegant|raffiné|confortable|calme|relaxant|paisible|vue (mer|superbe|magnifique)|terrasse|lumineux|spacieux|accueillant|charmant|authentique)\b/iu';
    private const AMBIANCE_NEGATIVE = '/\b(bruyant|bruit|sale|froid|sombre|étroit|bondé|vide|impersonnel|vétuste|vetuste|moche|déco (nulle|vieille)|mal entretenu|odeur|puant|inconfortable)\b/iu';

    private const SERVICE_POSITIVE = '/\b(accueil (chaleureux|excellent|formidable|super)|service (rapide|impeccable|excellent|top|parfait)|serveur (gentil|souriant|aimable|professionnel)|personnel (agréable|souriant|serviable)|bien accueilli|sourire|poli|professionnel)\b/iu';
    private const SERVICE_NEGATIVE = '/\b(service (lent|nul|mauvais|désagréable|catastrophique)|mal accueilli|serveur (froid|désagréable|impoli)|attente (longue|interminable)|ignoré|impoli|arrogant|mal parler)\b/iu';

    private const FOOD_POSITIVE = '/\b(délicieux|delicieux|excellent|savoureux|succulent|copieux|frais|fait maison|goûteux|raffiné|généreux|genéreux|bien cuisiné|bien assaisonné|bonne cuisine|top|régal|exquis)\b/iu';
    private const FOOD_NEGATIVE = '/\b(mauvais|immangeable|sec|fade|froid|réchauffé|pas frais|portion (petite|ridicule)|déçu|décevant|insipide|trop (salé|cuit|gras))\b/iu';

    private const PRICE_POSITIVE = '/\b(bon rapport|rapport qualité[- ]prix|pas cher|abordable|raisonnable|correct|bon prix|prix (doux|correct|raisonnable))\b/iu';
    private const PRICE_NEGATIVE = '/\b(cher|trop cher|arnaque|hors de prix|excessif|pas donné|surpayé|overpriced)\b/iu';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->groq = new GroqService();
    }

    /**
     * Analyze a single review
     * @return array The insight data that was stored
     */
    public function analyzeReview(int $reviewId): ?array
    {
        // Fetch review
        $stmt = $this->db->prepare("
            SELECT r.id, r.restaurant_id, r.message, r.note_globale, r.note_ambiance,
                   r.note_service, r.note_nourriture, r.note_prix, r.trip_type, r.source
            FROM reviews r
            WHERE r.id = :id AND r.status = 'approved'
        ");
        $stmt->execute([':id' => $reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$review || empty($review['message']) || mb_strlen($review['message']) < 10) {
            return null;
        }

        // Try Groq first, fallback to NLP
        $insight = null;
        if ($this->groq->isAvailable()) {
            $insight = $this->analyzeWithGroq($review);
        }

        if ($insight === null) {
            $insight = $this->analyzeWithNLP($review);
        }

        // Store in review_insights
        $this->storeInsight($review['id'], $review['restaurant_id'], $insight);

        return $insight;
    }

    /**
     * Batch analyze unprocessed reviews (or force re-analyze all)
     * @param int  $limit Max reviews per batch
     * @param bool $force Re-analyze even if insights exist (uses NLP only for speed)
     * @return int Number of reviews analyzed
     */
    public function analyzeBatch(int $limit = 100, bool $force = false): int
    {
        if ($force) {
            $stmt = $this->db->prepare("
                SELECT r.id
                FROM reviews r
                WHERE r.status = 'approved'
                  AND r.message IS NOT NULL
                  AND LENGTH(r.message) >= 15
                ORDER BY r.id ASC
                LIMIT :lim
            ");
        } else {
            $stmt = $this->db->prepare("
                SELECT r.id
                FROM reviews r
                LEFT JOIN review_insights ri ON ri.review_id = r.id
                WHERE r.status = 'approved'
                  AND r.message IS NOT NULL
                  AND LENGTH(r.message) >= 15
                  AND ri.id IS NULL
                ORDER BY r.id ASC
                LIMIT :lim
            ");
        }
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $count = 0;
        foreach ($ids as $id) {
            $result = $this->analyzeReview((int)$id);
            if ($result !== null) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Enrich reviews.trip_type from review_insights for reviews without trip_type
     * @return int Number of reviews updated
     */
    public function enrichTripTypes(): int
    {
        $stmt = $this->db->prepare("
            UPDATE reviews r
            INNER JOIN review_insights ri ON ri.review_id = r.id
            SET r.trip_type = ri.detected_trip_type
            WHERE (r.trip_type IS NULL OR r.trip_type = '')
              AND ri.detected_trip_type IS NOT NULL
              AND ri.trip_type_confidence >= 0.6
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Analyze with Groq AI (precise, understands context)
     */
    private function analyzeWithGroq(array $review): ?array
    {
        $text = mb_substr($review['message'], 0, 500);

        $systemPrompt = <<<'PROMPT'
Tu analyses un avis de restaurant en Algerie. Extrais les informations suivantes au format JSON strict.

trip_type: "En famille", "En couple", "Entre amis", "Solo", "Business", ou null si pas clair
trip_confidence: 0.0 a 1.0 (certitude)
occasion_romantique: 0.0 a 1.0 (indices romantiques dans le texte)
occasion_familial: 0.0 a 1.0 (indices familiaux)
occasion_festif: 0.0 a 1.0 (indices festifs/soiree)
occasion_business: 0.0 a 1.0 (indices professionnels)
sentiment_ambiance: 1.0 a 5.0 (ambiance/deco/cadre, null si pas mentionne)
sentiment_service: 1.0 a 5.0 (service/accueil, null si pas mentionne)
sentiment_food: 1.0 a 5.0 (nourriture/cuisine, null si pas mentionne)
sentiment_price: 1.0 a 5.0 (rapport qualite-prix, null si pas mentionne)
keywords: tableau de 0-8 mots-cles pertinents extraits du texte (en francais). Privilegier: vue_mer, rooftop, calme, cosy, bruyant, belle_deco, parking, wifi, brunch, petit_dejeuner, ftour, livraison, emporter, healthy, salade, vegetarien, tard, ramadan, grand_groupe, salle_privee, jeux_enfants, menu_enfant, traditionnel, plat_algerien, nouveau, terrasse, fait_maison, copieux

Reponds UNIQUEMENT en JSON valide, sans texte autour.
PROMPT;

        $response = $this->groq->extractIntent($text, []); // Reuse the chat method indirectly
        // Actually, let's use a dedicated call via GroqService
        $rawResponse = $this->callGroqForAnalysis($systemPrompt, $text);

        if ($rawResponse === null) {
            return null;
        }

        // Parse response
        $data = $this->parseGroqResponse($rawResponse);
        if ($data === null) {
            return null;
        }

        return [
            'detected_trip_type' => $data['trip_type'] ?? null,
            'trip_type_confidence' => min(1.0, max(0, (float)($data['trip_confidence'] ?? 0))),
            'occasion_romantique' => min(1.0, max(0, (float)($data['occasion_romantique'] ?? 0))),
            'occasion_familial' => min(1.0, max(0, (float)($data['occasion_familial'] ?? 0))),
            'occasion_festif' => min(1.0, max(0, (float)($data['occasion_festif'] ?? 0))),
            'occasion_business' => min(1.0, max(0, (float)($data['occasion_business'] ?? 0))),
            'sentiment_ambiance' => isset($data['sentiment_ambiance']) ? min(5, max(1, (float)$data['sentiment_ambiance'])) : null,
            'sentiment_service' => isset($data['sentiment_service']) ? min(5, max(1, (float)$data['sentiment_service'])) : null,
            'sentiment_food' => isset($data['sentiment_food']) ? min(5, max(1, (float)$data['sentiment_food'])) : null,
            'sentiment_price' => isset($data['sentiment_price']) ? min(5, max(1, (float)$data['sentiment_price'])) : null,
            'keywords' => array_slice((array)($data['keywords'] ?? []), 0, 8),
            'analyzed_by' => 'groq',
        ];
    }

    /**
     * Analyze with NLP regex (always available, fast)
     */
    private function analyzeWithNLP(array $review): array
    {
        $text = $review['message'];

        // Detect trip_type
        $tripType = null;
        $tripConfidence = 0;
        foreach (self::TRIP_PATTERNS as $type => $patterns) {
            if (preg_match($patterns['high'], $text)) {
                $tripType = $type;
                $tripConfidence = 0.9;
                break;
            }
        }
        if ($tripType === null) {
            foreach (self::TRIP_PATTERNS as $type => $patterns) {
                if (preg_match($patterns['medium'], $text)) {
                    $tripType = $type;
                    $tripConfidence = 0.6;
                    break;
                }
            }
        }

        // Detect occasion scores
        $occasions = [];
        foreach (self::OCCASION_PATTERNS as $occasion => $patterns) {
            $posCount = preg_match_all($patterns['positive'], $text);
            $negCount = preg_match_all($patterns['negative'], $text);
            $score = min(1.0, max(0, ($posCount * 0.3) - ($negCount * 0.2)));
            $occasions[$occasion] = $score;
        }

        // Sentiment analysis
        $sentimentAmbiance = $this->computeSentiment($text, self::AMBIANCE_POSITIVE, self::AMBIANCE_NEGATIVE, (float)($review['note_ambiance'] ?? 0));
        $sentimentService = $this->computeSentiment($text, self::SERVICE_POSITIVE, self::SERVICE_NEGATIVE, (float)($review['note_service'] ?? 0));
        $sentimentFood = $this->computeSentiment($text, self::FOOD_POSITIVE, self::FOOD_NEGATIVE, (float)($review['note_nourriture'] ?? 0));
        $sentimentPrice = $this->computeSentiment($text, self::PRICE_POSITIVE, self::PRICE_NEGATIVE, (float)($review['note_prix'] ?? 0));

        // Extract keywords
        $keywords = $this->extractKeywords($text);

        return [
            'detected_trip_type' => $tripType,
            'trip_type_confidence' => $tripConfidence,
            'occasion_romantique' => $occasions['romantique'] ?? 0,
            'occasion_familial' => $occasions['familial'] ?? 0,
            'occasion_festif' => $occasions['festif'] ?? 0,
            'occasion_business' => $occasions['business'] ?? 0,
            'sentiment_ambiance' => $sentimentAmbiance,
            'sentiment_service' => $sentimentService,
            'sentiment_food' => $sentimentFood,
            'sentiment_price' => $sentimentPrice,
            'keywords' => $keywords,
            'analyzed_by' => 'nlp',
        ];
    }

    /**
     * Compute a sentiment score (1-5) from text patterns + existing note
     */
    private function computeSentiment(string $text, string $posPattern, string $negPattern, float $existingNote): ?float
    {
        $posCount = preg_match_all($posPattern, $text);
        $negCount = preg_match_all($negPattern, $text);

        if ($posCount === 0 && $negCount === 0) {
            // No textual signal — use existing note if available
            return $existingNote > 0 ? $existingNote : null;
        }

        // Text-based sentiment: start from existing note or 3.0
        $base = $existingNote > 0 ? $existingNote : 3.0;
        $textScore = $base + ($posCount * 0.4) - ($negCount * 0.5);
        return min(5.0, max(1.0, round($textScore, 1)));
    }

    /**
     * Extract meaningful keywords from review text
     * Keywords feed into ScoringService for the 22 score columns
     */
    private function extractKeywords(string $text): array
    {
        $keywords = [];
        $kwPatterns = [
            // ═══ Existing general signals ═══
            'terrasse'       => '/\bterrasse\b/iu',
            'fait maison'    => '/\bfait maison\b/iu',
            'copieux'        => '/\bcopieux\b/iu',
            'propre'         => '/\bpropre\b/iu',
            'chaleureux'     => '/\bchaleureu(x|se)\b/iu',
            'bon accueil'    => '/\b(bon|super|excellent|formidable) accueil\b/iu',
            'service rapide' => '/\bservice rapide\b/iu',
            'attente longue' => '/\b(attente|longue attente|attente (longue|interminable))\b/iu',
            'bon rapport'    => '/\b(bon )?rapport qualit(é|e)[- ]prix\b/iu',
            'cher'           => '/\btrop cher\b/iu',
            'frais'          => '/\b(produits? )?frais\b/iu',
            'délicieux'      => '/\bd(é|e)licieu(x|se)\b/iu',

            // ═══ score_vue signals ═══
            'vue_mer'        => '/\bvue\s+(sur\s+la\s+)?mer\b/iu',
            'vue_panoramique'=> '/\b(panoram|vue\s+(magnifique|superbe|imprenable|splendide|incroyable))\b/iu',
            'rooftop'        => '/\b(rooftop|toit[- ]?terrasse|roof)\b/iu',

            // ═══ score_calme signals ═══
            'calme'          => '/\b(calme|paisible|tranquille|serein|silencieu[sx]e?)\b/iu',
            'cosy'           => '/\b(cosy|cozy|feutr[eé]|tamisé|apaisant)\b/iu',
            'bruyant'        => '/\bbruyant\b/iu',
            'intime'         => '/\bintime\b/iu',

            // ═══ score_instagrammable signals ═══
            'belle_deco'     => '/\b(belle?\s+d[eé]co|design|instagramm?able|photog[eé]nique|magnifique\s+cadre|esth[eé]tique)\b/iu',
            'cadre agréable' => '/\b(beau|joli|superbe|magnifique)\s+(cadre|endroit|lieu)\b/iu',

            // ═══ score_parking signals ═══
            'parking'        => '/\bparking\b/iu',

            // ═══ score_wifi_travail signals ═══
            'wifi'           => '/\bwifi\b/iu',
            'travailler'     => '/\b(travaill|coworking|co[- ]?working|laptop|ordinateur|bosser)\b/iu',

            // ═══ score_brunch signals ═══
            'brunch'         => '/\bbrunch\b/iu',
            'petit_dejeuner' => '/\b(petit[- ]?d[eé]jeuner|breakfast)\b/iu',
            'ftour'          => '/\b(ftour|f[ou]tour|iftar)\b/iu',

            // ═══ score_livraison signals ═══
            'livraison'      => '/\b(livr[eé]|livraison|delivery|re[cç]u\s+[àa]\s+domicile)\b/iu',
            'emporter'       => '/\b([àa]\s+emporter|takeaway|take[- ]?away)\b/iu',

            // ═══ score_healthy signals ═══
            'healthy'        => '/\b(healthy|sant[eé]|di[eé]t[eé]tique|l[eé]ger)\b/iu',
            'salade'         => '/\b(salade|salad|bowl|poke)\b/iu',
            'vegetarien'     => '/\b(v[eé]g[eé]tarien|vegan|v[eé]g[eé]tal|sans viande)\b/iu',

            // ═══ score_ouvert_tard signals ═══
            'tard'           => '/\b(ouvert\s+tard|apr[eè]s\s+minuit|nocturne|tard\s+le\s+soir)\b/iu',

            // ═══ score_ramadan signals ═══
            'ramadan'        => '/\b(ramadan|s[ou]hour|shour|menu\s+(ramadan|iftar|ftour))\b/iu',

            // ═══ score_groupe signals ═══
            'grand_groupe'   => '/\b(grand\s+groupe|grande\s+table|1[05]\s+personnes?|20\s+personnes?|banquet)\b/iu',
            'salle_privee'   => '/\b(salle?\s+priv[eé]e?|salon\s+priv[eé]|privatiser)\b/iu',

            // ═══ score_enfants signals ═══
            'jeux_enfants'   => '/\b(jeu[x]?\s+(pour\s+)?enfants?|toboggan|trampoline|gonflable|animation\s+enfant)\b/iu',
            'menu_enfant'    => '/\b(menu\s+enfant|portion\s+enfant|kids?\s+menu)\b/iu',
            'espace enfant'  => '/\b(espace\s+(enfant|jeux)|aire\s+de\s+jeu)\b/iu',

            // ═══ score_traditionnel signals ═══
            'traditionnel'   => '/\b(tradition[n]?el|authentique|typique|artisanal|comme\s+chez\s+(ma\s+)?m[eè]re)\b/iu',
            'plat_algerien'  => '/\b(couscous|tajine|chorba|rechta|chakhchoukha|bourek|garantita|karantika|makroud|zlabia|rfiss|berkoukes|trida|tlitli|m\'?hajeb|mhadjeb)\b/iu',

            // ═══ score_nouveau signals ═══
            'nouveau'        => '/\b(nouveau|nouvelle?\s+ouverture|vient\s+d\'ouvrir|inaugur|tout\s+neuf)\b/iu',
        ];

        foreach ($kwPatterns as $kw => $pattern) {
            if (preg_match($pattern, $text)) {
                $keywords[] = $kw;
            }
            if (count($keywords) >= 8) break;
        }

        return $keywords;
    }

    /**
     * Call Groq API for review analysis
     */
    private function callGroqForAnalysis(string $systemPrompt, string $text): ?string
    {
        if (!$this->groq->isAvailable()) {
            return null;
        }

        $apiKey = getenv('GROQ_API_KEY') ?: null;
        if (empty($apiKey)) {
            return null;
        }

        $payload = json_encode([
            'model' => 'llama-3.1-8b-instant',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.1,
            'max_tokens' => 300,
        ]);

        $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 429) {
            GroqService::resetRateLimit(); // Will set flag via next isAvailable check
            return null;
        }

        if ($httpCode !== 200 || $response === false) {
            return null;
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    /**
     * Parse Groq response JSON
     */
    private function parseGroqResponse(string $text): ?array
    {
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Try code fences
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) return $decoded;
        }

        // Try first { ... }
        if (preg_match('/\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) return $decoded;
        }

        return null;
    }

    /**
     * Store insight in review_insights table
     */
    private function storeInsight(int $reviewId, int $restaurantId, array $insight): void
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO review_insights
                    (review_id, restaurant_id, detected_trip_type, trip_type_confidence,
                     occasion_romantique, occasion_familial, occasion_festif, occasion_business,
                     sentiment_ambiance, sentiment_service, sentiment_food, sentiment_price,
                     keywords, analyzed_by, analyzed_at)
                VALUES
                    (:rid, :restid, :trip, :conf,
                     :rom, :fam, :fest, :biz,
                     :amb, :serv, :food, :price,
                     :kw, :by, NOW())
                ON DUPLICATE KEY UPDATE
                    detected_trip_type = VALUES(detected_trip_type),
                    trip_type_confidence = VALUES(trip_type_confidence),
                    occasion_romantique = VALUES(occasion_romantique),
                    occasion_familial = VALUES(occasion_familial),
                    occasion_festif = VALUES(occasion_festif),
                    occasion_business = VALUES(occasion_business),
                    sentiment_ambiance = VALUES(sentiment_ambiance),
                    sentiment_service = VALUES(sentiment_service),
                    sentiment_food = VALUES(sentiment_food),
                    sentiment_price = VALUES(sentiment_price),
                    keywords = VALUES(keywords),
                    analyzed_by = VALUES(analyzed_by),
                    analyzed_at = NOW()
            ");
            $stmt->execute([
                ':rid' => $reviewId,
                ':restid' => $restaurantId,
                ':trip' => $insight['detected_trip_type'],
                ':conf' => $insight['trip_type_confidence'],
                ':rom' => $insight['occasion_romantique'],
                ':fam' => $insight['occasion_familial'],
                ':fest' => $insight['occasion_festif'],
                ':biz' => $insight['occasion_business'],
                ':amb' => $insight['sentiment_ambiance'],
                ':serv' => $insight['sentiment_service'],
                ':food' => $insight['sentiment_food'],
                ':price' => $insight['sentiment_price'],
                ':kw' => !empty($insight['keywords']) ? json_encode($insight['keywords'], JSON_UNESCAPED_UNICODE) : null,
                ':by' => $insight['analyzed_by'],
            ]);
        } catch (\Exception $e) {
            error_log("[ReviewAnalyzer] Store error: " . $e->getMessage());
        }
    }
}

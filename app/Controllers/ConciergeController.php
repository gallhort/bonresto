<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\GroqService;
use App\Services\RateLimiter;
use App\Services\ReviewAnalyzerService;
use App\Services\ScoringService;
use PDO;

/**
 * Concierge v2 — Intent-based contextual discovery engine
 *
 * Upgrade from v1 regex chatbot to a scoring-powered recommendation engine:
 * - Multi-criteria extraction from a single message (city + cuisine + occasion + budget)
 * - Dynamic contextual scoring via ScoringService (weighted, explainable)
 * - Pre-indexed occasion scores (familial, romantique, business, rapide, festif...)
 * - Feedback loop: recommendation → click → conversion tracking
 * - Conversational responses with "why this restaurant" explanations
 */
class ConciergeController extends Controller
{
    private ScoringService $scoring;
    private GroqService $groq;

    /** Occasion keywords → occasion column mapping
     *  IMPORTANT: Multi-word patterns FIRST — strpos matches first hit in iteration order.
     *  This prevents "pot de depart collegue" matching "collegue→business" before "pot de depart→festif".
     *  Covers: Google search patterns, TripAdvisor occasions, Algerian context.
     */
    private const OCCASION_MAP = [
        // ═══ MULTI-WORD (3+ words) — highest specificity ═══
        'demande en mariage'  => 'romantique',
        'diner aux chandelles' => 'romantique',
        'enterrement de vie'  => 'festif',
        'dejeuner d\'equipe'  => 'business',
        'repas d\'affaires'   => 'business',
        'menu degustation'    => 'gastronomique',
        'ouvert tard le soir' => 'ouvert_tard',
        'apres minuit'        => 'ouvert_tard',
        'manger sur le tard'  => 'ouvert_tard',
        'cuisine fait maison' => 'traditionnel',
        'plat algerien'       => 'traditionnel',
        'comme chez mama'     => 'traditionnel',
        'espace pour enfants' => 'enfants',
        'jeux pour enfants'   => 'enfants',
        'salle pour groupe'   => 'groupe',
        'grande table'        => 'groupe',
        'cafe pour travailler' => 'wifi_travail',
        'endroit pour bosser' => 'wifi_travail',
        'bon pour la sante'   => 'healthy',

        // ═══ MULTI-WORD (2 words) — before single-word ═══
        'saint valentin'  => 'romantique',
        'tete a tete'     => 'romantique',
        'en amoureux'     => 'romantique',
        'kid friendly'    => 'enfants',
        'espace jeux'     => 'enfants',
        'menu enfant'     => 'enfants',
        'chaise bebe'     => 'enfants',
        'aire de jeu'     => 'enfants',
        'team building'   => 'business',
        'sur le pouce'    => 'rapide',
        'pot de depart'   => 'festif',
        'vue mer'         => 'vue',
        'bord de mer'     => 'vue',
        'front de mer'    => 'vue',
        'vue panoramique' => 'vue',
        'plein air'       => 'terrasse',
        'petit budget'    => 'budget',
        'pas cher'        => 'budget',
        'bon marche'      => 'budget',
        'bon plan'        => 'budget',
        'prix bas'        => 'budget',
        'haut de gamme'   => 'gastronomique',
        'fine dining'     => 'gastronomique',
        'salle privee'    => 'groupe',
        'salon prive'     => 'groupe',
        'grand groupe'    => 'groupe',
        'ouvert tard'     => 'ouvert_tard',
        'petit dejeuner'  => 'brunch',
        'menu ramadan'    => 'ramadan',
        'menu ftour'      => 'ramadan',
        'menu iftar'      => 'ramadan',
        'sans gluten'     => 'healthy',

        // ═══ SINGLE-WORD — Familial ═══
        'famille'         => 'familial',
        'familial'        => 'familial',

        // ═══ SINGLE-WORD — Enfants (split from familial) ═══
        'enfants'         => 'enfants',
        'enfant'          => 'enfants',
        'bebe'            => 'enfants',
        'kids'            => 'enfants',

        // ═══ SINGLE-WORD — Romantique ═══
        'romantique'      => 'romantique',
        'couple'          => 'romantique',
        'amoureux'        => 'romantique',
        'anniversaire'    => 'romantique',
        'mariage'         => 'romantique',
        'fiancailles'     => 'romantique',
        'intime'          => 'romantique',

        // ═══ SINGLE-WORD — Business ═══
        'business'        => 'business',
        'affaires'        => 'business',
        'reunion'         => 'business',
        'collegue'        => 'business',
        'collegues'       => 'business',
        'seminaire'       => 'business',
        'afterwork'       => 'business',

        // ═══ SINGLE-WORD — Rapide ═══
        'rapide'          => 'rapide',
        'express'         => 'rapide',
        'vite'            => 'rapide',
        'pause'           => 'rapide',
        'quick'           => 'rapide',
        'presse'          => 'rapide',
        'snack'           => 'rapide',

        // ═══ SINGLE-WORD — Festif ═══
        'celebration'     => 'festif',
        'retrouvailles'   => 'festif',
        'fete'            => 'festif',
        'festif'          => 'festif',
        'soiree'          => 'festif',
        'sortie'          => 'festif',
        'amis'            => 'festif',
        'bapteme'         => 'festif',
        'communion'       => 'festif',
        'apero'           => 'festif',
        'evg'             => 'festif',
        'evjf'            => 'festif',

        // ═══ SINGLE-WORD — Terrasse ═══
        'terrasse'        => 'terrasse',
        'dehors'          => 'terrasse',
        'exterieur'       => 'terrasse',
        'jardin'          => 'terrasse',

        // ═══ SINGLE-WORD — Vue (split from terrasse) ═══
        'rooftop'         => 'vue',
        'vue'             => 'vue',
        'panoramique'     => 'vue',
        'panorama'        => 'vue',

        // ═══ SINGLE-WORD — Budget ═══
        'economique'      => 'budget',
        'abordable'       => 'budget',
        'promo'           => 'budget',

        // ═══ SINGLE-WORD — Gastronomique ═══
        'gastronomique'   => 'gastronomique',
        'luxe'            => 'gastronomique',
        'chic'            => 'gastronomique',
        'standing'        => 'gastronomique',
        'raffinement'     => 'gastronomique',
        'etoile'          => 'gastronomique',
        'degustation'     => 'gastronomique',

        // ═══ SINGLE-WORD — Brunch ═══
        'brunch'          => 'brunch',
        'breakfast'       => 'brunch',
        'ftour'           => 'brunch',

        // ═══ SINGLE-WORD — Livraison ═══
        'livraison'       => 'livraison',
        'delivery'        => 'livraison',
        'emporter'        => 'livraison',

        // ═══ SINGLE-WORD — Healthy ═══
        'healthy'         => 'healthy',
        'sain'            => 'healthy',
        'dietetique'      => 'healthy',
        'leger'           => 'healthy',
        'vegetarien'      => 'healthy',
        'vegan'           => 'healthy',

        // ═══ SINGLE-WORD — Ouvert tard ═══
        'nocturne'        => 'ouvert_tard',
        'nuit'            => 'ouvert_tard',

        // ═══ SINGLE-WORD — Instagrammable ═══
        'instagrammable'  => 'instagrammable',
        'photogenique'    => 'instagrammable',
        'esthetique'      => 'instagrammable',

        // ═══ SINGLE-WORD — Calme ═══
        'calme'           => 'calme',
        'paisible'        => 'calme',
        'tranquille'      => 'calme',
        'cosy'            => 'calme',
        'zen'             => 'calme',

        // ═══ SINGLE-WORD — Nouveau ═══
        'nouveau'         => 'nouveau',
        'nouvelle'        => 'nouveau',
        'tendance'        => 'nouveau',

        // ═══ SINGLE-WORD — Parking ═══
        'parking'         => 'parking',
        'stationner'      => 'parking',

        // ═══ SINGLE-WORD — Ramadan ═══
        'ramadan'         => 'ramadan',
        'iftar'           => 'ramadan',
        'shour'           => 'ramadan',
        'sohour'          => 'ramadan',

        // ═══ SINGLE-WORD — Groupe ═══
        'groupe'          => 'groupe',
        'banquet'         => 'groupe',
        'privatiser'      => 'groupe',

        // ═══ SINGLE-WORD — Wifi/Travail ═══
        'coworking'       => 'wifi_travail',

        // ═══ SINGLE-WORD — Traditionnel ═══
        'traditionnel'    => 'traditionnel',
        'authentique'     => 'traditionnel',
        'typique'         => 'traditionnel',
        'artisanal'       => 'traditionnel',
    ];

    /** Amenity keywords → DB column */
    private const AMENITY_MAP = [
        'wifi'           => 'wifi',
        'parking'        => 'parking',
        'climatisation'  => 'air_conditioning',
        'clim'           => 'air_conditioning',
        'pmr'            => 'handicap_access',
        'handicap'       => 'handicap_access',
        'accessible'     => 'handicap_access',
        'fauteuil roulant' => 'handicap_access',
        'espace jeux'    => 'game_zone',
        'aire de jeu'    => 'game_zone',
        'jeux'           => 'game_zone',
        'salon prive'    => 'private_room',
        'salle privee'   => 'private_room',
        'prive'          => 'private_room',
        'animaux'        => 'pets_allowed',
        'chien'          => 'pets_allowed',
        'chat'           => 'pets_allowed',
        'chaise bebe'    => 'baby_chair',
        'chaise haute'   => 'baby_chair',
        'voiturier'      => 'valet_service',
        'valet'          => 'valet_service',
        'livraison'      => 'delivery',
        'delivery'       => 'delivery',
    ];

    /** Cuisine keywords (single words — multi-word handled in extractCuisine) */
    private const CUISINE_KEYWORDS = [
        // Nationalités
        'italien', 'italienne', 'chinois', 'chinoise', 'japonais', 'japonaise',
        'francais', 'francaise', 'algerien', 'algerienne', 'marocain', 'marocaine',
        'tunisien', 'tunisienne', 'libanais', 'libanaise', 'turc', 'turque',
        'indien', 'indienne', 'mexicain', 'mexicaine', 'americain', 'americaine',
        'thai', 'thailandais', 'vietnamien', 'coreen', 'syrien', 'syrienne',
        'kabyle', 'oriental', 'occidental', 'asiatique', 'africain',
        // Plats / types
        'pizza', 'pizzeria', 'burger', 'sushi', 'kebab', 'tacos',
        'grillades', 'grillade', 'poisson', 'steak', 'steakhouse',
        'couscous', 'tajine', 'chawarma', 'shawarma',
        'crepe', 'creperie', 'patisserie', 'boulangerie',
        'ramen', 'poke', 'brunch', 'tapas',
        // Régime / style
        'vegetarien', 'vegan', 'bio', 'traditionnel',
        // Boissons / lieux
        'cafe', 'coffee', 'glacier', 'glace', 'salon de the',
    ];

    /**
     * Page chatbot (kept for direct /concierge access)
     * GET /concierge
     */
    public function chat(): void
    {
        $this->render('concierge.index', [
            'title' => 'Concierge IA - LeBonResto',
        ]);
    }

    /**
     * v2 API endpoint
     * POST /api/concierge/ask
     *
     * Input JSON: { message, session_id, lat?, lng? }
     * Output JSON: { success, session_id, intent, response, data, suggestions, recommendations[] }
     */
    public function ask(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $rateLimitKey = $this->isAuthenticated()
            ? 'concierge_user_' . (int)$_SESSION['user']['id']
            : 'concierge_ip_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        if (!RateLimiter::attempt($rateLimitKey, 20, 60)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de messages. Attendez un moment.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');
        $sessionId = trim($input['session_id'] ?? '');
        $userLat = isset($input['lat']) ? (float)$input['lat'] : null;
        $userLng = isset($input['lng']) ? (float)$input['lng'] : null;

        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le message ne peut pas etre vide']);
            return;
        }

        if (mb_strlen($message) > 500) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Message trop long (max 500 caracteres)']);
            return;
        }

        if (empty($sessionId)) {
            $sessionId = bin2hex(random_bytes(16));
        }

        // Initialize services
        $this->scoring = new ScoringService($this->db);
        $this->groq = new GroqService();

        // ── Step 1: Parse intent — Groq AI with NLP fallback ──
        $parsed = $this->parseIntentWithAI($message);

        // Inject geo context
        if ($userLat && $userLng) {
            $parsed['context']['lat'] = $userLat;
            $parsed['context']['lng'] = $userLng;
        }

        // ── Step 2: Route to handler (with fallback cascade tracking) ──
        $result = $this->routeIntent($parsed);

        // Fallback cascade: track consecutive failures
        if ($result['intent'] === 'general') {
            $_SESSION['concierge_fail_count'] = ($_SESSION['concierge_fail_count'] ?? 0) + 1;
        } else {
            $_SESSION['concierge_fail_count'] = 0;
        }

        // ── Step 2b: AI response generation (if Groq available) ──
        if (!empty($result['data']['restaurants']) && $this->groq->isAvailable()) {
            $aiResponse = $this->groq->generateResponse(
                $message,
                $result['data']['restaurants'],
                $result['intent'],
                $result['relaxed'] ?? false
            );
            if ($aiResponse !== null) {
                $result['response_text'] = $aiResponse;
            }
        }

        // ── Step 3: Log conversation ──
        $conversationId = $this->logConversation($sessionId, $message, $result['intent'], $result['response_text']);

        // ── Step 4: Log recommendations for feedback loop ──
        $recommendations = [];
        if (!empty($result['data']['restaurants'])) {
            $userId = $this->isAuthenticated() ? (int)$_SESSION['user']['id'] : null;
            foreach ($result['data']['restaurants'] as $i => $r) {
                $recId = $this->scoring->logRecommendation(
                    $sessionId,
                    $conversationId,
                    $userId,
                    (int)$r['id'],
                    $i + 1,
                    $result['intent'],
                    $message,
                    (float)($r['score'] ?? 0),
                    $r['explanation'] ?? ''
                );
                if ($recId) {
                    $recommendations[] = ['rec_id' => $recId, 'restaurant_id' => $r['id']];
                }
            }
        }

        echo json_encode([
            'success' => true,
            'session_id' => $sessionId,
            'intent' => $result['intent'],
            'response' => $result['response_text'],
            'data' => $result['data'],
            'suggestions' => $result['suggestions'],
            'recommendations' => $recommendations,
            'ai_powered' => !empty($parsed['ai_parsed']),
        ]);
    }

    /**
     * Track a click on a recommendation
     * POST /api/concierge/click
     * Input: { rec_id }
     */
    public function trackClick(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $recId = (int)($input['rec_id'] ?? 0);

        if ($recId > 0) {
            $this->scoring = new ScoringService($this->db);
            $this->scoring->trackClick($recId);

            // Store in session for conversion tracking (30-min window)
            $_SESSION['concierge_last_click'] = [
                'rec_id' => $recId,
                'restaurant_id' => (int)($input['restaurant_id'] ?? 0),
                'timestamp' => time(),
            ];
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Track dwell time on restaurant page after concierge click
     * POST /api/concierge/dwell
     * Input: { rec_id, seconds }
     */
    public function trackDwell(Request $request): void
    {
        header('Content-Type: application/json');

        // No CSRF on beforeunload (browser limitation) — low risk endpoint
        $input = json_decode(file_get_contents('php://input'), true);
        $recId = (int)($input['rec_id'] ?? 0);
        $seconds = (int)($input['seconds'] ?? 0);

        if ($recId > 0 && $seconds > 0) {
            $this->scoring = new ScoringService($this->db);
            $this->scoring->trackDwell($recId, $seconds);
        }

        echo json_encode(['success' => true]);
    }

    /**
     * Cron endpoint: recompute occasion scores + adaptive weights
     * GET /api/cron/recompute-scores
     */
    public function cronRecomputeScores(Request $request): void
    {
        header('Content-Type: application/json');

        // Simple token protection for cron endpoint
        $token = $request->param('token') ?? ($_GET['token'] ?? '');
        $expectedToken = getenv('CRON_TOKEN') ?: 'lebonresto-cron-2026';
        if ($token !== $expectedToken) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token invalide']);
            return;
        }

        $this->scoring = new ScoringService($this->db);
        $updated = $this->scoring->recomputeOccasionScores();
        $weightStats = $this->scoring->updateAdaptiveWeights();
        ScoringService::clearCache();

        echo json_encode([
            'success' => true,
            'restaurants_updated' => $updated,
            'weight_stats' => $weightStats,
        ]);
    }

    /**
     * Cron endpoint: analyze reviews and enrich data
     * GET /api/cron/analyze-reviews?token=xxx
     */
    public function cronAnalyzeReviews(Request $request): void
    {
        header('Content-Type: application/json');

        $token = $request->param('token') ?? ($_GET['token'] ?? '');
        $expectedToken = getenv('CRON_TOKEN') ?: 'lebonresto-cron-2026';
        if ($token !== $expectedToken) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token invalide']);
            return;
        }

        $limit = min(500, max(10, (int)($_GET['limit'] ?? 100)));
        $force = (int)($_GET['force'] ?? 0) === 1;

        $analyzer = new ReviewAnalyzerService($this->db);

        // Step 1: Batch analyze reviews (force=1 re-analyzes existing insights)
        $analyzed = $analyzer->analyzeBatch($limit, $force);

        // Step 2: Enrich reviews.trip_type from insights
        $enriched = $analyzer->enrichTripTypes();

        // Step 3: Recompute occasion scores with fresh data
        $this->scoring = new ScoringService($this->db);
        $updated = $this->scoring->recomputeOccasionScores();
        ScoringService::clearCache();

        // Stats
        $totalInsights = 0;
        $remaining = 0;
        try {
            $totalInsights = (int)$this->db->query("SELECT COUNT(*) FROM review_insights")->fetchColumn();
            $remaining = (int)$this->db->query("
                SELECT COUNT(*) FROM reviews r
                LEFT JOIN review_insights ri ON ri.review_id = r.id
                WHERE r.status = 'approved' AND r.message IS NOT NULL AND LENGTH(r.message) >= 15 AND ri.id IS NULL
            ")->fetchColumn();
        } catch (\Exception $e) {}

        echo json_encode([
            'success' => true,
            'analyzed' => $analyzed,
            'trip_types_enriched' => $enriched,
            'scores_updated' => $updated,
            'total_insights' => $totalInsights,
            'remaining_to_analyze' => $remaining,
        ]);
    }

    /**
     * Cron endpoint: fetch external data (Phase 2 infrastructure)
     * GET /api/cron/fetch-external-data?token=xxx&source=google_places&limit=50
     */
    public function cronFetchExternalData(Request $request): void
    {
        header('Content-Type: application/json');

        $token = $request->param('token') ?? ($_GET['token'] ?? '');
        $expectedToken = getenv('CRON_TOKEN') ?: 'lebonresto-cron-2026';
        if ($token !== $expectedToken) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token invalide']);
            return;
        }

        $source = trim($_GET['source'] ?? '');
        $limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));

        $extService = new \App\Services\ExternalDataService($this->db);

        if ($source === 'cleanup') {
            $deleted = $extService->cleanupExpired();
            echo json_encode(['success' => true, 'expired_deleted' => $deleted]);
            return;
        }

        if ($source === 'stats') {
            echo json_encode(['success' => true, 'stats' => $extService->getStats()]);
            return;
        }

        if (empty($source)) {
            echo json_encode(['success' => false, 'error' => 'Parametre source requis', 'valid_sources' => ['google_places', 'facebook', 'yassir', 'jumia', 'instagram', 'website', 'cleanup', 'stats']]);
            return;
        }

        $result = $extService->fetchBySource($source, $limit);
        echo json_encode(array_merge(['success' => true], $result));
    }

    /**
     * Cron endpoint: data enrichment pipeline
     * GET /api/cron/enrich-data?token=xxx&source=reviews&limit=200
     * Sources: reviews, descriptions, horaires, normalize, osm, websites, all
     */
    public function cronEnrichData(Request $request): void
    {
        header('Content-Type: application/json');

        $token = $request->param('token') ?? ($_GET['token'] ?? '');
        $expectedToken = getenv('CRON_TOKEN') ?: 'lebonresto-cron-2026';
        if ($token !== $expectedToken) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token invalide']);
            return;
        }

        $source = trim($_GET['source'] ?? '');
        $limit = min(500, max(1, (int)($_GET['limit'] ?? 200)));

        $enrichService = new \App\Services\DataEnrichmentService($this->db);

        if ($source === 'stats') {
            echo json_encode(['success' => true, 'stats' => $enrichService->getStats()]);
            return;
        }

        if (empty($source)) {
            echo json_encode(['success' => false, 'error' => 'Parametre source requis', 'valid_sources' => ['reviews', 'descriptions', 'horaires', 'normalize', 'osm', 'websites', 'all', 'stats']]);
            return;
        }

        $result = $enrichService->enrichBySource($source, $limit);
        echo json_encode(array_merge(['success' => true], $result));
    }

    // ═══════════════════════════════════════════════════════════════
    // INTENT PARSER v3 — AI-powered with NLP fallback
    // ═══════════════════════════════════════════════════════════════

    /**
     * Try Groq AI for intent extraction, fallback to NLP regex
     */
    private function parseIntentWithAI(string $message): array
    {
        // Try Groq AI first
        if ($this->groq->isAvailable()) {
            $cities = $this->getKnownCities();
            $aiParsed = $this->groq->extractIntent($message, $cities);

            if ($aiParsed !== null && !empty($aiParsed['intent'])) {
                // Build the same structure as parseIntent() returns
                $parsed = [
                    'original' => $message,
                    'normalized' => $this->normalizeMessage($message),
                    'intent' => $aiParsed['intent'],
                    'city' => $aiParsed['city'],
                    'cuisine' => $aiParsed['cuisine'],
                    'occasion' => $aiParsed['occasion'],
                    'budget' => $aiParsed['budget'],
                    'amenity' => $aiParsed['amenity'],
                    'amenity_column' => $aiParsed['amenity_column'],
                    'restaurant_name' => $aiParsed['restaurant_name'],
                    'context' => [],
                    'ai_parsed' => true,
                ];

                if ($parsed['budget']) {
                    $parsed['context']['budget'] = $parsed['budget'];
                }
                if ($parsed['occasion']) {
                    $parsed['context']['occasion'] = $parsed['occasion'];
                }

                return $parsed;
            }
        }

        // Fallback to NLP regex parser
        return $this->parseIntent($message);
    }

    /**
     * Parse a user message into a structured intent (NLP regex — fallback)
     * Extracts: primary_intent, city, cuisine, occasion, budget, amenity, restaurant_name
     */
    private function parseIntent(string $message): array
    {
        $normalized = $this->normalizeMessage($message);

        $parsed = [
            'original' => $message,
            'normalized' => $normalized,
            'intent' => 'general',
            'city' => null,
            'cuisine' => null,
            'occasion' => null,
            'budget' => null,
            'amenity' => null,
            'amenity_column' => null,
            'restaurant_name' => null,
            'context' => [],
        ];

        // Extract all entities simultaneously
        $parsed['city'] = $this->extractCity($message);
        $parsed['cuisine'] = $this->extractCuisine($normalized);
        $parsed['occasion'] = $this->extractOccasion($normalized);
        $parsed['budget'] = $this->extractBudget($normalized);
        [$parsed['amenity'], $parsed['amenity_column']] = $this->extractAmenity($normalized);

        // Set budget context
        if ($parsed['budget']) {
            $parsed['context']['budget'] = $parsed['budget'];
        }
        if ($parsed['occasion']) {
            $parsed['context']['occasion'] = $parsed['occasion'];
        }

        // Determine primary intent (priority-based)
        $parsed['intent'] = $this->determinePrimaryIntent($normalized, $parsed);

        // Extract restaurant name for specific intents
        if (in_array($parsed['intent'], ['hours', 'booking', 'order'])) {
            $parsed['restaurant_name'] = $this->extractRestaurantName($message);
        }

        return $parsed;
    }

    /**
     * Determine the primary intent based on keywords (priority order)
     */
    private function determinePrimaryIntent(string $normalized, array $parsed): string
    {
        // Priority 1: Booking (explicit reservation request)
        if (preg_match('/\b(reserver|reservation|booking|table pour)\b/iu', $normalized)) {
            return 'booking';
        }

        // Priority 2: Order (explicit order/delivery request)
        if (preg_match('/\b(commander|commande|livrer|emporter)\b/iu', $normalized)) {
            return 'order';
        }

        // Priority 3: Occasion-based search — BEFORE hours!
        // "restaurant ouvert ce soir pour un anniversaire" → occasion, not hours
        // "ou manger en famille" → occasion, not general
        if ($parsed['occasion']) {
            return 'occasion_' . $parsed['occasion'];
        }

        // Priority 4: Hours / open now (only if no occasion detected)
        if (preg_match('/\b(horaire|horaires|fermeture|ouverture)\b/iu', $normalized)) {
            return 'hours';
        }
        if (preg_match('/\b(ouvert|ouvre)\b.*\b(maintenant|la|ce\s+soir|aujourd|en\s+ce\s+moment)\b/iu', $normalized)) {
            return 'open_now';
        }
        // "est-ce que X est ouvert" without occasion → hours
        if (preg_match('/\b(ouvert|ferme|fermer|ouvre)\b/iu', $normalized)) {
            return 'hours';
        }

        // Priority 5: Budget search
        if ($parsed['budget']) {
            return 'price';
        }

        // Priority 6: Amenity search
        if ($parsed['amenity']) {
            return 'amenity_search';
        }

        // Priority 7: Recommendation keywords
        if (preg_match('/\b(meilleur|top|populaire|recommand|suggest|tendance|classement|ou manger|ou diner|ou dejeuner)\b/iu', $normalized)) {
            return 'recommendation';
        }

        // Priority 8: Generic positive search (less specific)
        if (preg_match('/\b(bon|bonne|bien manger)\b/iu', $normalized) && ($parsed['city'] || $parsed['cuisine'])) {
            return 'recommendation';
        }

        // Priority 9: Cuisine search
        if ($parsed['cuisine']) {
            return 'search_restaurant';
        }

        // Priority 10: Direct restaurant name (short message, no other intent)
        $trimmed = trim($parsed['original']);
        if (mb_strlen($trimmed) >= 2 && mb_strlen($trimmed) <= 40) {
            return 'direct_search';
        }

        return 'general';
    }

    /**
     * Extract city from message
     */
    private function extractCity(string $text): ?string
    {
        $cities = $this->getKnownCities();
        $lower = mb_strtolower($text);
        foreach ($cities as $city) {
            if (mb_strpos($lower, mb_strtolower($city)) !== false) {
                return $city;
            }
        }
        if (preg_match('/(?:\ba\b|\bà\b|\bdans\b|\bsur\b|\bde\b)\s+([a-zéèêàâîïôùûç]{3,})/iu', $text, $m)) {
            return mb_strtolower(trim($m[1]));
        }
        return null;
    }

    /**
     * Extract cuisine type from message
     */
    private function extractCuisine(string $normalized): ?string
    {
        // Multi-word cuisines first (priority)
        $multiWord = [
            'fast food' => 'fast food', 'fastfood' => 'fast food',
            'fruits de mer' => 'fruits de mer',
            'salon de the' => 'salon de the',
        ];
        foreach ($multiWord as $pattern => $cuisine) {
            if (mb_strpos($normalized, $pattern) !== false) {
                return $cuisine;
            }
        }

        // Single-word cuisines
        $words = preg_split('/\s+/', $normalized);
        foreach ($words as $word) {
            if (in_array($word, self::CUISINE_KEYWORDS)) {
                return $word;
            }
        }
        return null;
    }

    /**
     * Extract occasion from message
     */
    private function extractOccasion(string $normalized): ?string
    {
        foreach (self::OCCASION_MAP as $keyword => $occasion) {
            if (mb_strpos($normalized, $keyword) !== false) {
                return $occasion;
            }
        }
        return null;
    }

    /**
     * Extract budget preference from message
     */
    private function extractBudget(string $normalized): ?string
    {
        if (preg_match('/\b(pas\s+cher|budget|economique|abordable|bon\s+marche|moins\s+cher|petit\s+budget|prix\s+bas)\b/iu', $normalized)) {
            return 'low';
        }
        if (preg_match('/\b(luxe|haut\s+de\s+gamme|chic|standing|gastronomique|cher)\b/iu', $normalized)) {
            return 'high';
        }
        if (preg_match('/\b(prix\s+moyen|correct|raisonnable)\b/iu', $normalized)) {
            return 'medium';
        }
        return null;
    }

    /**
     * Extract amenity from message
     * @return array [keyword, db_column] or [null, null]
     */
    private function extractAmenity(string $normalized): array
    {
        foreach (self::AMENITY_MAP as $keyword => $dbColumn) {
            if (mb_strpos($normalized, $keyword) !== false) {
                return [$keyword, $dbColumn];
            }
        }
        return [null, null];
    }

    /**
     * Extract restaurant name from message
     */
    private function extractRestaurantName(string $text): ?string
    {
        if (preg_match('/(?:de|du|chez|pour|au)\s+(.+?)(?:\s*\?|$)/iu', $text, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/(?:horaire|horaires|ouvert|heure|ferme|ouvre|fermeture)s?\s+(.+?)(?:\s*\?|$)/iu', $text, $m)) {
            $name = trim($m[1]);
            $name = preg_replace('/\s+(?:a|à|dans|sur)\s+\w+$/iu', '', $name);
            return $name ?: null;
        }
        return null;
    }

    // ═══════════════════════════════════════════════════════════════
    // INTENT ROUTING — Dispatch to scored handlers
    // ═══════════════════════════════════════════════════════════════

    /**
     * Route parsed intent to appropriate handler
     */
    private function routeIntent(array $parsed): array
    {
        $intent = $parsed['intent'];

        // Occasion-based intents → scored discovery
        if (str_starts_with($intent, 'occasion_')) {
            return $this->handleScoredDiscovery($parsed);
        }

        return match ($intent) {
            'booking' => $this->handleBooking($parsed['restaurant_name']),
            'order' => $this->handleOrder($parsed['restaurant_name']),
            'hours' => $this->handleHours($parsed['restaurant_name']),
            'open_now' => $this->handleOpenNow($parsed['city']),
            'price' => $this->handleScoredDiscovery($parsed),
            'amenity_search' => $this->handleScoredAmenitySearch($parsed),
            'recommendation' => $this->handleScoredDiscovery($parsed),
            'search_restaurant' => $this->handleScoredDiscovery($parsed),
            'direct_search' => $this->handleDirectSearch(trim($parsed['original'])),
            default => $this->handleGeneral(),
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // v2 SCORED HANDLERS — With ScoringService + explanations
    // ═══════════════════════════════════════════════════════════════

    /**
     * Main scored discovery handler
     * Used for: recommendation, search_restaurant, price, occasion_*
     */
    private function handleScoredDiscovery(array $parsed): array
    {
        $intent = $parsed['intent'];
        $city = $parsed['city'];
        $cuisine = $parsed['cuisine'];
        $context = $parsed['context'];

        // Build SQL to fetch candidate pool (wider net = better scoring)
        $params = [];
        $conditions = ["r.status = 'validated'"];

        if ($city) {
            $conditions[] = "(LOWER(r.ville) LIKE :city OR LOWER(r.adresse) LIKE :city2 OR LOWER(r.nom) LIKE :city3)";
            $params[':city'] = '%' . $city . '%';
            $params[':city2'] = '%' . $city . '%';
            $params[':city3'] = '%' . $city . '%';
        }

        if ($cuisine) {
            $conditions[] = "(LOWER(r.type_cuisine) LIKE :cuisine OR LOWER(r.nom) LIKE :cuisine2)";
            $params[':cuisine'] = '%' . $cuisine . '%';
            $params[':cuisine2'] = '%' . $cuisine . '%';
        }

        $where = implode(' AND ', $conditions);

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                   r.note_moyenne, r.nb_avis, r.adresse, r.popularity_score,
                   r.gps_latitude, r.gps_longitude, r.orders_enabled,
                   r.score_familial, r.score_romantique, r.score_business,
                   r.score_rapide, r.score_festif, r.score_terrasse,
                   r.score_budget, r.score_gastronomique,
                   r.score_brunch, r.score_livraison, r.score_vue, r.score_healthy,
                   r.score_ouvert_tard, r.score_instagrammable, r.score_calme, r.score_nouveau,
                   r.score_parking, r.score_ramadan, r.score_groupe, r.score_wifi_travail,
                   r.score_enfants, r.score_traditionnel,
                   (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id
                    AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_reviews,
                   (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as photo
            FROM restaurants r
            WHERE {$where}
            ORDER BY r.popularity_score DESC
            LIMIT 20
        ");
        $stmt->execute($params);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── STORY-018: Relaxation progressive si 0 résultats ──
        $relaxed = false;
        $relaxedInfo = '';

        if (empty($candidates) && ($cuisine || $city)) {
            // Step 1: drop cuisine, keep city
            if ($cuisine && $city) {
                $relaxParams = [];
                $relaxConds = ["r.status = 'validated'"];
                $relaxConds[] = "(LOWER(r.ville) LIKE :city OR LOWER(r.adresse) LIKE :city2 OR LOWER(r.nom) LIKE :city3)";
                $relaxParams[':city'] = '%' . $city . '%';
                $relaxParams[':city2'] = '%' . $city . '%';
                $relaxParams[':city3'] = '%' . $city . '%';
                $relaxWhere = implode(' AND ', $relaxConds);

                $stmt2 = $this->db->prepare("
                    SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                           r.note_moyenne, r.nb_avis, r.adresse, r.popularity_score,
                           r.gps_latitude, r.gps_longitude, r.orders_enabled,
                           r.score_familial, r.score_romantique, r.score_business,
                           r.score_rapide, r.score_festif, r.score_terrasse,
                           r.score_budget, r.score_gastronomique,
                           (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id
                            AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_reviews,
                           (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as photo
                    FROM restaurants r
                    WHERE {$relaxWhere}
                    ORDER BY r.popularity_score DESC
                    LIMIT 20
                ");
                $stmt2->execute($relaxParams);
                $candidates = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($candidates)) {
                    $relaxed = true;
                    $relaxedInfo = "Pas de " . htmlspecialchars($cuisine) . " a " . htmlspecialchars(ucfirst($city)) . ", mais voici les meilleures adresses du coin :";
                }
            }

            // Step 2: drop city, keep cuisine
            if (empty($candidates) && $cuisine) {
                $relaxParams = [];
                $relaxConds = ["r.status = 'validated'"];
                $relaxConds[] = "(LOWER(r.type_cuisine) LIKE :cuisine OR LOWER(r.nom) LIKE :cuisine2)";
                $relaxParams[':cuisine'] = '%' . $cuisine . '%';
                $relaxParams[':cuisine2'] = '%' . $cuisine . '%';
                $relaxWhere = implode(' AND ', $relaxConds);

                $stmt3 = $this->db->prepare("
                    SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                           r.note_moyenne, r.nb_avis, r.adresse, r.popularity_score,
                           r.gps_latitude, r.gps_longitude, r.orders_enabled,
                           r.score_familial, r.score_romantique, r.score_business,
                           r.score_rapide, r.score_festif, r.score_terrasse,
                           r.score_budget, r.score_gastronomique,
                           (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id
                            AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_reviews,
                           (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as photo
                    FROM restaurants r
                    WHERE {$relaxWhere}
                    ORDER BY r.popularity_score DESC
                    LIMIT 20
                ");
                $stmt3->execute($relaxParams);
                $candidates = $stmt3->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($candidates)) {
                    $relaxed = true;
                    $relaxedInfo = "Pas de " . htmlspecialchars($cuisine) . ($city ? " a " . htmlspecialchars(ucfirst($city)) : "") . ", mais voici des restaurants " . htmlspecialchars($cuisine) . " ailleurs :";
                }
            }

            // Step 3: drop everything, show top popular
            if (empty($candidates)) {
                $stmt4 = $this->db->prepare("
                    SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                           r.note_moyenne, r.nb_avis, r.adresse, r.popularity_score,
                           r.gps_latitude, r.gps_longitude, r.orders_enabled,
                           r.score_familial, r.score_romantique, r.score_business,
                           r.score_rapide, r.score_festif, r.score_terrasse,
                           r.score_budget, r.score_gastronomique,
                           (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id
                            AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_reviews,
                           (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as photo
                    FROM restaurants r
                    WHERE r.status = 'validated'
                    ORDER BY r.popularity_score DESC
                    LIMIT 20
                ");
                $stmt4->execute();
                $candidates = $stmt4->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($candidates)) {
                    $relaxed = true;
                    $loc = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
                    $cuisineText = $cuisine ? " " . htmlspecialchars($cuisine) : "";
                    $relaxedInfo = "Aucun resultat pour{$cuisineText}{$loc}. Voici les restaurants les plus populaires :";
                }
            }
        }

        if (empty($candidates)) {
            return [
                'intent' => $intent,
                'response_text' => "Desole, je n'ai trouve aucun restaurant correspondant. Essayez avec d'autres criteres !",
                'data' => [],
                'suggestions' => [
                    'Meilleur restaurant',
                    'Restaurant populaire',
                    'Voir tous les restaurants',
                ],
            ];
        }

        // Score and rank using ScoringService
        $ranked = $this->scoring->rankRestaurants($candidates, $intent, $context, 3);

        // Build response
        $restaurantList = [];
        $responseLines = [];

        foreach ($ranked as $i => $item) {
            $r = $item['restaurant'];
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $price = $r['price_range'] ?: '';

            $photo = !empty($r['photo']) ? '/' . ltrim($r['photo'], '/') : '';
            $restaurantList[] = [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'slug' => $r['slug'],
                'city' => $r['ville'],
                'cuisine' => $r['type_cuisine'],
                'rating' => (float)($r['note_moyenne'] ?? 0),
                'price' => $price,
                'address' => $r['adresse'],
                'photo' => $photo,
                'url' => '/restaurant/' . $r['id'],
                'score' => $item['score'],
                'explanation' => $item['explanation'],
            ];

            // Build conversational line with explanation
            $line = ($i + 1) . ". **" . $r['nom'] . "** - " . $rating . "/5";
            if ($price) $line .= " (" . $price . ")";
            $line .= " - " . $r['ville'];
            $line .= "\n   → " . $item['explanation'];
            $responseLines[] = $line;
        }

        // Generate header text (override with relaxation info if applicable)
        $headerText = $relaxed ? $relaxedInfo : $this->generateResponseHeader($parsed, count($ranked));

        $responseText = $headerText . "\n\n" . implode("\n\n", $responseLines);

        // Smart suggestions based on context
        $suggestions = $this->generateSuggestions($ranked, $parsed);

        return [
            'intent' => $intent,
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => $suggestions,
            'relaxed' => $relaxed,
        ];
    }

    /**
     * Scored amenity search
     */
    private function handleScoredAmenitySearch(array $parsed): array
    {
        $keyword = $parsed['amenity'];
        $dbColumn = $parsed['amenity_column'];
        $city = $parsed['city'];
        $context = $parsed['context'];

        $params = [];
        $whereCity = '';

        if ($city) {
            $whereCity = "AND (LOWER(r.ville) LIKE :city OR LOWER(r.adresse) LIKE :city2 OR LOWER(r.nom) LIKE :city3)";
            $params[':city'] = '%' . $city . '%';
            $params[':city2'] = '%' . $city . '%';
            $params[':city3'] = '%' . $city . '%';
        }

        // delivery is on restaurants table, not restaurant_options
        if ($dbColumn === 'delivery') {
            $amenityJoin = '';
            $amenityWhere = 'AND r.delivery_enabled = 1';
        } else {
            $amenityJoin = 'INNER JOIN restaurant_options ro ON ro.restaurant_id = r.id';
            $amenityWhere = "AND ro.{$dbColumn} = 1";
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                   r.note_moyenne, r.nb_avis, r.adresse, r.popularity_score,
                   r.gps_latitude, r.gps_longitude, r.orders_enabled,
                   r.score_familial, r.score_romantique, r.score_business,
                   r.score_rapide, r.score_festif, r.score_terrasse,
                   r.score_budget, r.score_gastronomique,
                   r.score_brunch, r.score_livraison, r.score_vue, r.score_healthy,
                   r.score_ouvert_tard, r.score_instagrammable, r.score_calme, r.score_nouveau,
                   r.score_parking, r.score_ramadan, r.score_groupe, r.score_wifi_travail,
                   r.score_enfants, r.score_traditionnel,
                   (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id
                    AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_reviews,
                   (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as photo
            FROM restaurants r
            {$amenityJoin}
            WHERE r.status = 'validated'
              {$amenityWhere}
              {$whereCity}
            ORDER BY r.popularity_score DESC
            LIMIT 15
        ");
        $stmt->execute($params);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
        $relaxed = false;
        $relaxedInfo = '';

        // ── STORY-018: Relaxation progressive pour amenity search ──
        if (empty($candidates) && $city) {
            // Drop city, keep amenity
            $stmt2 = $this->db->prepare("
                SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                       r.note_moyenne, r.nb_avis, r.adresse, r.popularity_score,
                       r.gps_latitude, r.gps_longitude, r.orders_enabled,
                       r.score_familial, r.score_romantique, r.score_business,
                       r.score_rapide, r.score_festif, r.score_terrasse,
                       r.score_budget, r.score_gastronomique,
                       (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id
                        AND rv.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as recent_reviews,
                       (SELECT rp.path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as photo
                FROM restaurants r
                {$amenityJoin}
                WHERE r.status = 'validated' {$amenityWhere}
                ORDER BY r.popularity_score DESC
                LIMIT 15
            ");
            $stmt2->execute();
            $candidates = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($candidates)) {
                $relaxed = true;
                $relaxedInfo = "Pas de " . htmlspecialchars($keyword) . $locationText . ", mais voici des restaurants avec " . htmlspecialchars($keyword) . " ailleurs :";
            }
        }

        if (empty($candidates)) {
            return [
                'intent' => 'amenity_search',
                'response_text' => "Je n'ai pas trouve de restaurants avec " . htmlspecialchars($keyword) . $locationText . ".",
                'data' => [],
                'suggestions' => ['Meilleur restaurant', 'Restaurant ouvert maintenant'],
            ];
        }

        // Score and rank
        $ranked = $this->scoring->rankRestaurants($candidates, 'amenity_search', $context, 3);

        $restaurantList = [];
        $responseLines = [];
        foreach ($ranked as $i => $item) {
            $r = $item['restaurant'];
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);

            $aPhoto = !empty($r['photo']) ? '/' . ltrim($r['photo'], '/') : '';
            $restaurantList[] = [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'slug' => $r['slug'],
                'city' => $r['ville'],
                'cuisine' => $r['type_cuisine'],
                'rating' => (float)($r['note_moyenne'] ?? 0),
                'price' => $r['price_range'] ?: '',
                'photo' => $aPhoto,
                'url' => '/restaurant/' . $r['id'],
                'score' => $item['score'],
                'explanation' => $item['explanation'],
            ];

            $line = ($i + 1) . ". **" . $r['nom'] . "** (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5 - " . $r['ville'];
            $line .= "\n   → " . $item['explanation'];
            $responseLines[] = $line;
        }

        $headerText = $relaxed ? $relaxedInfo : "Restaurants avec " . htmlspecialchars($keyword) . $locationText . " :";
        $responseText = $headerText . "\n\n" . implode("\n\n", $responseLines);

        return [
            'intent' => 'amenity_search',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => ['Restaurant avec terrasse', 'Restaurant avec wifi', 'Restaurant pas cher'],
            'relaxed' => $relaxed,
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // SPECIFIC INTENT HANDLERS (hours, booking, order — unchanged logic)
    // ═══════════════════════════════════════════════════════════════

    private function handleHours(?string $restaurantName): array
    {
        if (empty($restaurantName)) {
            return [
                'intent' => 'hours',
                'response_text' => "De quel restaurant souhaitez-vous connaitre les horaires ? Exemple : \"Horaires de Chez Karim\"",
                'data' => [],
                'suggestions' => ['Meilleur restaurant', 'Restaurant ouvert maintenant', 'Reserver une table'],
            ];
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug
            FROM restaurants r
            WHERE r.status = 'validated'
              AND LOWER(r.nom) LIKE :name
            ORDER BY r.popularity_score DESC
            LIMIT 1
        ");
        $stmt->execute([':name' => '%' . mb_strtolower($restaurantName) . '%']);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            return [
                'intent' => 'hours',
                'response_text' => "Je n'ai pas trouve \"" . htmlspecialchars($restaurantName) . "\". Verifiez l'orthographe.",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Restaurant populaire'],
            ];
        }

        $stmtH = $this->db->prepare("
            SELECT jour_semaine, ferme, service_continu,
                   ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir
            FROM restaurant_horaires
            WHERE restaurant_id = :rid
            ORDER BY jour_semaine
        ");
        $stmtH->execute([':rid' => $restaurant['id']]);
        $horairesRows = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        $dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $horaires = [];
        foreach ($horairesRows as $h) {
            $horaires[(int)$h['jour_semaine']] = $h;
        }

        $responseText = "**Horaires de " . $restaurant['nom'] . "** :\n";
        if (!empty($horaires)) {
            for ($d = 0; $d <= 6; $d++) {
                $dayName = $dayNames[$d];
                if (!isset($horaires[$d]) || (int)($horaires[$d]['ferme'] ?? 0)) {
                    $responseText .= $dayName . " : Ferme\n";
                } else {
                    $h = $horaires[$d];
                    if ((int)($h['service_continu'] ?? 0) && $h['ouverture_matin'] && $h['fermeture_soir']) {
                        $responseText .= $dayName . " : " . substr($h['ouverture_matin'], 0, 5) . " - " . substr($h['fermeture_soir'], 0, 5) . " (continu)\n";
                    } else {
                        $parts = [];
                        if ($h['ouverture_matin'] && $h['fermeture_matin']) {
                            $parts[] = substr($h['ouverture_matin'], 0, 5) . "-" . substr($h['fermeture_matin'], 0, 5);
                        }
                        if ($h['ouverture_soir'] && $h['fermeture_soir']) {
                            $parts[] = substr($h['ouverture_soir'], 0, 5) . "-" . substr($h['fermeture_soir'], 0, 5);
                        }
                        $responseText .= $dayName . " : " . (implode(' / ', $parts) ?: 'Non renseigne') . "\n";
                    }
                }
            }
        } else {
            $responseText .= "Les horaires ne sont pas encore renseignes.";
        }

        return [
            'intent' => 'hours',
            'response_text' => $responseText,
            'data' => [
                'restaurant' => [
                    'id' => (int)$restaurant['id'],
                    'name' => $restaurant['nom'],
                    'url' => '/restaurant/' . $restaurant['id'],
                ],
                'hours' => $horaires,
            ],
            'suggestions' => [
                'Reserver chez ' . $restaurant['nom'],
                'Commander chez ' . $restaurant['nom'],
                'Voir les avis',
            ],
        ];
    }

    private function handleBooking(?string $restaurantName): array
    {
        if (empty($restaurantName)) {
            return [
                'intent' => 'booking',
                'response_text' => "Chez quel restaurant souhaitez-vous reserver ? Exemple : \"Reserver chez La Palmeraie\"",
                'data' => [],
                'suggestions' => ['Meilleur restaurant', 'Restaurant italien', 'Restaurant ouvert'],
            ];
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.reservations_enabled
            FROM restaurants r
            WHERE r.status = 'validated'
              AND LOWER(r.nom) LIKE :name
            ORDER BY r.popularity_score DESC
            LIMIT 1
        ");
        $stmt->execute([':name' => '%' . mb_strtolower($restaurantName) . '%']);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            return [
                'intent' => 'booking',
                'response_text' => "Je n'ai pas trouve \"" . htmlspecialchars($restaurantName) . "\".",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Meilleur restaurant'],
            ];
        }

        if ((int)($restaurant['reservations_enabled'] ?? 0)) {
            return [
                'intent' => 'booking',
                'response_text' => "Vous pouvez reserver chez **" . $restaurant['nom'] . "** directement sur sa page.",
                'data' => [
                    'restaurant' => [
                        'id' => (int)$restaurant['id'],
                        'name' => $restaurant['nom'],
                        'url' => '/restaurant/' . $restaurant['id'],
                        'reservations_enabled' => true,
                    ],
                    'action_url' => '/restaurant/' . $restaurant['id'] . '#reservation',
                ],
                'suggestions' => ['Horaires de ' . $restaurant['nom'], 'Commander chez ' . $restaurant['nom']],
            ];
        }

        return [
            'intent' => 'booking',
            'response_text' => $restaurant['nom'] . " n'accepte pas encore les reservations en ligne. Contactez-le directement via sa fiche.",
            'data' => [
                'restaurant' => [
                    'id' => (int)$restaurant['id'],
                    'name' => $restaurant['nom'],
                    'url' => '/restaurant/' . $restaurant['id'],
                    'reservations_enabled' => false,
                ],
            ],
            'suggestions' => ['Voir la fiche du restaurant', 'Restaurant avec reservation'],
        ];
    }

    private function handleOrder(?string $restaurantName): array
    {
        if (empty($restaurantName)) {
            return [
                'intent' => 'order',
                'response_text' => "Chez quel restaurant souhaitez-vous commander ? Exemple : \"Commander chez Pizza House\"",
                'data' => [],
                'suggestions' => ['Restaurant avec livraison', 'Meilleur restaurant', 'Pizza'],
            ];
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.orders_enabled, r.delivery_enabled
            FROM restaurants r
            WHERE r.status = 'validated'
              AND LOWER(r.nom) LIKE :name
            ORDER BY r.popularity_score DESC
            LIMIT 1
        ");
        $stmt->execute([':name' => '%' . mb_strtolower($restaurantName) . '%']);
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$restaurant) {
            return [
                'intent' => 'order',
                'response_text' => "Je n'ai pas trouve \"" . htmlspecialchars($restaurantName) . "\".",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Restaurant avec livraison'],
            ];
        }

        if ((int)($restaurant['orders_enabled'] ?? 0)) {
            $slug = $restaurant['slug'] ?: $restaurant['id'];
            $deliveryText = (int)($restaurant['delivery_enabled'] ?? 0) ? " (livraison disponible)" : " (retrait sur place)";
            return [
                'intent' => 'order',
                'response_text' => "Vous pouvez commander chez **" . $restaurant['nom'] . "**" . $deliveryText . ".",
                'data' => [
                    'restaurant' => [
                        'id' => (int)$restaurant['id'],
                        'name' => $restaurant['nom'],
                        'url' => '/restaurant/' . $restaurant['id'],
                        'orders_enabled' => true,
                        'delivery_enabled' => (bool)(int)($restaurant['delivery_enabled'] ?? 0),
                    ],
                    'action_url' => '/commander/' . $slug,
                ],
                'suggestions' => ['Voir le menu', 'Horaires de ' . $restaurant['nom']],
            ];
        }

        return [
            'intent' => 'order',
            'response_text' => $restaurant['nom'] . " n'accepte pas encore les commandes en ligne.",
            'data' => [
                'restaurant' => [
                    'id' => (int)$restaurant['id'],
                    'name' => $restaurant['nom'],
                    'url' => '/restaurant/' . $restaurant['id'],
                    'orders_enabled' => false,
                ],
            ],
            'suggestions' => ['Restaurant avec commande en ligne', 'Voir la fiche'],
        ];
    }

    private function handleOpenNow(?string $city): array
    {
        $dayIndex = (int)(new \DateTime('now', new \DateTimeZone('Africa/Algiers')))->format('N') - 1;
        $currentTime = (new \DateTime('now', new \DateTimeZone('Africa/Algiers')))->format('H:i:s');
        $params = [
            ':day' => $dayIndex,
            ':t1' => $currentTime, ':t2' => $currentTime,
            ':t3' => $currentTime, ':t4' => $currentTime,
            ':t5' => $currentTime, ':t6' => $currentTime,
        ];
        $whereCity = '';

        if ($city) {
            $whereCity = "AND (LOWER(r.ville) LIKE :city OR LOWER(r.adresse) LIKE :city2 OR LOWER(r.nom) LIKE :city3)";
            $params[':city'] = '%' . $city . '%';
            $params[':city2'] = '%' . $city . '%';
            $params[':city3'] = '%' . $city . '%';
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne,
                   r.nb_avis, r.adresse, r.popularity_score, r.price_range,
                   r.gps_latitude, r.gps_longitude, r.orders_enabled,
                   r.score_familial, r.score_romantique, r.score_business,
                   r.score_rapide, r.score_festif, r.score_terrasse,
                   r.score_budget, r.score_gastronomique,
                   r.score_brunch, r.score_livraison, r.score_vue, r.score_healthy,
                   r.score_ouvert_tard, r.score_instagrammable, r.score_calme, r.score_nouveau,
                   r.score_parking, r.score_ramadan, r.score_groupe, r.score_wifi_travail,
                   r.score_enfants, r.score_traditionnel,
                   0 as recent_reviews
            FROM restaurants r
            INNER JOIN restaurant_horaires h ON h.restaurant_id = r.id
            WHERE r.status = 'validated'
              AND h.jour_semaine = :day
              AND h.ferme = 0
              AND (
                (h.service_continu = 1 AND h.ouverture_matin <= :t1 AND h.fermeture_soir >= :t2)
                OR (h.ouverture_matin <= :t3 AND h.fermeture_matin >= :t4)
                OR (h.ouverture_soir <= :t5 AND h.fermeture_soir >= :t6)
              )
              {$whereCity}
            ORDER BY r.note_moyenne DESC
            LIMIT 15
        ");
        $stmt->execute($params);
        $candidates = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";

        if (empty($candidates)) {
            return [
                'intent' => 'open_now',
                'response_text' => "Je n'ai pas trouve de restaurants ouverts en ce moment" . $locationText . ".",
                'data' => [],
                'suggestions' => ['Meilleur restaurant', 'Restaurant pas cher'],
            ];
        }

        // Score the open ones
        $ranked = $this->scoring->rankRestaurants($candidates, 'open_now', [], 5);

        $restaurantList = [];
        $responseLines = [];
        foreach ($ranked as $i => $item) {
            $r = $item['restaurant'];
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $restaurantList[] = [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'url' => '/restaurant/' . $r['id'],
                'score' => $item['score'],
                'explanation' => $item['explanation'],
            ];
            $line = ($i + 1) . ". **" . $r['nom'] . "** (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5";
            $line .= "\n   → " . $item['explanation'];
            $responseLines[] = $line;
        }

        $responseText = "Restaurants ouverts maintenant" . $locationText . " :\n\n" . implode("\n\n", $responseLines);

        return [
            'intent' => 'open_now',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => ['Restaurant pas cher', 'Restaurant familial', 'Reserver une table'],
        ];
    }

    private function handleDirectSearch(string $name): ?array
    {
        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne,
                   r.adresse, r.price_range, r.nb_avis, r.popularity_score,
                   r.gps_latitude, r.gps_longitude, r.orders_enabled,
                   r.score_familial, r.score_romantique, r.score_business,
                   r.score_rapide, r.score_festif, r.score_terrasse,
                   r.score_budget, r.score_gastronomique,
                   r.score_brunch, r.score_livraison, r.score_vue, r.score_healthy,
                   r.score_ouvert_tard, r.score_instagrammable, r.score_calme, r.score_nouveau,
                   r.score_parking, r.score_ramadan, r.score_groupe, r.score_wifi_travail,
                   r.score_enfants, r.score_traditionnel,
                   0 as recent_reviews
            FROM restaurants r
            WHERE r.status = 'validated'
              AND LOWER(r.nom) LIKE :name
            ORDER BY r.popularity_score DESC
            LIMIT 5
        ");
        $stmt->execute([':name' => '%' . mb_strtolower($name) . '%']);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($restaurants)) {
            return $this->handleGeneral();
        }

        $ranked = $this->scoring->rankRestaurants($restaurants, 'direct_search', [], 3);

        $restaurantList = [];
        $responseLines = [];
        foreach ($ranked as $i => $item) {
            $r = $item['restaurant'];
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $restaurantList[] = [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'url' => '/restaurant/' . $r['id'],
                'score' => $item['score'],
                'explanation' => $item['explanation'],
            ];
            $line = ($i + 1) . ". **" . $r['nom'] . "** (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5 - " . $r['ville'];
            $line .= "\n   → " . $item['explanation'];
            $responseLines[] = $line;
        }

        $responseText = "Voici ce que j'ai trouve :\n\n" . implode("\n\n", $responseLines);

        return [
            'intent' => 'direct_search',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => [
                'Horaires de ' . $restaurants[0]['nom'],
                'Reserver chez ' . $restaurants[0]['nom'],
                'Restaurant similaire',
            ],
        ];
    }

    private function handleGeneral(): array
    {
        $failCount = $_SESSION['concierge_fail_count'] ?? 0;

        // 2nd consecutive failure: show trends instead of generic help
        if ($failCount >= 2) {
            return $this->handleTrendsFallback();
        }

        // 1st failure: standard help text
        return [
            'intent' => 'general',
            'response_text' => "Je peux vous aider a trouver le restaurant ideal ! Essayez :\n\n" .
                "- \"Restaurant italien a Alger\"\n" .
                "- \"Restaurant familial pas cher\"\n" .
                "- \"Diner romantique a Oran\"\n" .
                "- \"Restaurant rapide avec terrasse\"\n" .
                "- \"Horaires de Chez Karim\"\n" .
                "- \"Reserver une table\"",
            'data' => [],
            'suggestions' => [
                'Meilleur restaurant',
                'Restaurant familial',
                'Diner romantique',
                'Restaurant rapide',
            ],
        ];
    }

    /**
     * Trends fallback: show top 3 most popular restaurants when user is stuck
     */
    private function handleTrendsFallback(): array
    {
        $stmt = $this->db->query("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne,
                   r.nb_avis, r.adresse, r.price_range, r.popularity_score
            FROM restaurants r
            WHERE r.status = 'validated'
            ORDER BY r.popularity_score DESC
            LIMIT 3
        ");
        $top = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $restaurantList = [];
        $responseLines = [];
        foreach ($top as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $restaurantList[] = [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'url' => '/restaurant/' . $r['id'],
                'score' => 0,
                'explanation' => 'Parmi les plus populaires sur LeBonResto',
            ];
            $line = ($i + 1) . ". **" . $r['nom'] . "** (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5 - " . $r['ville'];
            $line .= "\n   → Parmi les plus populaires sur LeBonResto";
            $responseLines[] = $line;
        }

        // Reset fail count after showing trends
        $_SESSION['concierge_fail_count'] = 0;

        return [
            'intent' => 'general',
            'response_text' => "Voici les tendances du moment :\n\n" . implode("\n\n", $responseLines) .
                "\n\nN'hesitez pas a me decrire ce que vous cherchez !",
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => [
                'Restaurant familial',
                'Diner romantique',
                'Restaurant pas cher',
                'Restaurant a Alger',
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // RESPONSE GENERATION — Headers + Smart suggestions
    // ═══════════════════════════════════════════════════════════════

    /**
     * Generate contextual response header
     */
    private function generateResponseHeader(array $parsed, int $resultCount = 3): string
    {
        // Zero results
        if ($resultCount === 0) {
            return "Je n'ai pas trouve de restaurant correspondant a votre recherche.";
        }

        $intent = $parsed['intent'];
        $city = $parsed['city'];
        $cuisine = $parsed['cuisine'];
        $occasion = $parsed['occasion'];

        $loc = $city ? " a " . ucfirst($city) : "";

        // Template variety via random pick
        $pick = fn(array $templates) => $templates[array_rand($templates)];

        if (str_starts_with($intent, 'occasion_')) {
            $occasionLabels = [
                'familial' => 'en famille',
                'romantique' => 'pour une occasion romantique',
                'business' => 'pour un repas d\'affaires',
                'rapide' => 'pour manger rapidement',
                'festif' => 'pour une celebration',
                'terrasse' => 'avec terrasse',
                'budget' => 'a petit prix',
                'gastronomique' => 'pour une experience gastronomique',
                'brunch' => 'pour un brunch',
                'livraison' => 'avec livraison',
                'vue' => 'avec une belle vue',
                'healthy' => 'cuisine saine et legere',
                'ouvert_tard' => 'ouverts tard le soir',
                'instagrammable' => 'au cadre photogenique',
                'calme' => 'au cadre calme et paisible',
                'nouveau' => 'nouveaux et tendance',
                'parking' => 'avec parking facile',
                'ramadan' => 'pour le Ramadan',
                'groupe' => 'pour les grands groupes',
                'wifi_travail' => 'pour travailler au calme',
                'enfants' => 'avec espace enfants',
                'traditionnel' => 'cuisine algerienne authentique',
            ];
            $label = $occasionLabels[$occasion] ?? $occasion;
            $cuisineStr = $cuisine ? htmlspecialchars($cuisine) . " " : "";
            return $pick([
                "Voici les meilleurs restaurants {$cuisineStr}{$label}{$loc} :",
                "J'ai selectionne {$resultCount} restaurants {$cuisineStr}{$label}{$loc} :",
                "Excellents choix {$label}{$loc} ! Voici ma selection {$cuisineStr}:",
            ]);
        }

        if ($intent === 'price') {
            return $pick([
                "Voici les restaurants les plus abordables{$loc} :",
                "Bon plan ! {$resultCount} adresses petit budget{$loc} :",
                "Des restaurants a petits prix{$loc} :",
            ]);
        }

        if ($intent === 'recommendation') {
            return $pick([
                "Voici les restaurants les plus recommandes{$loc} :",
                "Les meilleures adresses{$loc} selon vos criteres :",
                "Mes coups de coeur{$loc} :",
            ]);
        }

        if ($intent === 'search_restaurant' && $cuisine) {
            return $pick([
                "Voici les meilleurs restaurants " . htmlspecialchars($cuisine) . "{$loc} :",
                "Les meilleures adresses " . htmlspecialchars($cuisine) . "{$loc} :",
                "{$resultCount} restaurants " . htmlspecialchars($cuisine) . "{$loc} pour vous :",
            ]);
        }

        if ($intent === 'amenity_search') {
            return $pick([
                "Voici les restaurants correspondant a vos criteres{$loc} :",
                "J'ai trouve {$resultCount} restaurants{$loc} :",
            ]);
        }

        return $pick([
            "Voici mes recommandations{$loc} :",
            "Voici {$resultCount} restaurants selectionnes{$loc} :",
            "J'ai trouve {$resultCount} bonnes adresses{$loc} :",
        ]);
    }

    /**
     * Generate smart suggestions based on results
     */
    private function generateSuggestions(array $ranked, array $parsed): array
    {
        $suggestions = [];

        // Suggest related occasion if none specified
        if (!$parsed['occasion'] && !empty($ranked)) {
            $suggestions[] = 'Restaurant familial';
            $suggestions[] = 'Diner romantique';
        }

        // Suggest first result's name for specific actions
        if (!empty($ranked)) {
            $firstName = $ranked[0]['restaurant']['nom'] ?? '';
            if ($firstName) {
                $suggestions[] = 'Reserver chez ' . $firstName;
            }
        }

        // Add generic useful ones
        if (!$parsed['city']) {
            $suggestions[] = 'Restaurant a Alger';
        }

        // Cap at 4
        return array_slice($suggestions, 0, 4);
    }

    // ═══════════════════════════════════════════════════════════════
    // UTILITIES
    // ═══════════════════════════════════════════════════════════════

    private function normalizeMessage(string $message): string
    {
        $normalized = mb_strtolower($message);
        $normalized = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            $normalized
        );
        $normalized = str_replace(
            ["\xC3\xA9", "\xC3\xA8", "\xC3\xAA", "\xC3\xAB",
             "\xC3\xA0", "\xC3\xA2",
             "\xC3\xAE", "\xC3\xAF",
             "\xC3\xB4", "\xC3\xB6",
             "\xC3\xB9", "\xC3\xBB", "\xC3\xBC",
             "\xC3\xA7"],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            $normalized
        );
        return trim($normalized);
    }

    private function getKnownCities(): array
    {
        static $cities = null;
        if ($cities === null) {
            try {
                $stmt = $this->db->query("
                    SELECT DISTINCT ville FROM restaurants
                    WHERE status = 'validated' AND ville IS NOT NULL AND ville != ''
                    ORDER BY ville
                ");
                $cities = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } catch (\Exception $e) {
                $cities = ['alger', 'oran', 'constantine', 'annaba', 'setif', 'blida', 'tizi ouzou', 'batna', 'bejaia', 'tlemcen'];
            }
        }
        return $cities;
    }

    private function logConversation(string $sessionId, string $userMessage, string $intent, string $botResponse): ?int
    {
        try {
            $userId = $this->isAuthenticated() ? (int)$_SESSION['user']['id'] : null;

            $stmt = $this->db->prepare("
                INSERT INTO concierge_conversations
                    (session_id, user_id, message, intent, response, created_at)
                VALUES
                    (:session_id, :user_id, :message, :intent, :response, NOW())
            ");
            $stmt->execute([
                ':session_id' => $sessionId,
                ':user_id' => $userId,
                ':message' => mb_substr($userMessage, 0, 500),
                ':intent' => $intent,
                ':response' => mb_substr($botResponse, 0, 2000),
            ]);
            return (int)$this->db->lastInsertId();
        } catch (\Exception $e) {
            return null;
        }
    }
}

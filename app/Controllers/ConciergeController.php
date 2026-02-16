<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use PDO;

/**
 * F32 - AI Concierge Controller
 * Chatbot intelligent avec detection d'intentions par regex NLP
 * Permet aux utilisateurs de rechercher des restaurants, horaires, reservations, etc.
 * via une interface conversationnelle
 */
class ConciergeController extends Controller
{
    /**
     * Page chatbot
     * GET /concierge
     */
    public function chat(): void
    {
        $this->render('concierge.index', [
            'title' => 'Concierge IA - LeBonResto',
        ]);
    }

    /**
     * Traitement d'un message utilisateur
     * POST /api/concierge/ask
     *
     * Detection d'intention par patterns regex :
     * - search_restaurant : "restaurant (cuisine) a (ville)"
     * - recommendation   : "meilleur|top|bon"
     * - hours            : "ouvert|horaire|heure"
     * - booking          : "reserver|reservation|table"
     * - order            : "commander|commande|livraison"
     * - price            : "prix|cher|budget"
     * - general          : fallback
     */
    public function ask(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            return;
        }

        // Rate limit: 30 questions per minute (per IP or user)
        $rateLimitKey = $this->isAuthenticated()
            ? 'concierge_user_' . (int)$_SESSION['user']['id']
            : 'concierge_ip_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

        if (!RateLimiter::attempt($rateLimitKey, 30, 60)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de messages. Attendez un moment.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? '');
        $sessionId = trim($input['session_id'] ?? '');

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

        // Generate session ID if not provided
        if (empty($sessionId)) {
            $sessionId = bin2hex(random_bytes(16));
        }

        // Normalize message for intent detection
        $normalized = $this->normalizeMessage($message);

        // Detect intent and generate response
        $result = $this->detectIntentAndRespond($normalized, $message);

        // Log conversation
        $this->logConversation($sessionId, $message, $result['intent'], $result['response_text']);

        echo json_encode([
            'success' => true,
            'session_id' => $sessionId,
            'intent' => $result['intent'],
            'response' => $result['response_text'],
            'data' => $result['data'],
            'suggestions' => $result['suggestions'],
        ]);
    }

    /**
     * Normaliser le message pour la detection d'intent
     * Supprime accents, met en minuscule, simplifie
     */
    private function normalizeMessage(string $message): string
    {
        $normalized = mb_strtolower($message);

        // Remove common accents for easier regex matching
        $accents = [
            'a' => ['a', "\xC3\xA0", "\xC3\xA2", "\xC3\xA4"],
            'e' => ['e', "\xC3\xA9", "\xC3\xA8", "\xC3\xAA", "\xC3\xAB"],
            'i' => ['i', "\xC3\xAE", "\xC3\xAF"],
            'o' => ['o', "\xC3\xB4", "\xC3\xB6"],
            'u' => ['u', "\xC3\xB9", "\xC3\xBB", "\xC3\xBC"],
            'c' => ['c', "\xC3\xA7"],
        ];

        // Simple transliteration
        $search  = ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'];
        $normalized = str_replace(
            ["\xC3\xA9", "\xC3\xA8", "\xC3\xAA", "\xC3\xAB",
             "\xC3\xA0", "\xC3\xA2",
             "\xC3\xAE", "\xC3\xAF",
             "\xC3\xB4", "\xC3\xB6",
             "\xC3\xB9", "\xC3\xBB", "\xC3\xBC",
             "\xC3\xA7"],
            $search,
            $normalized
        );

        // Also handle UTF-8 composed accents
        $normalized = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            $normalized
        );

        return trim($normalized);
    }

    /**
     * Detecter l'intention et generer la reponse
     */
    private function detectIntentAndRespond(string $normalized, string $original): array
    {
        // Helper: extract city from message
        $extractCity = function($text) {
            $cities = $this->getKnownCities();
            $lower = mb_strtolower($text);
            foreach ($cities as $city) {
                if (mb_strpos($lower, mb_strtolower($city)) !== false) {
                    return $city;
                }
            }
            // Fallback: "a/à/dans/sur + word"
            if (preg_match('/(?:\ba\b|\bà\b|\bdans\b|\bsur\b|\bde\b)\s+([a-zéèêàâîïôùûç]{3,})/iu', $text, $m)) {
                return mb_strtolower(trim($m[1]));
            }
            return null;
        };

        // Helper: extract restaurant name (after "de/du/chez" or remaining words after intent keyword)
        $extractName = function($text) {
            // "horaire kfc", "horaire de kfc", "horaires du Milk Bar"
            if (preg_match('/(?:de|du|chez|pour|au)\s+(.+?)(?:\s*\?|$)/iu', $text, $m)) {
                return trim($m[1]);
            }
            // Direct: "horaire kfc" → everything after the keyword
            if (preg_match('/(?:horaire|horaires|ouvert|heure|ferme|ouvre|fermeture)s?\s+(.+?)(?:\s*\?|$)/iu', $text, $m)) {
                $name = trim($m[1]);
                // Remove trailing city context like "a oran"
                $name = preg_replace('/\s+(?:a|à|dans|sur)\s+\w+$/iu', '', $name);
                return $name ?: null;
            }
            return null;
        };

        // ── Priority 1: Booking ──
        if (preg_match('/\b(reserver|reservation|r[ée]server|r[ée]servation|booking)\b/iu', $normalized)) {
            $restaurantName = null;
            if (preg_match('/(?:a|au|chez|pour|dans)\s+(.+?)(?:\s*\?|$)/iu', $original, $nameMatch)) {
                $restaurantName = trim($nameMatch[1]);
            }
            return $this->handleBooking($restaurantName);
        }

        // ── Priority 2: Order ──
        if (preg_match('/\b(commander|commande|livraison|delivery|livrer|emporter)\b/iu', $normalized)) {
            $restaurantName = null;
            if (preg_match('/(?:a|au|chez|de|du)\s+(.+?)(?:\s*\?|$)/iu', $original, $nameMatch)) {
                $restaurantName = trim($nameMatch[1]);
            }
            return $this->handleOrder($restaurantName);
        }

        // ── Priority 3: Price (BEFORE cuisine to catch "restaurant pas cher a Oran") ──
        if (preg_match('/\b(pas\s+cher|prix|budget|economique|abordable|bon\s+marche|moins\s+cher|petit\s+budget)\b/iu', $normalized)
            || preg_match('/\bcher\b/iu', $normalized)) {
            return $this->handlePrice($extractCity($original));
        }

        // ── Priority 4: Hours / Open now ──
        if (preg_match('/\b(ouvert|horaire|horaires|heure|ferme|fermer|ouvre|fermeture|ouverture)\b/iu', $normalized)) {
            // "ouvert maintenant" → show open restaurants
            if (preg_match('/\b(ouvert|ouvre)\b.*\b(maintenant|la|ce\s+soir|aujourd|en\s+ce\s+moment)\b/iu', $normalized)) {
                return $this->handleOpenNow($extractCity($original));
            }
            $restaurantName = $extractName($original);
            return $this->handleHours($restaurantName);
        }

        // ── Priority 5: Amenity search ("terrasse", "wifi", "parking", "livraison") ──
        $amenityMap = [
            'terrasse' => 'terrace', 'wifi' => 'wifi', 'parking' => 'parking',
            'climatisation' => 'air_conditioning', 'clim' => 'air_conditioning',
            'pmr' => 'handicap_access', 'handicap' => 'handicap_access', 'accessible' => 'handicap_access',
            'jeux' => 'game_zone', 'enfant' => 'game_zone', 'famille' => 'game_zone',
            'salon prive' => 'private_room', 'prive' => 'private_room',
            'animaux' => 'pets_allowed', 'chien' => 'pets_allowed',
            'chaise bebe' => 'baby_chair', 'bebe' => 'baby_chair',
            'voiturier' => 'valet_service', 'valet' => 'valet_service',
        ];
        foreach ($amenityMap as $keyword => $dbColumn) {
            if (mb_strpos($normalized, $keyword) !== false) {
                return $this->handleAmenitySearch($keyword, $dbColumn, $extractCity($original));
            }
        }

        // ── Priority 6: Recommendation ──
        if (preg_match('/\b(meilleur|top|bon|populaire|recommand|suggest|tendance|classement)\b/iu', $normalized)) {
            return $this->handleRecommendation($extractCity($original));
        }

        // ── Priority 7: Cuisine + city search ──
        // "restaurant italien a alger", "pizza a oran", "sushi"
        $words = preg_split('/\s+/', $normalized);
        foreach ($words as $word) {
            if ($this->looksLikeCuisine($word)) {
                return $this->handleSearchRestaurant($word, $extractCity($original));
            }
        }

        // ── Priority 8: Direct restaurant name search (last resort before fallback) ──
        // If message is 2-30 chars and doesn't match anything else, try as restaurant name
        $trimmed = trim($original);
        if (mb_strlen($trimmed) >= 2 && mb_strlen($trimmed) <= 40) {
            $result = $this->handleDirectSearch($trimmed);
            if ($result) return $result;
        }

        // Default: general fallback
        return $this->handleGeneral();
    }

    /**
     * Verifier si un mot ressemble a un type de cuisine
     */
    private function looksLikeCuisine(string $word): bool
    {
        $cuisineKeywords = [
            'italien', 'italienne', 'chinois', 'chinoise', 'japonais', 'japonaise',
            'francais', 'francaise', 'algerien', 'algerienne', 'marocain', 'marocaine',
            'tunisien', 'tunisienne', 'libanais', 'libanaise', 'turc', 'turque',
            'indien', 'indienne', 'mexicain', 'mexicaine', 'americain', 'americaine',
            'thai', 'thailandais', 'vietnamien', 'coreen', 'pizza', 'burger',
            'sushi', 'kebab', 'tacos', 'grillades', 'poisson', 'fruits de mer',
            'vegetarien', 'vegan', 'bio', 'traditionnel', 'gastronomique',
            'fast food', 'fastfood', 'oriental', 'occidental', 'asiatique', 'africain',
            'restaurant', 'resto',
        ];

        return in_array($word, $cuisineKeywords);
    }

    /**
     * Intent: search_restaurant - Chercher par cuisine et ville
     */
    private function handleSearchRestaurant(string $cuisine, ?string $city): array
    {
        $params = [':cuisine' => '%' . $cuisine . '%', ':cuisine2' => '%' . $cuisine . '%'];
        $whereCity = '';
        if ($city) {
            $whereCity = "AND LOWER(r.ville) LIKE :city";
            $params[':city'] = '%' . $city . '%';
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                   r.note_moyenne, r.adresse
            FROM restaurants r
            WHERE r.status = 'validated'
              AND (LOWER(r.type_cuisine) LIKE :cuisine OR LOWER(r.nom) LIKE :cuisine2)
              {$whereCity}
            ORDER BY r.note_moyenne DESC, r.popularity_score DESC
            LIMIT 3
        ");
        $stmt->execute($params);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($restaurants)) {
            $loc = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
            return [
                'intent' => 'search_restaurant',
                'response_text' => "Desole, je n'ai pas trouve de restaurant " . htmlspecialchars($cuisine) . $loc . ". Essayez une recherche plus large !",
                'data' => [],
                'suggestions' => [
                    'Meilleur restaurant' . ($city ? ' a ' . ucfirst($city) : ''),
                    'Restaurant populaire',
                    'Voir tous les restaurants',
                ],
            ];
        }

        $restaurantList = array_map(function ($r) {
            return [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'slug' => $r['slug'],
                'city' => $r['ville'],
                'cuisine' => $r['type_cuisine'],
                'rating' => (float)($r['note_moyenne'] ?? 0),
                'price' => $r['price_range'],
                'address' => $r['adresse'],
                'url' => '/restaurant/' . $r['id'],
            ];
        }, $restaurants);

        $loc = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
        $responseText = "Voici les meilleurs restaurants " . htmlspecialchars($cuisine) . $loc . " :\n";
        foreach ($restaurants as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $responseText .= ($i + 1) . ". " . $r['nom'] . " - " . $rating . "/5 - " . $r['adresse'] . "\n";
        }

        return [
            'intent' => 'search_restaurant',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => [
                'Horaires de ' . ($restaurants[0]['nom'] ?? ''),
                'Reserver une table',
                'Voir les avis',
            ],
        ];
    }

    /**
     * Intent: recommendation - Meilleurs restaurants
     */
    private function handleRecommendation(?string $city): array
    {
        if ($city) {
            $stmt = $this->db->prepare("
                SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                       r.note_moyenne, r.adresse, r.popularity_score
                FROM restaurants r
                WHERE r.status = 'validated'
                  AND LOWER(r.ville) LIKE :city
                ORDER BY r.popularity_score DESC, r.note_moyenne DESC
                LIMIT 3
            ");
            $stmt->execute([':city' => '%' . $city . '%']);
        } else {
            $stmt = $this->db->prepare("
                SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                       r.note_moyenne, r.adresse, r.popularity_score
                FROM restaurants r
                WHERE r.status = 'validated'
                ORDER BY r.popularity_score DESC, r.note_moyenne DESC
                LIMIT 3
            ");
            $stmt->execute();
        }

        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($restaurants)) {
            $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
            return [
                'intent' => 'recommendation',
                'response_text' => "Desole, je n'ai pas trouve de restaurants" . $locationText . " pour le moment.",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Restaurant italien', 'Restaurant algerien'],
            ];
        }

        $restaurantList = array_map(function ($r) {
            return [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'slug' => $r['slug'],
                'city' => $r['ville'],
                'cuisine' => $r['type_cuisine'],
                'rating' => (float)($r['note_moyenne'] ?? 0),
                'popularity' => (int)($r['popularity_score'] ?? 0),
                'url' => '/restaurant/' . $r['id'],
            ];
        }, $restaurants);

        $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
        $responseText = "Voici les restaurants les plus populaires" . $locationText . " :\n";
        foreach ($restaurants as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $responseText .= ($i + 1) . ". " . $r['nom'] . " (" . $r['type_cuisine'] . ") - " . $rating . "/5 - " . $r['ville'] . "\n";
        }

        return [
            'intent' => 'recommendation',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => [
                'Restaurant pas cher',
                'Voir le classement complet',
                'Commander en ligne',
            ],
        ];
    }

    /**
     * Intent: hours - Horaires d'un restaurant
     */
    private function handleHours(?string $restaurantName): array
    {
        if (empty($restaurantName)) {
            return [
                'intent' => 'hours',
                'response_text' => "De quel restaurant souhaitez-vous connaitre les horaires ? Indiquez son nom. Exemple : \"Horaires de Chez Karim\"",
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
                'response_text' => "Je n'ai pas trouve de restaurant nomme \"" . htmlspecialchars($restaurantName) . "\". Verifiez l'orthographe ou essayez un autre nom.",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Restaurant populaire'],
            ];
        }

        // Fetch hours from restaurant_horaires table
        // Columns: jour_semaine (0=Lundi..6=Dimanche), ferme, service_continu,
        //          ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir
        $stmtH = $this->db->prepare("
            SELECT jour_semaine, ferme, service_continu,
                   ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir
            FROM restaurant_horaires
            WHERE restaurant_id = :rid
            ORDER BY jour_semaine
        ");
        $stmtH->execute([':rid' => $restaurant['id']]);
        $horairesRows = $stmtH->fetchAll(PDO::FETCH_ASSOC);

        // 0=Lundi..6=Dimanche
        $dayNames = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $horaires = [];
        foreach ($horairesRows as $h) {
            $horaires[(int)$h['jour_semaine']] = $h;
        }

        $responseText = "Horaires de " . $restaurant['nom'] . " :\n";
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
            $responseText .= "Les horaires ne sont pas encore renseignes pour ce restaurant.";
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

    /**
     * Intent: booking - Lien vers la reservation
     */
    private function handleBooking(?string $restaurantName): array
    {
        if (empty($restaurantName)) {
            return [
                'intent' => 'booking',
                'response_text' => "Chez quel restaurant souhaitez-vous reserver ? Indiquez le nom du restaurant. Exemple : \"Reserver chez La Palmeraie\"",
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
                'response_text' => "Je n'ai pas trouve \"" . htmlspecialchars($restaurantName) . "\". Verifiez le nom du restaurant.",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Meilleur restaurant'],
            ];
        }

        $reservationsEnabled = (int)($restaurant['reservations_enabled'] ?? 0);

        if ($reservationsEnabled) {
            return [
                'intent' => 'booking',
                'response_text' => "Vous pouvez reserver chez " . $restaurant['nom'] . " directement sur sa page. Cliquez sur le lien ci-dessous pour acceder au formulaire de reservation.",
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
            'response_text' => $restaurant['nom'] . " n'accepte pas encore les reservations en ligne. Vous pouvez le contacter directement via sa fiche.",
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

    /**
     * Intent: order - Lien vers la commande
     */
    private function handleOrder(?string $restaurantName): array
    {
        if (empty($restaurantName)) {
            return [
                'intent' => 'order',
                'response_text' => "Chez quel restaurant souhaitez-vous commander ? Indiquez le nom. Exemple : \"Commander chez Pizza House\"",
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
                'response_text' => "Je n'ai pas trouve \"" . htmlspecialchars($restaurantName) . "\". Verifiez le nom du restaurant.",
                'data' => [],
                'suggestions' => ['Voir tous les restaurants', 'Restaurant avec livraison'],
            ];
        }

        $ordersEnabled = (int)($restaurant['orders_enabled'] ?? 0);

        if ($ordersEnabled) {
            $slug = $restaurant['slug'] ?: $restaurant['id'];
            $deliveryText = (int)($restaurant['delivery_enabled'] ?? 0)
                ? " (livraison disponible)"
                : " (retrait sur place uniquement)";

            return [
                'intent' => 'order',
                'response_text' => "Vous pouvez commander chez " . $restaurant['nom'] . $deliveryText . ". Accedez au menu en ligne via le lien ci-dessous.",
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
            'response_text' => $restaurant['nom'] . " n'accepte pas encore les commandes en ligne. Vous pouvez le contacter directement.",
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

    /**
     * Intent: price - Filtrer par budget
     */
    private function handlePrice(?string $city): array
    {
        $params = [];
        $whereCity = '';

        if ($city) {
            $whereCity = "AND LOWER(r.ville) LIKE :city";
            $params[':city'] = '%' . $city . '%';
        }

        // price_range is VARCHAR: €, €€, €€€ — sort by length (cheapest first)
        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.price_range,
                   r.note_moyenne, r.adresse
            FROM restaurants r
            WHERE r.status = 'validated'
              AND r.price_range IS NOT NULL
              AND r.price_range != ''
              {$whereCity}
            ORDER BY LENGTH(r.price_range) ASC, r.note_moyenne DESC
            LIMIT 3
        ");
        $stmt->execute($params);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($restaurants)) {
            $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
            return [
                'intent' => 'price',
                'response_text' => "Je n'ai pas trouve de restaurants avec des prix renseignes" . $locationText . ".",
                'data' => [],
                'suggestions' => ['Meilleur restaurant', 'Voir tous les restaurants'],
            ];
        }

        $restaurantList = array_map(function ($r) {
            return [
                'id' => (int)$r['id'],
                'name' => $r['nom'],
                'slug' => $r['slug'],
                'city' => $r['ville'],
                'cuisine' => $r['type_cuisine'],
                'rating' => (float)($r['note_moyenne'] ?? 0),
                'price_range' => $r['price_range'],
                'url' => '/restaurant/' . $r['id'],
            ];
        }, $restaurants);

        $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";
        $responseText = "Voici les restaurants les plus abordables" . $locationText . " :\n";
        foreach ($restaurants as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $responseText .= ($i + 1) . ". " . $r['nom'] . " (" . ($r['price_range'] ?: '?') . ") - " . $rating . "/5 - " . $r['ville'] . "\n";
        }

        return [
            'intent' => 'price',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => ['Meilleur rapport qualite-prix', 'Restaurant gastronomique', 'Fast food'],
        ];
    }

    /**
     * Intent: open_now - Restaurants ouverts maintenant
     */
    private function handleOpenNow(?string $city): array
    {
        // jour_semaine: 0=Lundi..6=Dimanche. PHP N: 1=Monday..7=Sunday → subtract 1
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
            $whereCity = "AND LOWER(r.ville) LIKE :city";
            $params[':city'] = '%' . $city . '%';
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne, r.adresse
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
            LIMIT 5
        ");
        $stmt->execute($params);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";

        if (empty($restaurants)) {
            return [
                'intent' => 'open_now',
                'response_text' => "Je n'ai pas trouve de restaurants ouverts en ce moment" . $locationText . ".",
                'data' => [],
                'suggestions' => ['Meilleur restaurant' . ($city ? ' a ' . ucfirst($city) : ''), 'Restaurant pas cher'],
            ];
        }

        $responseText = "Restaurants ouverts maintenant" . $locationText . " :\n";
        foreach ($restaurants as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $responseText .= ($i + 1) . ". " . $r['nom'] . " (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5\n";
        }

        $restaurantList = array_map(function ($r) {
            return ['id' => (int)$r['id'], 'name' => $r['nom'], 'url' => '/restaurant/' . $r['id']];
        }, $restaurants);

        return [
            'intent' => 'open_now',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => ['Restaurant pas cher', 'Meilleur restaurant', 'Reserver une table'],
        ];
    }

    /**
     * Intent: amenity_search - Recherche par equipement (terrasse, wifi, parking...)
     */
    private function handleAmenitySearch(string $keyword, string $dbColumn, ?string $city): array
    {
        $params = [];
        $whereCity = '';

        if ($city) {
            $whereCity = "AND LOWER(r.ville) LIKE :city";
            $params[':city'] = '%' . $city . '%';
        }

        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne, r.adresse
            FROM restaurants r
            INNER JOIN restaurant_options ro ON ro.restaurant_id = r.id
            WHERE r.status = 'validated'
              AND ro.{$dbColumn} = 1
              {$whereCity}
            ORDER BY r.note_moyenne DESC
            LIMIT 5
        ");
        $stmt->execute($params);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $locationText = $city ? " a " . htmlspecialchars(ucfirst($city)) : "";

        if (empty($restaurants)) {
            return [
                'intent' => 'amenity_search',
                'response_text' => "Je n'ai pas trouve de restaurants avec " . htmlspecialchars($keyword) . $locationText . ".",
                'data' => [],
                'suggestions' => ['Meilleur restaurant', 'Restaurant ouvert maintenant'],
            ];
        }

        $responseText = "Restaurants avec " . htmlspecialchars($keyword) . $locationText . " :\n";
        foreach ($restaurants as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $responseText .= ($i + 1) . ". " . $r['nom'] . " (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5 - " . $r['ville'] . "\n";
        }

        $restaurantList = array_map(function ($r) {
            return ['id' => (int)$r['id'], 'name' => $r['nom'], 'url' => '/restaurant/' . $r['id']];
        }, $restaurants);

        return [
            'intent' => 'amenity_search',
            'response_text' => $responseText,
            'data' => ['restaurants' => $restaurantList],
            'suggestions' => ['Restaurant avec terrasse', 'Restaurant avec wifi', 'Restaurant pas cher'],
        ];
    }

    /**
     * Intent: direct_search - Chercher un restaurant par nom
     */
    private function handleDirectSearch(string $name): ?array
    {
        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.type_cuisine, r.note_moyenne,
                   r.adresse, r.price_range
            FROM restaurants r
            WHERE r.status = 'validated'
              AND LOWER(r.nom) LIKE :name
            ORDER BY r.popularity_score DESC
            LIMIT 3
        ");
        $stmt->execute([':name' => '%' . mb_strtolower($name) . '%']);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($restaurants)) {
            return null; // Let fallback handle it
        }

        $responseText = "Voici ce que j'ai trouve :\n";
        foreach ($restaurants as $i => $r) {
            $rating = number_format((float)($r['note_moyenne'] ?? 0), 1);
            $responseText .= ($i + 1) . ". " . $r['nom'] . " (" . ($r['type_cuisine'] ?: '?') . ") - " . $rating . "/5 - " . $r['ville'] . "\n";
        }

        $restaurantList = array_map(function ($r) {
            return ['id' => (int)$r['id'], 'name' => $r['nom'], 'url' => '/restaurant/' . $r['id']];
        }, $restaurants);

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

    /**
     * Get list of known Algerian cities from DB (cached in static)
     */
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

    /**
     * Intent: general - Fallback quand aucun intent detecte
     */
    private function handleGeneral(): array
    {
        return [
            'intent' => 'general',
            'response_text' => "Je ne suis pas sur de comprendre. Essayez :\n" .
                "- \"meilleur restaurant italien a Alger\"\n" .
                "- \"restaurant pas cher a Oran\"\n" .
                "- \"horaires de Chez Karim\"\n" .
                "- \"reserver une table\"\n" .
                "- \"commander chez Pizza House\"",
            'data' => [],
            'suggestions' => [
                'Meilleur restaurant',
                'Restaurant pas cher',
                'Restaurant ouvert maintenant',
                'Voir le classement',
            ],
        ];
    }

    /**
     * Enregistrer la conversation dans concierge_conversations
     */
    private function logConversation(string $sessionId, string $userMessage, string $intent, string $botResponse): void
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
        } catch (\Exception $e) {
            // Silently fail - logging should not break the chatbot
        }
    }
}

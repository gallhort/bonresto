<?php

namespace App\Services;

/**
 * GroqService — Client API Groq (Llama 3.1 8B)
 *
 * Free tier: 30 req/min, 14,400 req/day, 6K tokens/min
 * Used by the Concierge for:
 *   1. Intent extraction (parse user message into structured intent)
 *   2. Natural response generation (humanize restaurant results)
 *
 * Fallback: returns null on rate limit (429) or error → caller uses NLP regex
 */
class GroqService
{
    private const API_URL = 'https://api.groq.com/openai/v1/chat/completions';
    private const MODEL = 'llama-3.1-8b-instant';
    private const TIMEOUT = 8; // seconds

    private ?string $apiKey;

    /** Track if last call was rate-limited */
    private static bool $rateLimited = false;

    public function __construct()
    {
        $this->apiKey = getenv('GROQ_API_KEY') ?: null;
    }

    /**
     * Check if the service is available (has API key and not rate-limited)
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey) && !self::$rateLimited;
    }

    /**
     * Extract intent from a user message
     *
     * Returns structured array or null on failure/rate-limit
     * @return array|null ['intent', 'city', 'cuisine', 'occasion', 'budget', 'amenity', 'restaurant_name']
     */
    public function extractIntent(string $message, array $knownCities = []): ?array
    {
        if (!$this->isAvailable()) {
            return null;
        }

        $citiesHint = !empty($knownCities)
            ? "Villes connues dans la base: " . implode(', ', array_slice($knownCities, 0, 30))
            : "";

        $systemPrompt = <<<PROMPT
Tu es un parseur d'intent pour un concierge de restaurants en Algerie. Analyse le message et extrais les informations au format JSON strict.

INTENTS (choisis le plus pertinent):
- "occasion_familial" : famille, sortie familiale
- "occasion_romantique" : couple, anniversaire, saint valentin, mariage, demande en mariage, fiancailles, diner aux chandelles, tete a tete
- "occasion_business" : affaires, reunion, collegues, seminaire, afterwork, team building, dejeuner d'equipe
- "occasion_rapide" : manger vite, sur le pouce, presse, snack, express
- "occasion_festif" : fete, soiree, amis, apero, bapteme, communion, evg, evjf, enterrement de vie, pot de depart, celebration
- "occasion_terrasse" : terrasse, dehors, plein air, jardin, exterieur
- "occasion_budget" : pas cher, economique, bon plan, promo, petit budget
- "occasion_gastronomique" : luxe, haut de gamme, chic, gastronomique, etoile, fine dining, degustation
- "occasion_brunch" : brunch, petit dejeuner, breakfast, ftour
- "occasion_livraison" : livraison, delivery, emporter, a domicile
- "occasion_vue" : vue mer, bord de mer, front de mer, rooftop, vue panoramique, panorama
- "occasion_healthy" : healthy, sain, vegetarien, vegan, bio, dietetique, leger, salade
- "occasion_ouvert_tard" : ouvert tard, nocturne, nuit, apres minuit
- "occasion_instagrammable" : instagrammable, photogenique, belle deco, esthetique
- "occasion_calme" : calme, paisible, tranquille, cosy, zen
- "occasion_nouveau" : nouveau, nouvelle ouverture, tendance, vient d'ouvrir
- "occasion_parking" : parking, stationner, se garer
- "occasion_ramadan" : ramadan, iftar, shour, sohour, menu ramadan
- "occasion_groupe" : grand groupe, grande table, salle privee, salon prive, banquet, privatiser
- "occasion_wifi_travail" : coworking, cafe pour travailler, wifi, bosser
- "occasion_enfants" : enfants, bebe, kids, kid friendly, espace jeux, aire de jeu, menu enfant
- "occasion_traditionnel" : traditionnel, authentique, typique, fait maison, plat algerien, couscous, chorba
- "search_restaurant" : recherche par type de cuisine (italien, kabyle, sushi, couscous...)
- "recommendation" : meilleur, top, populaire, tendance, ou manger, ou diner
- "booking" : reserver une table
- "order" : commander
- "hours" : horaires UNIQUEMENT (pas de recherche de restaurant)
- "open_now" : ouvert maintenant
- "amenity_search" : equipement specifique (wifi, parking, clim, pmr, salle privee)
- "direct_search" : nom de restaurant specifique
- "general" : aucun critere identifiable

IMPORTANT: Si le message contient une occasion ET "ouvert/horaire", prefere l'occasion.
Exemple: "restaurant ouvert ce soir pour un anniversaire" → occasion_romantique (pas hours)

Amenities DB: wifi, parking, air_conditioning, handicap_access, game_zone, private_room, pets_allowed, baby_chair, valet_service, terrace, delivery

Occasions: familial, romantique, business, rapide, festif, terrasse, budget, gastronomique, brunch, livraison, vue, healthy, ouvert_tard, instagrammable, calme, nouveau, parking, ramadan, groupe, wifi_travail, enfants, traditionnel

Budget: "low" (pas cher/economique), "medium" (correct/raisonnable), "high" (luxe/cher/chic)

{$citiesHint}

Reponds UNIQUEMENT avec un JSON valide:
{
  "intent": "...",
  "city": null ou "nom_ville",
  "cuisine": null ou "type_cuisine",
  "occasion": null ou "nom_occasion",
  "budget": null ou "low"/"medium"/"high",
  "amenity": null ou "nom_amenity_keyword",
  "amenity_column": null ou "colonne_db",
  "restaurant_name": null ou "nom_restaurant"
}
PROMPT;

        $response = $this->chat($systemPrompt, $message, 0.1, 200);

        if ($response === null) {
            return null;
        }

        // Parse JSON from response
        $json = $this->extractJson($response);
        if ($json === null) {
            return null;
        }

        // Validate expected keys
        $required = ['intent', 'city', 'cuisine', 'occasion', 'budget', 'amenity', 'amenity_column', 'restaurant_name'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $json)) {
                $json[$key] = null;
            }
        }

        // Validate intent is in allowed list
        $allowedIntents = [
            'occasion_familial', 'occasion_romantique', 'occasion_business',
            'occasion_rapide', 'occasion_festif', 'occasion_terrasse',
            'occasion_budget', 'occasion_gastronomique',
            'occasion_brunch', 'occasion_livraison', 'occasion_vue', 'occasion_healthy',
            'occasion_ouvert_tard', 'occasion_instagrammable', 'occasion_calme', 'occasion_nouveau',
            'occasion_parking', 'occasion_ramadan', 'occasion_groupe', 'occasion_wifi_travail',
            'occasion_enfants', 'occasion_traditionnel',
            'search_restaurant', 'recommendation', 'booking', 'order',
            'hours', 'open_now', 'amenity_search', 'direct_search', 'general',
        ];
        if (!in_array($json['intent'], $allowedIntents)) {
            $json['intent'] = 'general';
        }

        return $json;
    }

    /**
     * Generate a natural conversational response from restaurant results
     *
     * @param string $userMessage Original user message
     * @param array  $restaurants Top restaurants with scores and explanations
     * @param string $intent      Detected intent
     * @param bool   $relaxed     Whether results were relaxed (no exact match)
     * @return string|null Natural response text, or null on failure
     */
    public function generateResponse(string $userMessage, array $restaurants, string $intent, bool $relaxed = false): ?string
    {
        if (!$this->isAvailable()) {
            return null;
        }

        if (empty($restaurants)) {
            return null;
        }

        // Build restaurant context
        $restoContext = [];
        foreach ($restaurants as $i => $r) {
            $restoContext[] = [
                'rang' => $i + 1,
                'nom' => $r['name'] ?? $r['nom'] ?? '',
                'ville' => $r['city'] ?? $r['ville'] ?? '',
                'cuisine' => $r['cuisine'] ?? $r['type_cuisine'] ?? '',
                'note' => $r['rating'] ?? $r['note_moyenne'] ?? 0,
                'prix' => $r['price'] ?? $r['price_range'] ?? '',
                'explication' => $r['explanation'] ?? '',
            ];
        }

        $restoJson = json_encode($restoContext, JSON_UNESCAPED_UNICODE);
        $relaxedNote = $relaxed ? "\nNote: les resultats sont un elargissement car aucun resultat exact n'a ete trouve." : "";

        $systemPrompt = <<<PROMPT
Tu es le concierge IA de LeBonResto, un guide de restaurants en Algerie. Tu es chaleureux, professionnel et concis.

Genere une reponse conversationnelle naturelle en francais pour presenter les restaurants trouves.

Regles:
- Commence par une phrase d'introduction adaptee au contexte (max 1-2 phrases)
- Presente chaque restaurant avec son nom en gras (**nom**), sa note, sa ville
- Ajoute une courte raison personnalisee pour chaque (1 ligne max)
- Si les resultats sont relaxes (elargis), mentionne-le subtilement
- Utilise des emoji avec parcimonie (max 1-2 dans toute la reponse)
- Sois concis: max 4-5 lignes par restaurant
- Termine par une phrase engageante courte
- N'invente PAS d'informations (prix, adresse, etc.) qui ne sont pas dans les donnees
- Formate en markdown leger (gras pour les noms uniquement)
- Prix en DZD et € uniquement si fournis dans les donnees
- Ne dis pas "je suis une IA" ou "je suis un bot"
PROMPT;

        $userPrompt = "Message utilisateur: \"{$userMessage}\"\nIntent: {$intent}\nRestaurants trouves:\n{$restoJson}{$relaxedNote}";

        return $this->chat($systemPrompt, $userPrompt, 0.7, 500);
    }

    /**
     * Core chat completion call to Groq API
     *
     * @return string|null Response text, or null on error/rate-limit
     */
    private function chat(string $systemPrompt, string $userMessage, float $temperature = 0.3, int $maxTokens = 300): ?string
    {
        $payload = json_encode([
            'model' => self::MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Network error
        if ($response === false || !empty($curlError)) {
            error_log("[GroqService] cURL error: {$curlError}");
            return null;
        }

        // Rate limited — set flag so subsequent calls in this request skip API
        if ($httpCode === 429) {
            self::$rateLimited = true;
            error_log("[GroqService] Rate limited (429)");
            return null;
        }

        // Other HTTP errors
        if ($httpCode !== 200) {
            error_log("[GroqService] HTTP {$httpCode}: " . mb_substr($response, 0, 500));
            return null;
        }

        $data = json_decode($response, true);
        if (!$data || empty($data['choices'][0]['message']['content'])) {
            error_log("[GroqService] Invalid response structure");
            return null;
        }

        return trim($data['choices'][0]['message']['content']);
    }

    /**
     * Extract JSON from a string that may contain markdown code fences or extra text
     */
    private function extractJson(string $text): ?array
    {
        // Try direct parse first
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Try extracting from code fences
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $text, $m)) {
            $decoded = json_decode($m[1], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        // Try finding first { ... }
        if (preg_match('/\{[^{}]*\}/s', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        error_log("[GroqService] Failed to extract JSON from: " . mb_substr($text, 0, 300));
        return null;
    }

    /**
     * Reset rate limit flag (call at start of new request cycle if desired)
     */
    public static function resetRateLimit(): void
    {
        self::$rateLimited = false;
    }
}

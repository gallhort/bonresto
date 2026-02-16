<?php

namespace App\Services;

use PDO;

/**
 * Service de catégorisation de photos par IA (Google Cloud Vision API)
 * Limite : 1000 appels API / mois (free tier)
 */
class PhotoAIService
{
    private PDO $db;
    private string $apiKey;
    private int $monthlyLimit = 1000;

    // Mapping des labels Vision API → catégories internes
    private array $categoryMapping = [
        'food'       => 'plat',
        'dish'       => 'plat',
        'cuisine'    => 'plat',
        'meal'       => 'plat',
        'dessert'    => 'dessert',
        'cake'       => 'dessert',
        'pastry'     => 'dessert',
        'ice cream'  => 'dessert',
        'drink'      => 'boisson',
        'beverage'   => 'boisson',
        'coffee'     => 'boisson',
        'cocktail'   => 'boisson',
        'juice'      => 'boisson',
        'wine'       => 'boisson',
        'interior'   => 'interieur',
        'restaurant' => 'interieur',
        'room'       => 'interieur',
        'table'      => 'interieur',
        'dining'     => 'interieur',
        'building'   => 'exterieur',
        'facade'     => 'exterieur',
        'exterior'   => 'exterieur',
        'storefront' => 'exterieur',
        'sign'       => 'exterieur',
        'menu'       => 'menu',
        'text'       => 'menu',
        'document'   => 'menu',
        'people'     => 'ambiance',
        'person'     => 'ambiance',
        'crowd'      => 'ambiance',
        'event'      => 'ambiance',
    ];

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->apiKey = $_ENV['GOOGLE_VISION_API_KEY'] ?? '';
    }

    /**
     * Vérifier si on peut encore appeler l'API ce mois-ci
     */
    public function canMakeApiCall(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        $monthYear = date('Y-m');
        $stmt = $this->db->prepare("SELECT api_calls FROM ai_usage_log WHERE month_year = :month");
        $stmt->execute([':month' => $monthYear]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return !$row || (int)$row['api_calls'] < $this->monthlyLimit;
    }

    /**
     * Obtenir le nombre d'appels restants ce mois
     */
    public function getRemainingCalls(): int
    {
        $monthYear = date('Y-m');
        $stmt = $this->db->prepare("SELECT api_calls FROM ai_usage_log WHERE month_year = :month");
        $stmt->execute([':month' => $monthYear]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $used = $row ? (int)$row['api_calls'] : 0;
        return max(0, $this->monthlyLimit - $used);
    }

    /**
     * Incrémenter le compteur d'appels API
     */
    private function incrementUsage(): void
    {
        $monthYear = date('Y-m');
        $stmt = $this->db->prepare("
            INSERT INTO ai_usage_log (month_year, api_calls, last_call_at)
            VALUES (:month, 1, NOW())
            ON DUPLICATE KEY UPDATE api_calls = api_calls + 1, last_call_at = NOW()
        ");
        $stmt->execute([':month' => $monthYear]);
    }

    /**
     * Catégoriser une photo via Google Cloud Vision API
     * @return array{category: string, labels: array, confidence: float}|null
     */
    public function categorizePhoto(string $imagePath): ?array
    {
        if (!$this->canMakeApiCall()) {
            return null; // Limite atteinte
        }

        $fullPath = ROOT_PATH . '/public/' . $imagePath;
        if (!file_exists($fullPath)) {
            Logger::error("PhotoAI: fichier introuvable: $fullPath");
            return null;
        }

        $imageData = base64_encode(file_get_contents($fullPath));

        $requestBody = json_encode([
            'requests' => [[
                'image' => ['content' => $imageData],
                'features' => [
                    ['type' => 'LABEL_DETECTION', 'maxResults' => 10],
                ],
            ]],
        ]);

        $url = 'https://vision.googleapis.com/v1/images:annotate?key=' . $this->apiKey;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->incrementUsage();

        if ($httpCode !== 200 || !$response) {
            Logger::error("PhotoAI: API error HTTP $httpCode");
            return null;
        }

        $data = json_decode($response, true);
        $labels = $data['responses'][0]['labelAnnotations'] ?? [];

        if (empty($labels)) {
            return ['category' => 'other', 'labels' => [], 'confidence' => 0];
        }

        // Mapper les labels vers nos catégories
        $labelTexts = [];
        $bestCategory = 'other';
        $bestScore = 0;

        foreach ($labels as $label) {
            $desc = strtolower($label['description'] ?? '');
            $score = $label['score'] ?? 0;
            $labelTexts[] = $desc;

            foreach ($this->categoryMapping as $keyword => $category) {
                if (stripos($desc, $keyword) !== false && $score > $bestScore) {
                    $bestCategory = $category;
                    $bestScore = $score;
                }
            }
        }

        return [
            'category' => $bestCategory,
            'labels' => $labelTexts,
            'confidence' => $bestScore,
        ];
    }

    /**
     * Catégoriser une photo et mettre à jour la DB (review_photos)
     */
    public function categorizeReviewPhoto(int $photoId, string $photoPath): ?string
    {
        $result = $this->categorizePhoto($photoPath);

        if ($result) {
            $stmt = $this->db->prepare("
                UPDATE review_photos
                SET category = :cat, ai_labels = :labels, ai_processed = 1
                WHERE id = :id
            ");
            $stmt->execute([
                ':cat' => $result['category'],
                ':labels' => json_encode($result['labels']),
                ':id' => $photoId,
            ]);
            return $result['category'];
        }

        // Marquer comme traité même en cas d'échec
        $stmt = $this->db->prepare("UPDATE review_photos SET ai_processed = 1 WHERE id = :id");
        $stmt->execute([':id' => $photoId]);
        return null;
    }

    /**
     * Catégoriser une photo restaurant
     */
    public function categorizeRestaurantPhoto(int $photoId, string $photoPath): ?string
    {
        $result = $this->categorizePhoto($photoPath);

        if ($result) {
            $stmt = $this->db->prepare("
                UPDATE restaurant_photos
                SET ai_category = :cat, ai_labels = :labels
                WHERE id = :id
            ");
            $stmt->execute([
                ':cat' => $result['category'],
                ':labels' => json_encode($result['labels']),
                ':id' => $photoId,
            ]);
            return $result['category'];
        }

        return null;
    }

    /**
     * Message quand la limite est atteinte
     */
    public static function getLimitReachedMessage(): string
    {
        return "La categorisation automatique des photos est temporairement indisponible (limite mensuelle atteinte). Vos photos seront categori\u{73}ees automatiquement le mois prochain.";
    }

    /**
     * Catégorisation manuelle de secours (basée sur le nom du fichier ou contexte)
     */
    public function fallbackCategorize(string $context = ''): string
    {
        $context = strtolower($context);
        foreach ($this->categoryMapping as $keyword => $category) {
            if (stripos($context, $keyword) !== false) {
                return $category;
            }
        }
        return 'other';
    }

    /**
     * Obtenir les catégories disponibles
     */
    public static function getCategories(): array
    {
        return [
            'plat' => ['label' => 'Plats', 'icon' => 'fa-utensils'],
            'dessert' => ['label' => 'Desserts', 'icon' => 'fa-ice-cream'],
            'boisson' => ['label' => 'Boissons', 'icon' => 'fa-glass-cheers'],
            'interieur' => ['label' => 'Interieur', 'icon' => 'fa-couch'],
            'exterieur' => ['label' => 'Exterieur', 'icon' => 'fa-store'],
            'menu' => ['label' => 'Menu', 'icon' => 'fa-book-open'],
            'ambiance' => ['label' => 'Ambiance', 'icon' => 'fa-users'],
            'other' => ['label' => 'Autres', 'icon' => 'fa-camera'],
        ];
    }
}

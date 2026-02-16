<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use PDO;

/**
 * F27 - Profil preferences utilisateur
 * Gestion des preferences culinaires, regimes, allergies, gamme de prix, notifications
 * Stockees en JSON dans users.preferences
 */
class PreferencesController extends Controller
{
    /**
     * GET /preferences — Page de gestion des preferences
     */
    public function page(): void
    {
        $this->requireAuth();
        $this->render('preferences.index', [
            'title' => 'Mes préférences - LeBonResto',
        ]);
    }

    /**
     * Valeurs autorisees pour validation
     */
    private const VALID_CUISINES = [
        'algerien', 'tunisien', 'marocain', 'libanais', 'turc', 'syrien',
        'italien', 'francais', 'japonais', 'chinois', 'coreen', 'indien',
        'mexicain', 'americain', 'thai', 'vietnamien', 'espagnol', 'grec',
        'africain', 'senegalais', 'brésilien', 'fast-food', 'pizza',
        'burger', 'sushi', 'grillades', 'fruits_de_mer', 'patisserie',
        'cafe', 'creperie', 'autre',
    ];

    private const VALID_DIETS = [
        'vegetarien', 'vegan', 'halal', 'sans_gluten', 'casher',
        'pescetarien', 'keto', 'paleo', 'sans_lactose',
    ];

    private const VALID_ALLERGIES = [
        'gluten', 'dairy', 'eggs', 'fish', 'shellfish', 'nuts',
        'peanuts', 'soy', 'celery', 'mustard', 'sesame',
        'sulfites', 'lupin', 'mollusks',
    ];

    private const VALID_PRICE_RANGES = ['$', '$$', '$$$', '$$$$'];

    /**
     * GET /api/preferences
     * Retourne les preferences de l'utilisateur connecte
     */
    public function get(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT preferences FROM users WHERE id = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Utilisateur non trouvé']);
            exit;
        }

        // Decoder le JSON, ou retourner un objet vide par defaut
        $preferences = $row['preferences'] ? json_decode($row['preferences'], true) : null;

        if (!$preferences || !is_array($preferences)) {
            $preferences = $this->getDefaultPreferences();
        }

        echo json_encode([
            'success'     => true,
            'preferences' => $preferences,
        ]);
        exit;
    }

    /**
     * POST /api/preferences
     * Met a jour les preferences de l'utilisateur connecte
     * Body JSON: {"cuisines":["italien","japonais"],"diet":["vegetarien"],"allergies":["gluten"],"price_range":"$$","notifications":{"email":true,"push":true,"newsletter":true}}
     */
    public function update(): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            exit;
        }

        // CSRF check
        if (!verify_csrf()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Token CSRF invalide']);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];

        // Rate limit: 10 updates per minute
        if (!RateLimiter::attempt("preferences_update_{$userId}", 10, 60)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requêtes. Réessayez plus tard.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données JSON invalides']);
            exit;
        }

        // Construire les preferences validees
        $preferences = [];
        $errors = [];

        // Cuisines
        if (isset($input['cuisines'])) {
            if (!is_array($input['cuisines'])) {
                $errors[] = 'cuisines doit être un tableau';
            } else {
                $cuisines = [];
                foreach ($input['cuisines'] as $c) {
                    $c = trim((string)$c);
                    if (in_array($c, self::VALID_CUISINES, true)) {
                        $cuisines[] = $c;
                    }
                }
                $preferences['cuisines'] = array_values(array_unique($cuisines));
            }
        } else {
            $preferences['cuisines'] = [];
        }

        // Diet
        if (isset($input['diet'])) {
            if (!is_array($input['diet'])) {
                $errors[] = 'diet doit être un tableau';
            } else {
                $diets = [];
                foreach ($input['diet'] as $d) {
                    $d = trim((string)$d);
                    if (in_array($d, self::VALID_DIETS, true)) {
                        $diets[] = $d;
                    }
                }
                $preferences['diet'] = array_values(array_unique($diets));
            }
        } else {
            $preferences['diet'] = [];
        }

        // Allergies
        if (isset($input['allergies'])) {
            if (!is_array($input['allergies'])) {
                $errors[] = 'allergies doit être un tableau';
            } else {
                $allergies = [];
                foreach ($input['allergies'] as $a) {
                    $a = trim((string)$a);
                    if (in_array($a, self::VALID_ALLERGIES, true)) {
                        $allergies[] = $a;
                    }
                }
                $preferences['allergies'] = array_values(array_unique($allergies));
            }
        } else {
            $preferences['allergies'] = [];
        }

        // Price range
        if (isset($input['price_range'])) {
            $priceRange = trim((string)$input['price_range']);
            if ($priceRange !== '' && !in_array($priceRange, self::VALID_PRICE_RANGES, true)) {
                $errors[] = 'Gamme de prix invalide. Valeurs acceptées: $, $$, $$$, $$$$';
            } else {
                $preferences['price_range'] = $priceRange ?: null;
            }
        } else {
            $preferences['price_range'] = null;
        }

        // Notifications
        if (isset($input['notifications'])) {
            if (!is_array($input['notifications'])) {
                $errors[] = 'notifications doit être un objet';
            } else {
                $preferences['notifications'] = [
                    'email'      => !empty($input['notifications']['email']),
                    'push'       => !empty($input['notifications']['push']),
                    'newsletter' => !empty($input['notifications']['newsletter']),
                ];
            }
        } else {
            $preferences['notifications'] = [
                'email'      => true,
                'push'       => true,
                'newsletter' => true,
            ];
        }

        if (!empty($errors)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }

        // Sauvegarder en JSON dans users.preferences
        $preferencesJson = json_encode($preferences, JSON_UNESCAPED_UNICODE);

        $stmt = $this->db->prepare("
            UPDATE users SET preferences = :prefs WHERE id = :uid
        ");
        $stmt->execute([
            ':prefs' => $preferencesJson,
            ':uid'   => $userId,
        ]);

        echo json_encode([
            'success'     => true,
            'message'     => 'Préférences mises à jour',
            'preferences' => $preferences,
        ]);
        exit;
    }

    /**
     * Preferences par defaut pour un nouvel utilisateur
     */
    private function getDefaultPreferences(): array
    {
        return [
            'cuisines'      => [],
            'diet'          => [],
            'allergies'     => [],
            'price_range'   => null,
            'notifications' => [
                'email'      => true,
                'push'       => true,
                'newsletter' => true,
            ],
        ];
    }
}

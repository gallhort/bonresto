<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\RateLimiter;
use PDO;

/**
 * F14 - Gestion des allergenes pour les items de menu
 * Permet aux clients de voir les allergenes et aux owners de les gerer
 */
class AllergenController extends Controller
{
    /**
     * Liste maitre de tous les allergenes (14 allergenes reglementaires EU)
     */
    private const ALLERGENS = [
        'gluten'    => ['label' => 'Gluten',            'icon' => 'fas fa-bread-slice'],
        'dairy'     => ['label' => 'Produits laitiers',  'icon' => 'fas fa-cheese'],
        'eggs'      => ['label' => 'Œufs',              'icon' => 'fas fa-egg'],
        'fish'      => ['label' => 'Poisson',            'icon' => 'fas fa-fish'],
        'shellfish' => ['label' => 'Crustacés',          'icon' => 'fas fa-shrimp'],
        'nuts'      => ['label' => 'Fruits à coque',     'icon' => 'fas fa-seedling'],
        'peanuts'   => ['label' => 'Arachides',          'icon' => 'fas fa-circle'],
        'soy'       => ['label' => 'Soja',               'icon' => 'fas fa-leaf'],
        'celery'    => ['label' => 'Céleri',             'icon' => 'fas fa-carrot'],
        'mustard'   => ['label' => 'Moutarde',           'icon' => 'fas fa-mortar-pestle'],
        'sesame'    => ['label' => 'Sésame',             'icon' => 'fas fa-cookie'],
        'sulfites'  => ['label' => 'Sulfites',           'icon' => 'fas fa-wine-bottle'],
        'lupin'     => ['label' => 'Lupin',              'icon' => 'fas fa-spa'],
        'mollusks'  => ['label' => 'Mollusques',         'icon' => 'fas fa-water'],
    ];

    /**
     * GET /api/allergens
     * Retourne la liste maitre de tous les allergenes avec icones
     */
    public function list(): void
    {
        header('Content-Type: application/json');

        $result = [];
        foreach (self::ALLERGENS as $code => $info) {
            $result[] = [
                'code'  => $code,
                'label' => $info['label'],
                'icon'  => $info['icon'],
            ];
        }

        echo json_encode([
            'success'   => true,
            'allergens' => $result,
        ]);
        exit;
    }

    /**
     * GET /api/menu-item/{id}/allergens
     * Retourne les allergenes associes a un item de menu
     */
    public function getForItem(Request $request): void
    {
        header('Content-Type: application/json');

        $menuItemId = (int)$request->param('id');

        if ($menuItemId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID invalide']);
            exit;
        }

        // Verifier que l'item existe
        $itemStmt = $this->db->prepare("
            SELECT mi.id, mi.name, mi.restaurant_id
            FROM restaurant_menu_items mi
            WHERE mi.id = :id
        ");
        $itemStmt->execute([':id' => $menuItemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Article non trouvé']);
            exit;
        }

        // Recuperer les allergenes de cet item
        $stmt = $this->db->prepare("
            SELECT allergen FROM menu_item_allergens
            WHERE menu_item_id = :mid
            ORDER BY allergen
        ");
        $stmt->execute([':mid' => $menuItemId]);
        $allergenCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Enrichir avec les labels et icones
        $allergens = [];
        foreach ($allergenCodes as $code) {
            if (isset(self::ALLERGENS[$code])) {
                $allergens[] = [
                    'code'  => $code,
                    'label' => self::ALLERGENS[$code]['label'],
                    'icon'  => self::ALLERGENS[$code]['icon'],
                ];
            }
        }

        echo json_encode([
            'success'      => true,
            'menu_item_id' => $menuItemId,
            'item_name'    => $item['name'],
            'allergens'    => $allergens,
        ]);
        exit;
    }

    /**
     * POST /api/menu-item/{id}/allergens
     * Owner met a jour les allergenes d'un item de menu
     * Body JSON: ["gluten","dairy","eggs"]
     */
    public function update(Request $request): void
    {
        header('Content-Type: application/json');

        // Auth check
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
        $menuItemId = (int)$request->param('id');

        if ($menuItemId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID invalide']);
            exit;
        }

        // Rate limit: 30 updates per minute per user
        if (!RateLimiter::attempt("allergen_update_{$userId}", 30, 60)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Trop de requêtes. Réessayez plus tard.']);
            exit;
        }

        // Verifier que l'item existe et appartient a un restaurant de l'owner
        $itemStmt = $this->db->prepare("
            SELECT mi.id, mi.name, mi.restaurant_id, r.owner_id
            FROM restaurant_menu_items mi
            INNER JOIN restaurants r ON r.id = mi.restaurant_id
            WHERE mi.id = :mid
        ");
        $itemStmt->execute([':mid' => $menuItemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Article non trouvé']);
            exit;
        }

        // Verification de propriete
        if ((int)$item['owner_id'] !== $userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé. Vous n\'êtes pas le propriétaire de ce restaurant.']);
            exit;
        }

        // Parse input JSON
        $input = json_decode(file_get_contents('php://input'), true);

        if (!is_array($input)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Données invalides. Envoyez un tableau JSON de codes allergènes.']);
            exit;
        }

        // Valider chaque code allergene
        $validCodes = array_keys(self::ALLERGENS);
        $allergenCodes = [];
        foreach ($input as $code) {
            $code = trim((string)$code);
            if (!in_array($code, $validCodes, true)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Code allergène invalide: ' . htmlspecialchars($code)]);
                exit;
            }
            $allergenCodes[] = $code;
        }

        // Deduplicate
        $allergenCodes = array_unique($allergenCodes);

        // Transaction: delete old, insert new
        $this->db->beginTransaction();
        try {
            // Supprimer les anciens allergenes
            $deleteStmt = $this->db->prepare("
                DELETE FROM menu_item_allergens WHERE menu_item_id = :mid
            ");
            $deleteStmt->execute([':mid' => $menuItemId]);

            // Inserer les nouveaux
            if (!empty($allergenCodes)) {
                $insertStmt = $this->db->prepare("
                    INSERT INTO menu_item_allergens (menu_item_id, allergen)
                    VALUES (:mid, :allergen)
                ");

                foreach ($allergenCodes as $code) {
                    $insertStmt->execute([
                        ':mid'      => $menuItemId,
                        ':allergen' => $code,
                    ]);
                }
            }

            $this->db->commit();

            // Construire la reponse enrichie
            $allergens = [];
            foreach ($allergenCodes as $code) {
                $allergens[] = [
                    'code'  => $code,
                    'label' => self::ALLERGENS[$code]['label'],
                    'icon'  => self::ALLERGENS[$code]['icon'],
                ];
            }

            echo json_encode([
                'success'      => true,
                'message'      => 'Allergènes mis à jour',
                'menu_item_id' => $menuItemId,
                'allergens'    => $allergens,
                'count'        => count($allergenCodes),
            ]);
            exit;

        } catch (\Exception $e) {
            $this->db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur lors de la mise à jour des allergènes']);
            exit;
        }
    }
}

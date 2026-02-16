<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\ActivityFeedService;
use App\Services\RateLimiter;
use PDO;

/**
 * Controller pour les Collections publiques de restaurants
 */
class CollectionController extends Controller
{
    /**
     * Page - Toutes les collections publiques
     * GET /collections
     */
    public function index(Request $request): void
    {
        $sort = $_GET['sort'] ?? 'popular';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $orderBy = match ($sort) {
            'recent' => 'c.created_at DESC',
            'name' => 'c.title ASC',
            default => 'c.views_count DESC, restaurant_count DESC',
        };

        $stmt = $this->db->prepare("
            SELECT c.*, u.prenom, u.nom as user_nom, u.photo as user_photo,
                   (SELECT COUNT(*) FROM collection_restaurants cr WHERE cr.collection_id = c.id) as restaurant_count,
                   (SELECT rp.path FROM collection_restaurants cr2
                    INNER JOIN restaurant_photos rp ON rp.restaurant_id = cr2.restaurant_id AND rp.type = 'main'
                    WHERE cr2.collection_id = c.id ORDER BY cr2.position LIMIT 1) as first_photo
            FROM collections c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.is_public = 1
            HAVING restaurant_count > 0
            ORDER BY $orderBy
            LIMIT :lim OFFSET :off
        ");
        $stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $countStmt = $this->db->query("
            SELECT COUNT(*) FROM collections c
            WHERE c.is_public = 1
            AND (SELECT COUNT(*) FROM collection_restaurants cr WHERE cr.collection_id = c.id) > 0
        ");
        $total = (int)$countStmt->fetchColumn();

        $this->render('collections/index', [
            'title' => 'Collections de restaurants',
            'collections' => $collections,
            'sort' => $sort,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total / $perPage),
                'count' => $total,
            ],
        ]);
    }

    /**
     * Page - Voir une collection
     * GET /collections/{slug}
     */
    public function show(Request $request): void
    {
        $slug = $request->param('id'); // Le router passe le slug dans id

        $stmt = $this->db->prepare("
            SELECT c.*, u.prenom, u.nom as user_nom, u.photo as user_photo, u.id as author_id
            FROM collections c
            INNER JOIN users u ON u.id = c.user_id
            WHERE c.slug = :slug
        ");
        $stmt->execute([':slug' => $slug]);
        $collection = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$collection) {
            $this->notFound('Collection introuvable');
            return;
        }

        // Vérifier accès (publique ou propriétaire)
        $isOwner = $this->isAuthenticated() && (int)$_SESSION['user']['id'] === (int)$collection['user_id'];
        if (!$collection['is_public'] && !$isOwner) {
            $this->notFound('Collection privee');
            return;
        }

        // Incrémenter les vues
        $this->db->prepare("UPDATE collections SET views_count = views_count + 1 WHERE id = :id")
            ->execute([':id' => $collection['id']]);

        // Récupérer les restaurants de la collection
        $restosStmt = $this->db->prepare("
            SELECT cr.note_perso, cr.position, cr.added_at,
                   r.id, r.nom, r.slug, r.type_cuisine, r.ville, r.adresse,
                   r.price_range, COALESCE(r.note_moyenne, 0) as note_moyenne,
                   COALESCE(r.nb_avis, 0) as nb_avis,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as main_photo
            FROM collection_restaurants cr
            INNER JOIN restaurants r ON r.id = cr.restaurant_id AND r.status = 'validated'
            WHERE cr.collection_id = :cid
            ORDER BY cr.position ASC, cr.added_at ASC
        ");
        $restosStmt->execute([':cid' => $collection['id']]);
        $restaurants = $restosStmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('collections/show', [
            'title' => $collection['title'] . ' - Collection',
            'collection' => $collection,
            'restaurants' => $restaurants,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Page - Mes collections
     * GET /mes-collections
     */
    public function myCollections(Request $request): void
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user']['id'];

        $stmt = $this->db->prepare("
            SELECT c.*,
                   (SELECT COUNT(*) FROM collection_restaurants cr WHERE cr.collection_id = c.id) as restaurant_count
            FROM collections c
            WHERE c.user_id = :uid
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([':uid' => $userId]);

        $this->render('collections/my-collections', [
            'title' => 'Mes Collections',
            'collections' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ]);
    }

    /**
     * API - Créer une collection
     * POST /api/collections
     */
    public function apiCreate(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];

        if (!RateLimiter::attempt("collection_create_$userId", 5, 3600)) {
            http_response_code(429);
            echo json_encode(['success' => false, 'error' => 'Limite atteinte. Reessayez plus tard.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $isPublic = (int)($input['is_public'] ?? 1);

        if (strlen($title) < 3 || strlen($title) > 150) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le titre doit contenir entre 3 et 150 caracteres.']);
            return;
        }

        // Générer un slug unique
        $slug = $this->generateSlug($title);

        $stmt = $this->db->prepare("
            INSERT INTO collections (user_id, title, description, slug, is_public)
            VALUES (:uid, :title, :desc, :slug, :public)
        ");
        $stmt->execute([
            ':uid' => $userId,
            ':title' => $title,
            ':desc' => $description ?: null,
            ':slug' => $slug,
            ':public' => $isPublic,
        ]);

        $collectionId = (int)$this->db->lastInsertId();

        // Log activité
        $feedService = new ActivityFeedService($this->db);
        $feedService->log($userId, 'collection', 'collection', $collectionId, [
            'collection_name' => $title,
        ]);

        echo json_encode([
            'success' => true,
            'collection' => [
                'id' => $collectionId,
                'slug' => $slug,
                'title' => $title,
            ],
            'message' => 'Collection creee !',
        ]);
    }

    /**
     * API - Ajouter un restaurant à une collection
     * POST /api/collections/{id}/add
     */
    public function apiAddRestaurant(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $collectionId = (int)$request->param('id');

        $input = json_decode(file_get_contents('php://input'), true);
        $restaurantId = (int)($input['restaurant_id'] ?? 0);
        $notePerso = trim($input['note'] ?? '');

        // Vérifier propriété de la collection
        $colStmt = $this->db->prepare("SELECT id FROM collections WHERE id = :cid AND user_id = :uid");
        $colStmt->execute([':cid' => $collectionId, ':uid' => $userId]);
        if (!$colStmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Collection introuvable']);
            return;
        }

        // Vérifier que le restaurant existe
        $restoStmt = $this->db->prepare("SELECT id, nom FROM restaurants WHERE id = :rid AND status = 'validated'");
        $restoStmt->execute([':rid' => $restaurantId]);
        $restaurant = $restoStmt->fetch(PDO::FETCH_ASSOC);
        if (!$restaurant) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Restaurant introuvable']);
            return;
        }

        // Position = prochain numéro
        $posStmt = $this->db->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM collection_restaurants WHERE collection_id = :cid");
        $posStmt->execute([':cid' => $collectionId]);
        $nextPos = (int)$posStmt->fetchColumn();

        try {
            $stmt = $this->db->prepare("
                INSERT INTO collection_restaurants (collection_id, restaurant_id, note_perso, position)
                VALUES (:cid, :rid, :note, :pos)
                ON DUPLICATE KEY UPDATE note_perso = VALUES(note_perso)
            ");
            $stmt->execute([
                ':cid' => $collectionId,
                ':rid' => $restaurantId,
                ':note' => $notePerso ?: null,
                ':pos' => $nextPos,
            ]);

            echo json_encode([
                'success' => true,
                'message' => $restaurant['nom'] . ' ajoute a la collection !',
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * API - Retirer un restaurant d'une collection
     * POST /api/collections/{id}/remove
     */
    public function apiRemoveRestaurant(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $collectionId = (int)$request->param('id');

        $input = json_decode(file_get_contents('php://input'), true);
        $restaurantId = (int)($input['restaurant_id'] ?? 0);

        // Vérifier propriété
        $colStmt = $this->db->prepare("SELECT id FROM collections WHERE id = :cid AND user_id = :uid");
        $colStmt->execute([':cid' => $collectionId, ':uid' => $userId]);
        if (!$colStmt->fetch()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorise']);
            return;
        }

        $this->db->prepare("DELETE FROM collection_restaurants WHERE collection_id = :cid AND restaurant_id = :rid")
            ->execute([':cid' => $collectionId, ':rid' => $restaurantId]);

        echo json_encode(['success' => true, 'message' => 'Restaurant retire de la collection']);
    }

    /**
     * API - Supprimer une collection
     * POST /api/collections/{id}/delete
     */
    public function apiDelete(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $collectionId = (int)$request->param('id');

        $stmt = $this->db->prepare("DELETE FROM collections WHERE id = :cid AND user_id = :uid");
        $stmt->execute([':cid' => $collectionId, ':uid' => $userId]);

        echo json_encode([
            'success' => true,
            'deleted' => $stmt->rowCount() > 0,
            'message' => 'Collection supprimee',
        ]);
    }

    /**
     * API - Lister les collections de l'utilisateur (pour le modal "ajouter à")
     * GET /api/my-collections
     */
    public function apiMyCollections(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $userId = (int)$_SESSION['user']['id'];
        $restaurantId = (int)($_GET['restaurant_id'] ?? 0);

        $stmt = $this->db->prepare("
            SELECT c.id, c.title, c.slug, c.is_public,
                   (SELECT COUNT(*) FROM collection_restaurants cr WHERE cr.collection_id = c.id) as count
            FROM collections c
            WHERE c.user_id = :uid
            ORDER BY c.updated_at DESC
        ");
        $stmt->execute([':uid' => $userId]);
        $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si restaurant_id fourni, marquer les collections qui le contiennent déjà
        if ($restaurantId) {
            $checkStmt = $this->db->prepare("
                SELECT collection_id FROM collection_restaurants
                WHERE restaurant_id = :rid AND collection_id IN (
                    SELECT id FROM collections WHERE user_id = :uid
                )
            ");
            $checkStmt->execute([':rid' => $restaurantId, ':uid' => $userId]);
            $existing = $checkStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($collections as &$col) {
                $col['contains_restaurant'] = in_array($col['id'], $existing);
            }
        }

        echo json_encode(['success' => true, 'collections' => $collections]);
    }

    /**
     * Générer un slug unique
     */
    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        if (empty($slug)) {
            $slug = 'collection';
        }

        // Vérifier unicité
        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            $checkStmt = $this->db->prepare("SELECT id FROM collections WHERE slug = :slug");
            $checkStmt->execute([':slug' => $slug]);
            if (!$checkStmt->fetch()) break;
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

use App\Services\Logger;
use App\Services\NotificationService;
class RestaurantController extends Controller
{
    public function search(): void
    {
        $hasGeo = !empty($_GET['lat']) && !empty($_GET['lng']);
        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'ville' => $_GET['ville'] ?? '',
            'lat' => $_GET['lat'] ?? null,
            'lng' => $_GET['lng'] ?? null,
            'radius' => $hasGeo ? ($_GET['radius'] ?? 10) : ($_GET['radius'] ?? ''),
            'type' => $_GET['type'] ?? '',
            'price' => $_GET['price'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'open_now' => $_GET['open_now'] ?? '',
            'amenities' => $_GET['amenities'] ?? '',
            'sort' => $_GET['sort'] ?? ($hasGeo ? 'distance' : 'popularity'),
            'view' => $_GET['view'] ?? 'list',
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'perPage' => min(100, max(1, (int)($_GET['perPage'] ?? 20))),
            'top' => isset($_GET['top']) ? min(500, max(1, (int)$_GET['top'])) : null,
        ];

        try {
            // Restaurants paginés pour la liste
            $results = $this->getRestaurants($filters, false);

            // Cap total if top parameter is set
            if ($filters['top'] && $results['total'] > $filters['top']) {
                $results['total'] = $filters['top'];
                $results['pagination']['totalItems'] = $filters['top'];
                $results['pagination']['totalPages'] = (int)ceil($filters['top'] / $filters['perPage']);
                $results['pagination']['hasNextPage'] = $filters['page'] < $results['pagination']['totalPages'];
            }

            // Tous les restaurants pour la carte (mêmes filtres, sans pagination)
            $mapResults = $this->getRestaurants($filters, true);

            // Cap map results too
            if ($filters['top'] && count($mapResults['data']) > $filters['top']) {
                $mapResults['data'] = array_slice($mapResults['data'], 0, $filters['top']);
            }

            $types = $this->db->query("
                SELECT DISTINCT type_cuisine 
                FROM restaurants 
                WHERE status = 'validated' AND type_cuisine IS NOT NULL
                ORDER BY type_cuisine
            ")->fetchAll(PDO::FETCH_ASSOC);

            $this->view->renderPartial('restaurants/search', [
                'title' => 'Recherche restaurants',
                'restaurants' => $results['data'],
                'mapRestaurants' => $mapResults['data'],
                'total' => $results['total'],
                'pagination' => $results['pagination'],
                'filters' => $filters,
                'types' => $types,
                'view' => $filters['view']
            ]);
        } catch (\Exception $e) {
            Logger::error('Erreur recherche restaurants', [$e->getMessage()]);
            http_response_code(500);
            echo "Une erreur est survenue. Veuillez réessayer.";
        }
    }

    public function apiFilter(): void
    {
        header('Content-Type: application/json');

        $forMap = !empty($_GET['forMap']);

        $filters = [
            'q' => trim($_GET['q'] ?? ''),
            'lat' => $_GET['lat'] ?? null,
            'lng' => $_GET['lng'] ?? null,
            'radius' => $_GET['radius'] ?? 10,
            'type' => $_GET['type'] ?? '',
            'price' => $_GET['price'] ?? '',
            'rating' => $_GET['rating'] ?? '',
            'open_now' => $_GET['open_now'] ?? '',
            'amenities' => $_GET['amenities'] ?? '',
            'sort' => $_GET['sort'] ?? 'distance',
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'perPage' => min(100, max(1, (int)($_GET['perPage'] ?? 20))),
            'top' => isset($_GET['top']) ? min(500, max(1, (int)$_GET['top'])) : null,
        ];

        try {
            $restaurants = $this->getRestaurants($filters, $forMap);

            // Cap results if top parameter
            $total = $restaurants['total'];
            if ($filters['top'] && $total > $filters['top']) {
                $total = $filters['top'];
                if ($forMap) {
                    $restaurants['data'] = array_slice($restaurants['data'], 0, $filters['top']);
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $restaurants['data'],
                'total' => $total
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            Logger::error('Erreur API filter', [$e->getMessage()]);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur interne du serveur'
            ]);
        }
    }

    private function getRestaurants(array $filters, bool $forMap = false): array
    {
        $userLat = !empty($filters['lat']) ? (float)$filters['lat'] : null;
        $userLng = !empty($filters['lng']) ? (float)$filters['lng'] : null;
        $page = $filters['page'] ?? 1;
        $perPage = $filters['perPage'] ?? 20;
        $offset = ($page - 1) * $perPage;

        $where = ["r.status = 'validated'"];
        $having = [];
        $params = []; // Tout en named params pour compatibilité EMULATE_PREPARES=false

        $where[] = "r.gps_latitude IS NOT NULL";
        $where[] = "r.gps_longitude IS NOT NULL";

        $select = "r.id, r.nom, r.gps_latitude, r.gps_longitude, r.type_cuisine, r.price_range, r.ville, r.wilaya, r.adresse, r.popularity_score, COALESCE(r.note_moyenne, 0) as note_moyenne";

        if ($userLat && $userLng) {
            $select .= ", (6371 * acos(
                cos(radians(:user_lat1)) * cos(radians(r.gps_latitude)) *
                cos(radians(r.gps_longitude) - radians(:user_lng1)) +
                sin(radians(:user_lat2)) * sin(radians(r.gps_latitude))
            )) as distance";
            $params[':user_lat1'] = $userLat;
            $params[':user_lng1'] = $userLng;
            $params[':user_lat2'] = $userLat;

            if (!empty($filters['radius'])) {
                $having[] = "distance <= :radius";
                $params[':radius'] = (float)$filters['radius'];
            }
        } else {
            $select .= ", 0 as distance";
        }

        // Recherche textuelle par nom/ville/type
        // Si q == ville ET on a des coordonnées GPS → recherche par ville (autocomplete)
        // → on ne filtre PAS par texte, on laisse le radius GPS faire le travail
        $isCitySearch = !empty($filters['q']) && !empty($filters['ville'])
            && strtolower(trim($filters['q'])) === strtolower(trim($filters['ville']))
            && $userLat && $userLng;

        if (!empty($filters['q']) && !$isCitySearch) {
            $searchTerm = '%' . $filters['q'] . '%';
            $where[] = "(r.nom LIKE :q1 OR r.ville LIKE :q2 OR r.type_cuisine LIKE :q3 OR r.adresse LIKE :q4)";
            $params[':q1'] = $searchTerm;
            $params[':q2'] = $searchTerm;
            $params[':q3'] = $searchTerm;
            $params[':q4'] = $searchTerm;
        }

        // Filtre par ville texte : seulement si PAS de GPS (sinon le radius gère la géo)
        if (!empty($filters['ville']) && !$isCitySearch && !($userLat && $userLng)) {
            $where[] = "r.ville LIKE :ville";
            $params[':ville'] = '%' . $filters['ville'] . '%';
        }

        if (!empty($filters['type'])) {
            $where[] = "r.type_cuisine = :type_cuisine";
            $params[':type_cuisine'] = $filters['type'];
        }

        if (!empty($filters['price'])) {
            // Normalize $ to € (DB stores €)
            $priceVal = str_replace('$', '€', $filters['price']);
            $where[] = "r.price_range = :price_range";
            $params[':price_range'] = $priceVal;
        }

        if (!empty($filters['rating'])) {
            $where[] = "r.note_moyenne >= :min_rating";
            $params[':min_rating'] = (float)$filters['rating'];
        }

        // Filtre "ouvert maintenant" : jointure avec horaires
        $joinHoraires = "";
        if (!empty($filters['open_now'])) {
            $jourActuel = (int)(new \DateTime('now', new \DateTimeZone('Africa/Algiers')))->format('N') - 1;
            $heureActuelle = (new \DateTime('now', new \DateTimeZone('Africa/Algiers')))->format('H:i:s');
            $joinHoraires = " INNER JOIN restaurant_horaires rh ON rh.restaurant_id = r.id
                AND rh.jour_semaine = :jour_actuel
                AND rh.ferme = 0
                AND (rh.ouverture_matin IS NOT NULL OR rh.ouverture_soir IS NOT NULL)
                AND (
                    (rh.service_continu = 1 AND rh.ouverture_matin <= :heure1 AND rh.fermeture_soir >= :heure2)
                    OR (rh.service_continu = 0 AND rh.ouverture_matin <= :heure3 AND rh.fermeture_matin >= :heure4)
                    OR (rh.service_continu = 0 AND rh.ouverture_soir <= :heure5 AND rh.fermeture_soir >= :heure6)
                )";
            $params[':jour_actuel'] = $jourActuel;
            $params[':heure1'] = $heureActuelle;
            $params[':heure2'] = $heureActuelle;
            $params[':heure3'] = $heureActuelle;
            $params[':heure4'] = $heureActuelle;
            $params[':heure5'] = $heureActuelle;
            $params[':heure6'] = $heureActuelle;
        }

        // Filtre équipements/services (colonnes directes dans restaurant_options)
        $joinAmenities = "";
        $amenityColMap = [
            'wifi' => 'wifi', 'parking' => 'parking', 'terrasse' => 'terrace',
            'climatisation' => 'air_conditioning', 'livraison' => 'delivery',
            'emporter' => 'takeaway', 'accessible_pmr' => 'handicap_access',
            'espace_enfants' => 'game_zone', 'espace_prive' => 'private_room',
            'priere' => 'prayer_room', 'voiturier' => 'valet_service',
            'animaux' => 'pets_allowed', 'chaise_bebe' => 'baby_chair',
        ];
        if (!empty($filters['amenities'])) {
            $amenitiesList = explode(',', $filters['amenities']);
            $amenityCols = [];
            foreach ($amenitiesList as $a) {
                $a = trim($a);
                if (isset($amenityColMap[$a])) {
                    $amenityCols[] = $amenityColMap[$a];
                }
            }
            if (!empty($amenityCols)) {
                $joinAmenities = " INNER JOIN restaurant_options ro_am ON ro_am.restaurant_id = r.id";
                foreach ($amenityCols as $col) {
                    $joinAmenities .= " AND ro_am.{$col} = 1";
                }
            }
        }

        // ══════════════════════════════════════════════════════════════
        // REQUÊTE COUNT (pour pagination)
        // ══════════════════════════════════════════════════════════════
        // Dupliquer les params GPS avec préfixe :cnt_ pour la requête count
        $countParams = [];
        foreach ($params as $k => $v) {
            if (in_array($k, [':user_lat1', ':user_lng1', ':user_lat2', ':radius'])) {
                $cntKey = str_replace(':user_', ':cnt_', str_replace(':radius', ':cnt_radius', $k));
                $countParams[$cntKey] = $v;
            } else {
                $countParams[$k] = $v;
            }
        }

        $countHaving = [];
        foreach ($having as $h) {
            $countHaving[] = str_replace(':radius', ':cnt_radius', $h);
        }

        $countSql = "SELECT COUNT(*) as total FROM (
            SELECT r.id" . ($userLat && $userLng ? ", (6371 * acos(
                cos(radians(:cnt_lat1)) * cos(radians(r.gps_latitude)) *
                cos(radians(r.gps_longitude) - radians(:cnt_lng1)) +
                sin(radians(:cnt_lat2)) * sin(radians(r.gps_latitude))
            )) as distance" : "") . "
            FROM restaurants r {$joinHoraires} {$joinAmenities}
            WHERE " . implode(' AND ', $where) .
            (!empty($countHaving) ? " HAVING " . implode(' AND ', $countHaving) : "") .
        ") as counted";

        $countStmt = $this->db->prepare($countSql);
        foreach ($countParams as $k => $v) {
            $countStmt->bindValue($k, $v);
        }
        $countStmt->execute();
        $totalCount = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // ══════════════════════════════════════════════════════════════
        // REQUÊTE PRINCIPALE
        // ══════════════════════════════════════════════════════════════
        // Vérifier si la table restaurant_awards existe
        $hasAwards = false;
        try {
            $this->db->query("SELECT 1 FROM restaurant_awards LIMIT 1");
            $hasAwards = true;
        } catch (\Exception $e) { /* table n'existe pas */ }

        $awardSubquery = $hasAwards
            ? ", (SELECT ra.award_type FROM restaurant_awards ra WHERE ra.restaurant_id = r.id AND ra.award_year = YEAR(CURDATE()) ORDER BY FIELD(ra.award_type, 'travelers_choice', 'top_city', 'best_cuisine', 'trending', 'newcomer') LIMIT 1) AS top_award"
            : ", NULL AS top_award";

        $sql = "SELECT {$select},
                   (SELECT COUNT(*) FROM reviews rev WHERE rev.restaurant_id = r.id AND rev.status = 'approved') AS nb_avis,
                   (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' ORDER BY rp.ordre ASC LIMIT 1) AS main_photo
                   {$awardSubquery}
                FROM restaurants r {$joinHoraires} {$joinAmenities}
                WHERE " . implode(' AND ', $where);

        if (!empty($having)) {
            $sql .= " HAVING " . implode(' AND ', $having);
        }

        // Tri dynamique
        $orderBy = "distance ASC"; // Par défaut
        switch ($filters['sort'] ?? 'distance') {
            case 'relevance':
                $orderBy = "note_moyenne DESC, nb_avis DESC, distance ASC";
                break;
            case 'rating':
                $orderBy = "note_moyenne DESC, distance ASC";
                break;
            case 'price_low':
                $orderBy = "FIELD(r.price_range, '€', '€€', '€€€'), distance ASC";
                break;
            case 'price_high':
                $orderBy = "FIELD(r.price_range, '€€€', '€€', '€'), distance ASC";
                break;
            case 'popularity':
                $orderBy = "r.popularity_score DESC, note_moyenne DESC";
                break;
            case 'newest':
                $orderBy = "r.created_at DESC";
                break;
            case 'distance':
            default:
                $orderBy = "distance ASC";
                break;
        }

        $sql .= " ORDER BY {$orderBy}";

        // Pagination seulement pour la liste, pas pour la carte
        if (!$forMap) {
            $sql .= " LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        } else {
            $sql .= " LIMIT 2000"; // Enough for all Algerian restaurants on map
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $restaurantIds = array_column($restaurants, 'id');

        // Batch ranking using single window function query (1 query for ALL restaurants)
        // Compute rank among ALL validated restaurants, then filter to our IDs
        $rankLookup = [];
        if (!empty($restaurantIds)) {
            $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
            $rankStmt = $this->db->prepare("
                SELECT id, wilaya, ville, type_cuisine, cuisine_rank, cuisine_total FROM (
                    SELECT id, wilaya, ville, type_cuisine,
                           RANK() OVER (PARTITION BY COALESCE(wilaya, ville), type_cuisine ORDER BY popularity_score DESC) as cuisine_rank,
                           COUNT(*) OVER (PARTITION BY COALESCE(wilaya, ville), type_cuisine) as cuisine_total
                    FROM restaurants
                    WHERE status = 'validated' AND type_cuisine IS NOT NULL AND (wilaya IS NOT NULL OR ville IS NOT NULL)
                ) ranked
                WHERE id IN ({$placeholders})
            ");
            $rankStmt->execute($restaurantIds);
            foreach ($rankStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $rankLookup[$row['id']] = $row;
            }
        }

        // For map queries, skip review titles (heavy) but include ranking (now fast)
        if ($forMap) {
            foreach ($restaurants as &$resto) {
                if (isset($resto['distance'])) {
                    $resto['distance_formatted'] = number_format($resto['distance'], 1) . ' km';
                }
                $resto['reviews_titles'] = [];
                $resto['cuisine_rank'] = null;
                if (isset($rankLookup[$resto['id']])) {
                    $rl = $rankLookup[$resto['id']];
                    if ((int)$rl['cuisine_total'] > 1) {
                        $resto['cuisine_rank'] = [
                            'rank' => (int)$rl['cuisine_rank'],
                            'total' => (int)$rl['cuisine_total'],
                            'cuisine' => $rl['type_cuisine'],
                            'region' => $rl['wilaya'] ?: $rl['ville'],
                        ];
                    }
                }
            }
            unset($resto);

            return [
                'data' => $restaurants,
                'total' => $totalCount,
                'pagination' => [
                    'currentPage' => (int)$page,
                    'perPage' => (int)$perPage,
                    'totalPages' => (int)ceil($totalCount / $perPage),
                    'totalItems' => $totalCount,
                    'hasNextPage' => $page < ceil($totalCount / $perPage),
                    'hasPrevPage' => $page > 1,
                    'from' => $totalCount > 0 ? $offset + 1 : 0,
                    'to' => min($offset + $perPage, $totalCount)
                ]
            ];
        }

        // Batch load review titles for all restaurants (fix N+1)
        $titlesByRestaurant = [];
        if (!empty($restaurantIds)) {
            $placeholders = implode(',', array_fill(0, count($restaurantIds), '?'));
            $titlesStmt = $this->db->prepare("
                SELECT restaurant_id, title
                FROM (
                    SELECT restaurant_id, title,
                           ROW_NUMBER() OVER (PARTITION BY restaurant_id ORDER BY created_at DESC) as rn
                    FROM reviews
                    WHERE restaurant_id IN ({$placeholders})
                      AND status = 'approved'
                      AND title IS NOT NULL
                ) ranked
                WHERE rn <= 2
            ");
            $titlesStmt->execute($restaurantIds);
            foreach ($titlesStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $titlesByRestaurant[$row['restaurant_id']][] = $row['title'];
            }
        }

        foreach ($restaurants as &$resto) {
            if (isset($resto['distance'])) {
                $resto['distance_formatted'] = number_format($resto['distance'], 1) . ' km';
            }
            $resto['reviews_titles'] = $titlesByRestaurant[$resto['id']] ?? [];

            // Ranking from batch lookup
            $resto['cuisine_rank'] = null;
            if (isset($rankLookup[$resto['id']])) {
                $rl = $rankLookup[$resto['id']];
                if ((int)$rl['cuisine_total'] > 1) {
                    $resto['cuisine_rank'] = [
                        'rank' => (int)$rl['cuisine_rank'],
                        'total' => (int)$rl['cuisine_total'],
                        'cuisine' => $rl['type_cuisine'],
                        'region' => $rl['wilaya'] ?: $rl['ville'],
                    ];
                }
            }
        }

        return [
            'data' => $restaurants,
            'total' => $totalCount,
            'pagination' => [
                'currentPage' => (int)$page,
                'perPage' => (int)$perPage,
                'totalPages' => (int)ceil($totalCount / $perPage),
                'totalItems' => $totalCount,
                'hasNextPage' => $page < ceil($totalCount / $perPage),
                'hasPrevPage' => $page > 1,
                'from' => $totalCount > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalCount)
            ]
        ];
    }

    /**
     * ═══════════════════════════════════════════════════════════════════════════
     * PAGE DÉTAILS RESTAURANT - VERSION PREMIUM
     * ═══════════════════════════════════════════════════════════════════════════
     */
    public function show($request): void
    {
        // Extraire l'ID ou slug depuis la Request
        $identifier = null;
        if (is_object($request) && method_exists($request, 'getParam')) {
            $identifier = $request->getParam('id');
        } elseif (is_object($request) && isset($request->params['id'])) {
            $identifier = $request->params['id'];
        } elseif (is_int($request)) {
            $identifier = $request;
        } else {
            $uri = $_SERVER['REQUEST_URI'];
            preg_match('/\/restaurant\/([^\/\?]+)/', $uri, $matches);
            $identifier = $matches[1] ?? null;
        }

        if (!$identifier) {
            $this->notFound('Restaurant non trouvé');
            return;
        }

        // Support ID numérique OU slug textuel
        $isNumeric = ctype_digit((string)$identifier);

        // ─────────────────────────────────────────────────────────────────────
        // 1. DONNÉES RESTAURANT PRINCIPALES
        // ─────────────────────────────────────────────────────────────────────
        if ($isNumeric) {
            $sql = "SELECT r.*,
                        COALESCE(r.note_moyenne, 0) as note_moyenne,
                        (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved') as nb_avis,
                        COALESCE(r.nb_avis, 0) as nb_avis_google
                    FROM restaurants r
                    WHERE r.id = ? AND r.status = 'validated'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([(int)$identifier]);
        } else {
            $sql = "SELECT r.*,
                        COALESCE(r.note_moyenne, 0) as note_moyenne,
                        (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved') as nb_avis,
                        COALESCE(r.nb_avis, 0) as nb_avis_google
                    FROM restaurants r
                    WHERE r.slug = ? AND r.status = 'validated'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$identifier]);
        }
        $restaurant = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = $restaurant ? (int)$restaurant['id'] : 0;

        if (!$restaurant) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Restaurant non trouvé']);
            return;
        }

        // Incrémenter les vues
        $this->db->prepare("UPDATE restaurants SET vues_total = vues_total + 1 WHERE id = ?")->execute([$id]);

        // ─────────────────────────────────────────────────────────────────────
        // 2. PHOTOS (groupées par type)
        // ─────────────────────────────────────────────────────────────────────
        $photosStmt = $this->db->prepare("
            SELECT * FROM restaurant_photos 
            WHERE restaurant_id = ? 
            ORDER BY type = 'main' DESC, ordre ASC
        ");
        $photosStmt->execute([$id]);
        $allPhotos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

        $restaurant['photos'] = $allPhotos;
        $restaurant['photos_by_type'] = [
            'main' => [],
            'slide' => [],
            'menu' => [],
            'ambiance' => [],
            'plat' => [],
            'autre' => []
        ];

        foreach ($allPhotos as $photo) {
            $type = $photo['type'] ?? 'autre';
            $restaurant['photos_by_type'][$type][] = $photo;
        }

        // ─────────────────────────────────────────────────────────────────────
        // 3. HORAIRES
        // ─────────────────────────────────────────────────────────────────────
        $horairesStmt = $this->db->prepare("
            SELECT * FROM restaurant_horaires 
            WHERE restaurant_id = ? 
            ORDER BY jour_semaine ASC
        ");
        $horairesStmt->execute([$id]);
        $horairesRaw = $horairesStmt->fetchAll(PDO::FETCH_ASSOC);

        $restaurant['horaires'] = $this->formatHoraires($horairesRaw);
        $restaurant['is_open_now'] = $this->isOpenNow($horairesRaw);

        // ─────────────────────────────────────────────────────────────────────
        // 4. AVIS AVEC STATISTIQUES DÉTAILLÉES
        // ─────────────────────────────────────────────────────────────────────
        $reviewsStmt = $this->db->prepare("
    SELECT rev.*,
           u.prenom as user_prenom,
           u.nom as user_nom,
           u.photo_profil as user_photo,
           u.ville as user_ville,
           u.badge as user_badge,
           u.points as user_points,
           b.icon as user_badge_icon,
           b.color as user_badge_color,
           (SELECT COUNT(*) FROM reviews WHERE user_id = rev.user_id AND status = 'approved') as user_total_reviews,
           (SELECT GROUP_CONCAT(photo_path ORDER BY display_order SEPARATOR '|||')
            FROM review_photos
            WHERE review_id = rev.id) as photos,
           (SELECT title_label FROM user_titles WHERE user_id = rev.user_id AND is_active = 1 ORDER BY earned_at DESC LIMIT 1) as user_title,
           (SELECT title_icon FROM user_titles WHERE user_id = rev.user_id AND is_active = 1 ORDER BY earned_at DESC LIMIT 1) as user_title_icon,
           (SELECT title_color FROM user_titles WHERE user_id = rev.user_id AND is_active = 1 ORDER BY earned_at DESC LIMIT 1) as user_title_color,
           (SELECT 1 FROM checkins c
            WHERE c.user_id = rev.user_id AND c.restaurant_id = rev.restaurant_id
            AND c.created_at <= DATE_ADD(rev.created_at, INTERVAL 48 HOUR)
            LIMIT 1) as has_checkin,
           (SELECT COUNT(*) FROM reviews r2
            WHERE r2.user_id = rev.user_id AND r2.restaurant_id = rev.restaurant_id
            AND r2.status = 'approved') as user_visits_this_resto
    FROM reviews rev
    LEFT JOIN users u ON u.id = rev.user_id
    LEFT JOIN badges b ON b.name = u.badge
    WHERE rev.restaurant_id = ? AND rev.status = 'approved'
    ORDER BY rev.created_at DESC
");
        $reviewsStmt->execute([$id]);
        $restaurant['reviews'] = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Statistiques des notes
        $restaurant['rating_stats'] = $this->getRatingStats($id);

        // ─────────────────────────────────────────────────────────────────────
        // 5. AWARDS / BADGES
        // ─────────────────────────────────────────────────────────────────────
        try {
            $awardsStmt = $this->db->prepare("
                SELECT * FROM restaurant_awards
                WHERE restaurant_id = ?
                ORDER BY award_year DESC
            ");
            $awardsStmt->execute([$id]);
            $restaurant['awards'] = $awardsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $restaurant['awards'] = [];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 6. QUESTIONS & RÉPONSES
        // ─────────────────────────────────────────────────────────────────────
        try {
            $qaStmt = $this->db->prepare("
                SELECT q.*, u.prenom as user_prenom, u.nom as user_nom, u.photo_profil as user_photo,
                       (SELECT COUNT(*) FROM restaurant_qa_answers WHERE question_id = q.id) as answer_count
                FROM restaurant_qa q
                LEFT JOIN users u ON u.id = q.user_id
                WHERE q.restaurant_id = ? AND q.status = 'active'
                ORDER BY q.created_at DESC
                LIMIT 20
            ");
            $qaStmt->execute([$id]);
            $questions = $qaStmt->fetchAll(PDO::FETCH_ASSOC);

            // Batch load answers for all questions (fix N+1)
            $questionIds = array_column($questions, 'id');
            $answersByQuestion = [];
            if (!empty($questionIds)) {
                $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
                $ansStmt = $this->db->prepare("
                    SELECT a.*, u.prenom as user_prenom, u.nom as user_nom, u.photo_profil as user_photo
                    FROM restaurant_qa_answers a
                    LEFT JOIN users u ON u.id = a.user_id
                    WHERE a.question_id IN ({$placeholders})
                    ORDER BY a.is_owner_answer DESC, a.votes DESC, a.created_at ASC
                ");
                $ansStmt->execute($questionIds);
                foreach ($ansStmt->fetchAll(PDO::FETCH_ASSOC) as $ans) {
                    $answersByQuestion[$ans['question_id']][] = $ans;
                }
            }
            foreach ($questions as &$q) {
                $q['answers'] = $answersByQuestion[$q['id']] ?? [];
            }
            unset($q);
            $restaurant['questions'] = $questions;
        } catch (\Exception $e) {
            $restaurant['questions'] = [];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 7. RESTAURANTS SIMILAIRES
        // ─────────────────────────────────────────────────────────────────────
        $restaurant['similar'] = $this->getSimilarRestaurants($restaurant);

        // ─────────────────────────────────────────────────────────────────────
        // 8. MENU ITEMS (si activé par le propriétaire)
        // ─────────────────────────────────────────────────────────────────────
        if (!empty($restaurant['menu_enabled'])) {
            $menuStmt = $this->db->prepare("
                SELECT * FROM restaurant_menu_items
                WHERE restaurant_id = ? AND is_available = 1
                ORDER BY category, position
            ");
            $menuStmt->execute([$id]);
            $restaurant['menu_items'] = $menuStmt->fetchAll(PDO::FETCH_ASSOC);

            // Load allergens for all menu items in one query
            $menuIds = array_column($restaurant['menu_items'], 'id');
            if (!empty($menuIds)) {
                $ph = implode(',', array_fill(0, count($menuIds), '?'));
                $algStmt = $this->db->prepare("
                    SELECT menu_item_id, allergen FROM menu_item_allergens
                    WHERE menu_item_id IN ({$ph})
                ");
                $algStmt->execute($menuIds);
                $allergenMap = [];
                foreach ($algStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $allergenMap[(int)$row['menu_item_id']][] = $row['allergen'];
                }
                foreach ($restaurant['menu_items'] as &$mi) {
                    $mi['allergens'] = $allergenMap[(int)$mi['id']] ?? [];
                }
                unset($mi);
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 9. QUICK TIPS
        // ─────────────────────────────────────────────────────────────────────
        try {
            $tipsStmt = $this->db->prepare("
                SELECT t.*, u.prenom, u.nom as user_nom, u.photo_profil as user_photo
                FROM restaurant_tips t
                INNER JOIN users u ON u.id = t.user_id
                WHERE t.restaurant_id = ? AND t.status = 'approved'
                ORDER BY t.votes DESC, t.created_at DESC
                LIMIT 10
            ");
            $tipsStmt->execute([$id]);
            $restaurant['tips'] = $tipsStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $restaurant['tips'] = [];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 10. CONTEXT TAGS (aggregated from review tags)
        // ─────────────────────────────────────────────────────────────────────
        try {
            $ctxStmt = $this->db->prepare("
                SELECT tag, vote_count FROM restaurant_context_tags
                WHERE restaurant_id = ? AND vote_count >= 1
                ORDER BY vote_count DESC LIMIT 12
            ");
            $ctxStmt->execute([$id]);
            $restaurant['context_tags'] = $ctxStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $restaurant['context_tags'] = [];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 10b. FRIENDS REVIEWS (reviews from followed users)
        // ─────────────────────────────────────────────────────────────────────
        $restaurant['friends_reviews'] = [];
        if (isset($_SESSION['user']['id'])) {
            try {
                $friendsStmt = $this->db->prepare("
                    SELECT rev.note_globale, rev.message, rev.created_at,
                           u.id as user_id, u.prenom, u.nom as user_nom, u.photo_profil as user_photo, u.badge as user_badge
                    FROM reviews rev
                    INNER JOIN users u ON u.id = rev.user_id
                    INNER JOIN user_follows uf ON uf.followed_id = rev.user_id AND uf.follower_id = :uid
                    WHERE rev.restaurant_id = :rid AND rev.status = 'approved'
                    ORDER BY rev.created_at DESC
                    LIMIT 5
                ");
                $friendsStmt->execute([':uid' => (int)$_SESSION['user']['id'], ':rid' => $id]);
                $restaurant['friends_reviews'] = $friendsStmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                $restaurant['friends_reviews'] = [];
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 11. POPULAR DISHES (cross-reference menu items with review mentions)
        // ─────────────────────────────────────────────────────────────────────
        $restaurant['popular_dishes'] = [];
        if (!empty($restaurant['menu_items'])) {
            try {
                // Combine review mentions + actual order counts
                $approvedReviews = $this->db->prepare("
                    SELECT message FROM reviews WHERE restaurant_id = :rid AND status = 'approved'
                ");
                $approvedReviews->execute([':rid' => $id]);
                $allMessages = $approvedReviews->fetchAll(PDO::FETCH_COLUMN);
                $combinedText = mb_strtolower(implode(' ', $allMessages));

                // Get order counts per menu item (real popularity data)
                $orderCountsStmt = $this->db->prepare("
                    SELECT oi.menu_item_id, SUM(oi.quantity) as total_ordered
                    FROM order_items oi
                    INNER JOIN orders o ON o.id = oi.order_id
                    WHERE o.restaurant_id = :rid AND o.status IN ('delivered', 'confirmed', 'preparing', 'ready')
                    GROUP BY oi.menu_item_id
                ");
                $orderCountsStmt->execute([':rid' => $id]);
                $orderCounts = [];
                foreach ($orderCountsStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $orderCounts[(int)$row['menu_item_id']] = (int)$row['total_ordered'];
                }

                $dishes = [];
                foreach ($restaurant['menu_items'] as $item) {
                    $name = mb_strtolower($item['name']);
                    if (mb_strlen($name) < 3) continue;
                    $mentions = substr_count($combinedText, $name);
                    $orders = $orderCounts[(int)$item['id']] ?? 0;
                    $score = $mentions + ($orders * 2); // Orders count double
                    if ($score > 0) {
                        $dishes[] = ['name' => $item['name'], 'mentions' => $mentions, 'orders' => $orders, 'price' => $item['price']];
                    }
                }
                usort($dishes, fn($a, $b) => ($b['mentions'] + $b['orders'] * 2) - ($a['mentions'] + $a['orders'] * 2));
                $restaurant['popular_dishes'] = array_slice($dishes, 0, 5);
            } catch (\Exception $e) {
                $restaurant['popular_dishes'] = [];
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 6. SCHEMA.ORG JSON-LD
        // ─────────────────────────────────────────────────────────────────────
        $mainPhoto = null;
        foreach ($allPhotos as $p) {
            if ($p['type'] === 'main') { $mainPhoto = $p['path']; break; }
        }
        $baseUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz');
        $ogImage = $mainPhoto ? $baseUrl . $mainPhoto : null;

        $schemaOrg = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => $restaurant['nom'],
            'url' => $baseUrl . '/restaurant/' . $id,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $restaurant['adresse'] ?? '',
                'addressLocality' => $restaurant['ville'] ?? '',
                'addressCountry' => 'DZ'
            ]
        ];
        if (!empty($restaurant['type_cuisine'])) {
            $schemaOrg['servesCuisine'] = $restaurant['type_cuisine'];
        }
        if (!empty($restaurant['phone'])) {
            $schemaOrg['telephone'] = $restaurant['phone'];
        }
        if ($mainPhoto) {
            $schemaOrg['image'] = $ogImage;
        }
        if ($restaurant['note_moyenne'] > 0) {
            $schemaOrg['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($restaurant['note_moyenne'], 1),
                'bestRating' => '5',
                'worstRating' => '1',
                'reviewCount' => (int)$restaurant['nb_avis']
            ];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 12. RECENTLY VIEWED (server-side for logged-in users)
        // ─────────────────────────────────────────────────────────────────────
        if (isset($_SESSION['user']['id'])) {
            try {
                $this->db->prepare("
                    INSERT INTO user_recently_viewed (user_id, restaurant_id)
                    VALUES (:uid, :rid)
                    ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP
                ")->execute([':uid' => (int)$_SESSION['user']['id'], ':rid' => $id]);
            } catch (\Exception $e) { /* table may not exist yet */ }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 13. REVIEW SUMMARY (AI keyword extraction)
        // ─────────────────────────────────────────────────────────────────────
        $restaurant['review_summary'] = null;
        if ((int)$restaurant['nb_avis'] >= 3) {
            try {
                $summaryService = new \App\Services\ReviewSummaryService($this->db);
                $restaurant['review_summary'] = $summaryService->getSummary($id);
            } catch (\Exception $e) { /* table may not exist yet */ }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 14. ACTIVE OFFERS
        // ─────────────────────────────────────────────────────────────────────
        $restaurant['offers'] = [];
        try {
            $restaurant['offers'] = \App\Controllers\OfferController::getActiveOffers($id);
        } catch (\Exception $e) { /* table may not exist yet */ }

        // ─────────────────────────────────────────────────────────────────────
        // 15. ENHANCED JSON-LD (breadcrumbs, opening hours, price range)
        // ─────────────────────────────────────────────────────────────────────
        if (!empty($restaurant['price_range'])) {
            $priceMap = ['$' => '$', '$$' => '$$', '$$$' => '$$$', '$$$$' => '$$$$'];
            $schemaOrg['priceRange'] = $priceMap[$restaurant['price_range']] ?? '$$';
        }
        // Add opening hours to schema
        $dayMap = [0 => 'Monday', 1 => 'Tuesday', 2 => 'Wednesday', 3 => 'Thursday', 4 => 'Friday', 5 => 'Saturday', 6 => 'Sunday'];
        $openingHours = [];
        foreach ($horairesRaw as $h) {
            if (empty($h['est_ferme']) && !empty($h['heure_ouverture'])) {
                $dayName = $dayMap[(int)$h['jour_semaine']] ?? '';
                if ($dayName) {
                    $openingHours[] = [
                        '@type' => 'OpeningHoursSpecification',
                        'dayOfWeek' => $dayName,
                        'opens' => $h['heure_ouverture'],
                        'closes' => $h['heure_fermeture'] ?? '23:00'
                    ];
                }
            }
        }
        if (!empty($openingHours)) {
            $schemaOrg['openingHoursSpecification'] = $openingHours;
        }

        // Breadcrumb JSON-LD
        $breadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Accueil', 'item' => $baseUrl],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Restaurants', 'item' => $baseUrl . '/search'],
            ]
        ];
        if (!empty($restaurant['ville'])) {
            $breadcrumbSchema['itemListElement'][] = [
                '@type' => 'ListItem', 'position' => 3,
                'name' => $restaurant['ville'],
                'item' => $baseUrl . '/search?ville=' . urlencode($restaurant['ville'])
            ];
            $breadcrumbSchema['itemListElement'][] = [
                '@type' => 'ListItem', 'position' => 4, 'name' => $restaurant['nom']
            ];
        } else {
            $breadcrumbSchema['itemListElement'][] = [
                '@type' => 'ListItem', 'position' => 3, 'name' => $restaurant['nom']
            ];
        }

        // ─────────────────────────────────────────────────────────────────────
        // 16. RANKING SÉLECTIF (style TripAdvisor)
        // ─────────────────────────────────────────────────────────────────────
        $ranking = [];
        $restoWilaya = $restaurant['wilaya'] ?? '';
        $restoVille = $restaurant['ville'] ?? '';
        $restoScore = (float)($restaurant['popularity_score'] ?? 0);
        $restoCuisine = $restaurant['type_cuisine'] ?? '';
        $rankRegion = $restoWilaya ?: $restoVille;

        if ($rankRegion) {
            // Rank in wilaya/ville (global)
            $rankField = $restoWilaya ? 'wilaya' : 'ville';
            $rkStmt = $this->db->prepare("
                SELECT COUNT(*) + 1 as rk FROM restaurants
                WHERE {$rankField} = ? AND status = 'validated' AND popularity_score > ?
            ");
            $rkStmt->execute([$rankRegion, $restoScore]);
            $globalRank = (int)$rkStmt->fetchColumn();

            $totalStmt = $this->db->prepare("
                SELECT COUNT(*) FROM restaurants WHERE {$rankField} = ? AND status = 'validated'
            ");
            $totalStmt->execute([$rankRegion]);
            $totalInRegion = (int)$totalStmt->fetchColumn();

            $ranking['global'] = [
                'rank' => $globalRank,
                'total' => $totalInRegion,
                'region' => $rankRegion,
            ];

            // Rank by cuisine in wilaya/ville
            if ($restoCuisine) {
                $rkCStmt = $this->db->prepare("
                    SELECT COUNT(*) + 1 as rk FROM restaurants
                    WHERE {$rankField} = ? AND type_cuisine = ? AND status = 'validated' AND popularity_score > ?
                ");
                $rkCStmt->execute([$rankRegion, $restoCuisine, $restoScore]);
                $cuisineRank = (int)$rkCStmt->fetchColumn();

                $totalCStmt = $this->db->prepare("
                    SELECT COUNT(*) FROM restaurants WHERE {$rankField} = ? AND type_cuisine = ? AND status = 'validated'
                ");
                $totalCStmt->execute([$rankRegion, $restoCuisine]);
                $totalCuisine = (int)$totalCStmt->fetchColumn();

                if ($totalCuisine > 1) {
                    $ranking['cuisine'] = [
                        'rank' => $cuisineRank,
                        'total' => $totalCuisine,
                        'cuisine' => $restoCuisine,
                        'region' => $rankRegion,
                    ];
                }
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // 17. RENDU
        // ─────────────────────────────────────────────────────────────────────
        $metaDesc = $this->generateMetaDescription($restaurant);
        $this->render('restaurants/show', [
            'title' => $restaurant['nom'] . ' - Restaurant ' . ($restaurant['type_cuisine'] ?? ''),
            'meta_description' => $metaDesc,
            'og_title' => $restaurant['nom'] . ' - Le Bon Resto',
            'og_description' => $metaDesc,
            'og_type' => 'restaurant',
            'og_image' => $ogImage,
            'og_url' => $baseUrl . '/restaurant/' . $id,
            'schema_json' => json_encode($schemaOrg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP),
            'breadcrumb_json' => json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP),
            'restaurant' => $restaurant,
            'ranking' => $ranking,
        ]);
    }

    /**
     * Formater les horaires pour l'affichage
     */
    private function formatHoraires(array $horairesRaw): array
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $horaires = [];

        // Initialiser tous les jours
        for ($i = 0; $i < 7; $i++) {
            $horaires[$i] = [
                'jour' => $jours[$i],
                'jour_court' => mb_substr($jours[$i], 0, 3),
                'ferme' => true,
                'service_continu' => false,
                'periodes' => []
            ];
        }

        foreach ($horairesRaw as $h) {
            $jour = (int)$h['jour_semaine'];
            if ($jour < 0 || $jour > 6) continue;

            $horaires[$jour]['ferme'] = (bool)$h['ferme'];
            $horaires[$jour]['service_continu'] = (bool)$h['service_continu'];

            if (!$h['ferme']) {
                // Vérifier qu'il y a de vraies données horaires
                if (!$h['ouverture_matin'] && !$h['ouverture_soir']) {
                    continue;
                }

                if ($h['service_continu'] && $h['ouverture_matin'] && $h['fermeture_soir']) {
                    // Service continu
                    $horaires[$jour]['periodes'][] = [
                        'debut' => substr($h['ouverture_matin'], 0, 5),
                        'fin' => substr($h['fermeture_soir'], 0, 5)
                    ];
                } else {
                    // Service midi
                    if ($h['ouverture_matin'] && $h['fermeture_matin']) {
                        $horaires[$jour]['periodes'][] = [
                            'debut' => substr($h['ouverture_matin'], 0, 5),
                            'fin' => substr($h['fermeture_matin'], 0, 5)
                        ];
                    }
                    // Service soir
                    if ($h['ouverture_soir'] && $h['fermeture_soir']) {
                        $horaires[$jour]['periodes'][] = [
                            'debut' => substr($h['ouverture_soir'], 0, 5),
                            'fin' => substr($h['fermeture_soir'], 0, 5)
                        ];
                    }
                }
            }
        }

        return $horaires;
    }

    /**
     * Vérifier si le restaurant est ouvert maintenant
     */
    private function isOpenNow(array $horairesRaw): array
    {
        $now = new \DateTime('now', new \DateTimeZone('Africa/Algiers'));
        $jourActuel = (int)$now->format('N') - 1; // 0 = Lundi
        $heureActuelle = $now->format('H:i:s');

        $result = [
            'is_open' => false,
            'message' => 'Horaires non disponibles',
            'next_open' => null
        ];

        if (empty($horairesRaw)) return $result;

        foreach ($horairesRaw as $h) {
            if ((int)$h['jour_semaine'] !== $jourActuel) continue;

            if ($h['ferme']) {
                $result['message'] = 'Fermé aujourd\'hui';
                break;
            }

            // Pas de données horaires réelles
            if (!$h['ouverture_matin'] && !$h['ouverture_soir']) {
                $result['message'] = 'Horaires non disponibles';
                break;
            }

            // Vérifier service continu
            if ($h['service_continu'] && $h['ouverture_matin'] && $h['fermeture_soir']) {
                if ($heureActuelle >= $h['ouverture_matin'] && $heureActuelle <= $h['fermeture_soir']) {
                    $result['is_open'] = true;
                    $result['message'] = 'Ouvert · Ferme à ' . substr($h['fermeture_soir'], 0, 5);
                } elseif ($heureActuelle < $h['ouverture_matin']) {
                    $result['message'] = 'Fermé · Ouvre à ' . substr($h['ouverture_matin'], 0, 5);
                } else {
                    $result['message'] = 'Fermé';
                }
            } else {
                // Vérifier midi
                if ($h['ouverture_matin'] && $h['fermeture_matin']) {
                    if ($heureActuelle >= $h['ouverture_matin'] && $heureActuelle <= $h['fermeture_matin']) {
                        $result['is_open'] = true;
                        $result['message'] = 'Ouvert · Ferme à ' . substr($h['fermeture_matin'], 0, 5);
                    } elseif ($heureActuelle < $h['ouverture_matin']) {
                        $result['message'] = 'Fermé · Ouvre à ' . substr($h['ouverture_matin'], 0, 5);
                    }
                }
                // Vérifier soir
                if (!$result['is_open'] && $h['ouverture_soir'] && $h['fermeture_soir']) {
                    if ($heureActuelle >= $h['ouverture_soir'] && $heureActuelle <= $h['fermeture_soir']) {
                        $result['is_open'] = true;
                        $result['message'] = 'Ouvert · Ferme à ' . substr($h['fermeture_soir'], 0, 5);
                    } elseif ($heureActuelle < $h['ouverture_soir'] && $heureActuelle > ($h['fermeture_matin'] ?? '00:00')) {
                        $result['message'] = 'Fermé · Ouvre à ' . substr($h['ouverture_soir'], 0, 5);
                    }
                }
            }
            break;
        }

        return $result;
    }

    /**
     * Statistiques détaillées des notes
     */
    private function getRatingStats(int $restaurantId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                AVG(note_globale) as moyenne,
                AVG(note_nourriture) as moy_nourriture,
                AVG(note_service) as moy_service,
                AVG(note_ambiance) as moy_ambiance,
                AVG(note_prix) as moy_prix,
                SUM(CASE WHEN note_globale >= 4.5 THEN 1 ELSE 0 END) as count_5,
                SUM(CASE WHEN note_globale >= 3.5 AND note_globale < 4.5 THEN 1 ELSE 0 END) as count_4,
                SUM(CASE WHEN note_globale >= 2.5 AND note_globale < 3.5 THEN 1 ELSE 0 END) as count_3,
                SUM(CASE WHEN note_globale >= 1.5 AND note_globale < 2.5 THEN 1 ELSE 0 END) as count_2,
                SUM(CASE WHEN note_globale < 1.5 THEN 1 ELSE 0 END) as count_1
            FROM reviews 
            WHERE restaurant_id = ? AND status = 'approved'
        ");
        $stmt->execute([$restaurantId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int)$stats['total'];

        return [
            'total' => $total,
            'moyenne' => round((float)$stats['moyenne'], 1),
            'categories' => [
                'nourriture' => round((float)$stats['moy_nourriture'], 1),
                'service' => round((float)$stats['moy_service'], 1),
                'ambiance' => round((float)$stats['moy_ambiance'], 1),
                'prix' => round((float)$stats['moy_prix'], 1),
            ],
            'distribution' => [
                5 => ['count' => (int)$stats['count_5'], 'percent' => $total > 0 ? round(($stats['count_5'] / $total) * 100) : 0],
                4 => ['count' => (int)$stats['count_4'], 'percent' => $total > 0 ? round(($stats['count_4'] / $total) * 100) : 0],
                3 => ['count' => (int)$stats['count_3'], 'percent' => $total > 0 ? round(($stats['count_3'] / $total) * 100) : 0],
                2 => ['count' => (int)$stats['count_2'], 'percent' => $total > 0 ? round(($stats['count_2'] / $total) * 100) : 0],
                1 => ['count' => (int)$stats['count_1'], 'percent' => $total > 0 ? round(($stats['count_1'] / $total) * 100) : 0],
            ]
        ];
    }

    /**
     * Restaurants similaires
     */
    private function getSimilarRestaurants(array $restaurant): array
    {
        $params = [$restaurant['id']];
        $conditions = ["r.id != ?", "r.status = 'validated'"];

        // Même type de cuisine OU même ville
        if (!empty($restaurant['type_cuisine'])) {
            $conditions[] = "(r.type_cuisine = ? OR r.ville = ?)";
            $params[] = $restaurant['type_cuisine'];
            $params[] = $restaurant['ville'] ?? '';
        } else {
            $conditions[] = "r.ville = ?";
            $params[] = $restaurant['ville'] ?? '';
        }

        $sql = "SELECT r.id, r.nom, r.slug, r.type_cuisine, r.price_range, r.ville,
                       COALESCE(r.note_moyenne, 0) as note_moyenne,
                       COALESCE(r.nb_avis, 0) as nb_avis,
                       (SELECT path FROM restaurant_photos rp WHERE rp.restaurant_id = r.id AND rp.type = 'main' LIMIT 1) as main_photo
                FROM restaurants r
                WHERE " . implode(' AND ', $conditions) . "
                ORDER BY r.note_moyenne DESC, r.nb_avis DESC
                LIMIT 6";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * API - Compute and return review summary
     * GET /api/restaurant/{id}/summary
     */
    public function apiReviewSummary(Request $request): void
    {
        header('Content-Type: application/json');
        $id = (int)$request->param('id');
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID requis']);
            return;
        }
        try {
            $summaryService = new \App\Services\ReviewSummaryService($this->db);
            $summary = $summaryService->computeSummary($id);
            echo json_encode(['success' => true, 'summary' => $summary]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur']);
        }
    }

    /**
     * Meta description pour SEO
     */
    private function generateMetaDescription(array $restaurant): string
    {
        $parts = [];
        $parts[] = $restaurant['nom'];
        
        if (!empty($restaurant['type_cuisine'])) {
            $parts[] = 'restaurant ' . $restaurant['type_cuisine'];
        }
        
        if (!empty($restaurant['ville'])) {
            $parts[] = 'à ' . $restaurant['ville'];
        }
        
        if ($restaurant['note_moyenne'] > 0) {
            $parts[] = 'noté ' . number_format($restaurant['note_moyenne'], 1) . '/5';
        }

        return implode(' - ', $parts) . '. Découvrez les avis, photos, menu et réservez en ligne.';
    }
    /**
 * Affiche le formulaire d'ajout de restaurant
 */
/**
 * Affiche le formulaire d'ajout de restaurant
 */
public function create(): void
{
        $this->db->exec("SET NAMES utf8mb4");  // ← AJOUTER ICI

    // Récupérer les types de cuisine (juste les labels)
    $stmt = $this->db->query("SELECT label FROM types_cuisine WHERE actif = 1 ORDER BY label ASC");
    $cuisineTypes = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    
    $this->view->renderPartial('restaurants/create', [
        'title' => 'Ajouter un restaurant',
        'cuisineTypes' => $cuisineTypes
    ]);
}

/**
 * Enregistre un nouveau restaurant
 */
public function store(Request $request): void
{
    header('Content-Type: application/json');

    if (!verify_csrf()) {
        $this->json(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        return;
    }

    try {
        // Récupérer les données du formulaire
        $nom = trim($request->post('nom'));
        $typeCuisine = $request->post('type_cuisine');
        $description = trim($request->post('description') ?? '');
        $adresse = trim($request->post('adresse'));
        $ville = trim($request->post('ville'));
        $wilaya = trim($request->post('wilaya') ?? '');
        $codePostal = trim($request->post('code_postal') ?? '');
        $phone = trim($request->post('phone') ?? '');
        $whatsapp = trim($request->post('whatsapp') ?? '');
        $email = trim($request->post('email') ?? '');
        $website = trim($request->post('website') ?? '');
        $facebook = trim($request->post('facebook') ?? '');
        $instagram = trim($request->post('instagram') ?? '');
        $priceRange = $request->post('price_range') ?? '$$';
        $latitude = $request->post('gps_latitude') ?? null;
        $longitude = $request->post('gps_longitude') ?? null;

        // Validation basique
        if (empty($nom) || empty($adresse) || empty($ville)) {
            echo json_encode(['success' => false, 'message' => 'Nom, adresse et ville sont obligatoires']);
            return;
        }

        // Générer le slug
        $slug = $this->generateSlug($nom);

        // Owner ID (utilisateur connecté)
        $ownerId = $_SESSION['user']['id'] ?? null;

        $this->db->beginTransaction();

        try {
            // Insérer le restaurant
            $stmt = $this->db->prepare("
                INSERT INTO restaurants
                (nom, slug, type_cuisine, description, adresse, ville, wilaya, code_postal,
                 phone, whatsapp, email, website, facebook, instagram, price_range,
                 gps_latitude, gps_longitude, owner_id, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");

            $stmt->execute([
                $nom, $slug, $typeCuisine, $description, $adresse, $ville, $wilaya, $codePostal,
                $phone, $whatsapp, $email, $website, $facebook, $instagram, $priceRange,
                $latitude, $longitude, $ownerId
            ]);

            $restaurantId = $this->db->lastInsertId();

            // Gérer les horaires
            $this->saveHoraires($restaurantId, $request);

            // Gérer les options/services
            $this->saveOptions($restaurantId, $request);

            // Gérer les photos
            $this->savePhotos($restaurantId, $request);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        echo json_encode([
            'success' => true,
            'message' => 'Restaurant ajouté avec succès !',
            'redirect' => '/'
        ]);

    } catch (\Exception $e) {
        Logger::error(trim("Erreur store restaurant: "), [$e->getMessage()]);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
    }
}

/**
 * Génère un slug unique
 */
private function generateSlug(string $nom): string
{
    $slug = strtolower(trim($nom));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Vérifier unicité
    $baseSlug = $slug;
    $counter = 1;
    while (true) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM restaurants WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetchColumn() == 0) break;
        $slug = $baseSlug . '-' . $counter++;
    }
    
    return $slug;
}

/**
 * Sauvegarde les horaires
 */
private function saveHoraires(int $restaurantId, Request $request): void
{
    // Supprimer les anciens horaires (permet l'update)
    $this->db->prepare("DELETE FROM restaurant_horaires WHERE restaurant_id = ?")->execute([$restaurantId]);

    $horairesData = $request->post('horaires');
    if (!is_array($horairesData)) return;

    $stmt = $this->db->prepare("
        INSERT INTO restaurant_horaires
        (restaurant_id, jour_semaine, ferme, service_continu,
         ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    for ($i = 0; $i < 7; $i++) {
        $h = $horairesData[$i] ?? [];
        $ferme = !empty($h['ferme']) ? 1 : 0;
        $continu = !empty($h['service_continu']) ? 1 : 0;

        if ($ferme) {
            $stmt->execute([$restaurantId, $i, 1, 0, null, null, null, null]);
        } elseif ($continu) {
            $stmt->execute([$restaurantId, $i, 0, 1,
                $h['continu_ouverture'] ?? null, null, null, $h['continu_fermeture'] ?? null]);
        } else {
            $stmt->execute([$restaurantId, $i, 0, 0,
                $h['ouverture_matin'] ?? null, $h['fermeture_matin'] ?? null,
                $h['ouverture_soir'] ?? null, $h['fermeture_soir'] ?? null]);
        }
    }
}

/**
 * Sauvegarde les options/services
 */
private function saveOptions(int $restaurantId, Request $request): void
{
    $validOptions = ['wifi', 'parking', 'terrasse', 'climatisation', 'reservation',
                     'livraison', 'emporter', 'paiement_carte', 'accessible_pmr',
                     'espace_enfants', 'espace_prive', 'priere'];

    // Form sends options as options[key]=1 (nested array)
    $formOptions = $_POST['options'] ?? [];

    // Map form field names to option_name values
    $fieldMap = [
        'terrace' => 'terrasse', 'air_conditioning' => 'climatisation',
        'delivery' => 'livraison', 'takeaway' => 'emporter',
        'baby_chair' => 'espace_enfants', 'private_room' => 'espace_prive',
        'prayer_room' => 'priere',
    ];

    $stmt = $this->db->prepare("
        INSERT INTO restaurant_options (restaurant_id, option_name, option_value)
        VALUES (:rid, :name, '1')
    ");

    foreach ($formOptions as $field => $value) {
        $optionName = $fieldMap[$field] ?? $field;
        if (in_array($optionName, $validOptions, true) && $value) {
            $stmt->execute([':rid' => $restaurantId, ':name' => $optionName]);
        }
    }
}

/**
 * Sauvegarde les photos uploadées
 */
private function savePhotos(int $restaurantId, Request $request): void
{
    if (empty($_FILES['photos']['name'][0])) return;
    
    $uploadDir = __DIR__ . '/../../public/uploads/restaurants/' . $restaurantId . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $photoTypes = $request->post('photo_types') ?? [];
    
    foreach ($_FILES['photos']['name'] as $index => $name) {
        if ($_FILES['photos']['error'][$index] !== UPLOAD_ERR_OK) continue;
        
        $tmpName = $_FILES['photos']['tmp_name'][$index];
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $newName = uniqid() . '.' . $ext;
        $path = '/uploads/restaurants/' . $restaurantId . '/' . $newName;
        
        if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
            $type = $photoTypes[$index] ?? 'slide';
            $ordre = $index;
            
            $stmt = $this->db->prepare("
                INSERT INTO restaurant_photos (restaurant_id, path, type, ordre)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$restaurantId, $path, $type, $ordre]);
        }
    }
}

    // ═══════════════════════════════════════════════════════════════════════
    // Q&A — QUESTIONS & RÉPONSES
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * API — Poser une question sur un restaurant
     */
    public function apiAskQuestion($request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $restaurantId = (int)($request->params['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');

        if (strlen($question) < 10) {
            echo json_encode(['success' => false, 'error' => 'La question doit faire au moins 10 caractères']);
            return;
        }
        if (strlen($question) > 500) {
            echo json_encode(['success' => false, 'error' => 'La question ne doit pas dépasser 500 caractères']);
            return;
        }

        // Rate limit: 5 questions par heure
        if (!\App\Services\RateLimiter::attempt('qa_ask_' . $_SESSION['user']['id'], 5, 3600)) {
            echo json_encode(['success' => false, 'error' => 'Trop de questions, réessayez plus tard']);
            return;
        }

        try {
            $stmt = $this->db->prepare("
                INSERT INTO restaurant_qa (restaurant_id, user_id, question)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$restaurantId, $_SESSION['user']['id'], $question]);
            $questionId = (int)$this->db->lastInsertId();

            // Notifier le propriétaire si le restaurant est revendiqué
            $ownerStmt = $this->db->prepare("SELECT owner_id FROM restaurants WHERE id = ? AND owner_id IS NOT NULL");
            $ownerStmt->execute([$restaurantId]);
            $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);
            if ($owner && !empty($owner['owner_id'])) {
                $notifService = new NotificationService($this->db);
                $notifService->notifyNewQuestion((int)$owner['owner_id'], $restaurantId, $question);
            }

            echo json_encode(['success' => true, 'message' => 'Question publiée !']);
        } catch (\Exception $e) {
            Logger::error("Error posting question: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la publication']);
        }
    }

    /**
     * API — Répondre à une question
     */
    public function apiAnswerQuestion($request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Connexion requise']);
            return;
        }

        $questionId = (int)($request->params['id'] ?? 0);
        $answer = trim($_POST['answer'] ?? '');

        if (strlen($answer) < 5) {
            echo json_encode(['success' => false, 'error' => 'La réponse doit faire au moins 5 caractères']);
            return;
        }
        if (strlen($answer) > 1000) {
            echo json_encode(['success' => false, 'error' => 'La réponse ne doit pas dépasser 1000 caractères']);
            return;
        }

        // Rate limit: 10 réponses par heure
        if (!\App\Services\RateLimiter::attempt('qa_answer_' . $_SESSION['user']['id'], 10, 3600)) {
            echo json_encode(['success' => false, 'error' => 'Trop de réponses, réessayez plus tard']);
            return;
        }

        try {
            // Vérifier que la question existe et récupérer le restaurant
            $q = $this->db->prepare("
                SELECT q.*, r.owner_id FROM restaurant_qa q
                JOIN restaurants r ON r.id = q.restaurant_id
                WHERE q.id = ? AND q.status = 'active'
            ");
            $q->execute([$questionId]);
            $question = $q->fetch(PDO::FETCH_ASSOC);

            if (!$question) {
                echo json_encode(['success' => false, 'error' => 'Question introuvable']);
                return;
            }

            $isOwner = (!empty($question['owner_id']) && (int)$question['owner_id'] === (int)$_SESSION['user']['id']) ? 1 : 0;

            $stmt = $this->db->prepare("
                INSERT INTO restaurant_qa_answers (question_id, user_id, answer, is_owner_answer)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$questionId, $_SESSION['user']['id'], $answer, $isOwner]);

            // Notifier l'auteur de la question
            if ((int)$question['user_id'] !== (int)$_SESSION['user']['id']) {
                $notifService = new NotificationService($this->db);
                $notifService->notifyQaAnswer(
                    (int)$question['user_id'],
                    (int)$question['restaurant_id'],
                    $questionId,
                    (bool)$isOwner
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Réponse publiée !',
                'is_owner' => $isOwner
            ]);
        } catch (\Exception $e) {
            Logger::error("Error posting answer: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la publication']);
        }
    }

    /**
     * F9 - Classement popularite des restaurants
     * GET /classement-restaurants ou /classement-restaurants/{ville}
     */
    public function ranking($request = null): void
    {
        $ville = null;
        if ($request && method_exists($request, 'param')) {
            $ville = $request->param('ville');
        }
        if (!$ville) {
            $ville = $_GET['ville'] ?? null;
        }
        $cuisine = $_GET['cuisine'] ?? null;

        $cache = new \App\Services\CacheService();

        // Villes disponibles (cache 1h)
        $cities = $cache->remember('ranking_cities', function() {
            $stmt = $this->db->query("
                SELECT ville, COUNT(*) as nb_restos, ROUND(AVG(note_moyenne), 1) as avg_note
                FROM restaurants
                WHERE status = 'validated' AND ville IS NOT NULL AND ville != ''
                GROUP BY ville
                HAVING nb_restos >= 3
                ORDER BY nb_restos DESC
                LIMIT 30
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        // Cuisines disponibles (cache 1h)
        $cuisines = $cache->remember('ranking_cuisines', function() {
            $stmt = $this->db->query("
                SELECT type_cuisine, COUNT(*) as nb_restos
                FROM restaurants
                WHERE status = 'validated' AND type_cuisine IS NOT NULL AND type_cuisine != ''
                GROUP BY type_cuisine
                HAVING nb_restos >= 5
                ORDER BY nb_restos DESC
                LIMIT 20
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 3600);

        // Top 50 restaurants par popularite (cache 30min)
        $cacheKey = 'ranking_restos_' . ($ville ?? 'all') . '_' . ($cuisine ?? 'all');
        $db = $this->db;

        $restaurants = $cache->remember($cacheKey, function() use ($db, $ville, $cuisine) {
            $params = [];
            $extraClauses = '';
            if ($ville) {
                $extraClauses .= ' AND r.ville = :ville';
                $params[':ville'] = $ville;
            }
            if ($cuisine) {
                $extraClauses .= ' AND r.type_cuisine = :cuisine';
                $params[':cuisine'] = $cuisine;
            }

            $sql = "
                SELECT r.id, r.nom, r.slug, r.ville, r.wilaya, r.type_cuisine, r.price_range,
                       r.note_moyenne, r.nb_avis, r.vues_total, r.popularity_score,
                       r.orders_enabled,
                       rp.path as main_photo,
                       (SELECT ra.award_type FROM restaurant_awards ra WHERE ra.restaurant_id = r.id ORDER BY ra.award_year DESC LIMIT 1) as top_award,
                       (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved') as platform_reviews
                FROM restaurants r
                LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id AND rp.type = 'main' AND rp.ordre = 0
                WHERE r.status = 'validated' AND r.popularity_score > 0 {$extraClauses}
                GROUP BY r.id
                ORDER BY r.popularity_score DESC
                LIMIT 50
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 1800);

        $titleParts = ['Classement'];
        $descParts = ['les restaurants les plus populaires'];
        if ($cuisine) {
            $titleParts[] = $cuisine;
            $descParts = ['les meilleurs ' . $cuisine];
        }
        if ($ville) {
            $titleParts[] = $ville;
            $descParts[] = 'a ' . $ville;
        }

        $this->render('restaurants.ranking', [
            'title' => implode(' - ', $titleParts) . ' | Le Bon Resto',
            'meta_description' => 'Decouvrez ' . implode(' ', $descParts) . ($ville ? '' : ' en Algerie') . '. Classement base sur les avis, les vues et les commandes.',
            'restaurants' => $restaurants,
            'cities' => $cities,
            'cuisines' => $cuisines,
            'currentVille' => $ville,
            'currentCuisine' => $cuisine,
            'totalRanked' => count($restaurants),
        ]);
    }

}
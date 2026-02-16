<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Review;
use App\Models\Restaurant;
use App\Services\Logger;
use App\Services\EmailService;
use App\Services\LoyaltyService;
use App\Services\NotificationService;
use App\Services\RateLimiter;
/**
 * Controller pour la gestion des avis
 */
class ReviewController extends Controller
{
    private Review $reviewModel;
    private Restaurant $restaurantModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->reviewModel = new Review();
        $this->restaurantModel = new Restaurant();
    }
    
    /**
     * API - Charger les avis avec pagination, tri et filtres
     * Route: GET /api/reviews/{restaurant_id}
     */
    public function apiLoadReviews(Request $request): void
    {
        header('Content-Type: application/json');

        $restaurantId = (int)$request->param('id');
        $offset = (int)($_GET['offset'] ?? 0);
        $limit = (int)($_GET['limit'] ?? 5);
        $sort = $_GET['sort'] ?? 'recent';
        $ratingFilter = isset($_GET['rating']) ? (int)$_GET['rating'] : null;

        if (!$restaurantId) {
            echo json_encode(['success' => false, 'error' => 'Restaurant ID requis']);
            return;
        }
        
        try {
            // Construction de la requête
            $params = [':restaurant_id' => $restaurantId];
            $where = ['rev.restaurant_id = :restaurant_id', 'rev.status = :status'];
            $params[':status'] = 'approved';
            
            // Filtre par note
            if ($ratingFilter !== null) {
                if ($ratingFilter === 5) {
                    $where[] = 'rev.note_globale >= 4.5';
                } elseif ($ratingFilter === 4) {
                    $where[] = 'rev.note_globale >= 3.5 AND rev.note_globale < 4.5';
                } elseif ($ratingFilter === 3) {
                    $where[] = 'rev.note_globale >= 2.5 AND rev.note_globale < 3.5';
                } elseif ($ratingFilter === 2) {
                    $where[] = 'rev.note_globale >= 1.5 AND rev.note_globale < 2.5';
                } elseif ($ratingFilter === 1) {
                    $where[] = 'rev.note_globale < 1.5';
                }
            }

            // Recherche dans les avis
            $searchQuery = trim($_GET['q'] ?? '');
            if (!empty($searchQuery) && mb_strlen($searchQuery) >= 2) {
                $where[] = 'rev.message LIKE :search_term';
                $params[':search_term'] = '%' . $searchQuery . '%';
            }

            // Tri
            $orderBy = match($sort) {
                'helpful' => 'rev.votes_utiles DESC, rev.created_at DESC',
                'rating' => 'rev.note_globale DESC, rev.created_at DESC',
                default => 'rev.created_at DESC'
            };
            
            // Compter le total
            $countSql = "SELECT COUNT(*) as total 
                        FROM reviews rev 
                        WHERE " . implode(' AND ', $where);
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $total = (int)$countStmt->fetch(\PDO::FETCH_ASSOC)['total'];
            
            // Récupérer les avis avec badge fidélité
            $sql = "SELECT rev.*,
                           u.prenom as user_prenom,
                           u.nom as user_nom,
                           u.photo_profil as user_photo,
                           u.ville as user_ville,
                           (SELECT COUNT(*) FROM reviews WHERE user_id = rev.user_id AND status = 'approved') as user_total_reviews,
                           ul.badge as user_badge,
                           ul.points as user_points
                    FROM reviews rev
                    LEFT JOIN users u ON u.id = rev.user_id
                    LEFT JOIN user_loyalty ul ON ul.user_id = rev.user_id
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY {$orderBy}
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
            $stmt->execute();
            
            $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Charger toutes les photos en une seule requête (fix N+1)
            $reviewIds = array_column($reviews, 'id');
            $photosByReview = [];
            if (!empty($reviewIds)) {
                $placeholders = implode(',', array_fill(0, count($reviewIds), '?'));
                $photosStmt = $this->db->prepare("
                    SELECT review_id, photo_path, caption
                    FROM review_photos
                    WHERE review_id IN ({$placeholders})
                    ORDER BY display_order
                ");
                $photosStmt->execute($reviewIds);
                foreach ($photosStmt->fetchAll(\PDO::FETCH_ASSOC) as $photo) {
                    $photosByReview[$photo['review_id']][] = $photo;
                }
            }

            // Batch check-in verification (visite confirmée) — single query instead of N+1
            $checkinByReview = [];
            if (!empty($reviewIds)) {
                $cPlaceholders = implode(',', array_fill(0, count($reviewIds), '?'));
                $cStmt = $this->db->prepare("
                    SELECT rev.id as review_id
                    FROM reviews rev
                    INNER JOIN checkins c ON c.user_id = rev.user_id
                        AND c.restaurant_id = rev.restaurant_id
                        AND c.created_at <= DATE_ADD(rev.created_at, INTERVAL 48 HOUR)
                    WHERE rev.id IN ({$cPlaceholders})
                    GROUP BY rev.id
                ");
                $cStmt->execute($reviewIds);
                foreach ($cStmt->fetchAll(\PDO::FETCH_COLUMN) as $rid) {
                    $checkinByReview[$rid] = true;
                }
            }

            // Enrichir chaque avis
            foreach ($reviews as &$review) {
                $review['photos'] = $photosByReview[$review['id']] ?? [];
                $review['has_checkin'] = isset($checkinByReview[$review['id']]);
                $review['date_relative'] = $this->getRelativeTime($review['created_at']);
                $review['note_globale_display'] = number_format($review['note_globale'], 1);
            }
            
            echo json_encode([
                'success' => true,
                'reviews' => $reviews,
                'total' => $total,
                'offset' => $offset,
                'limit' => $limit,
                'hasMore' => ($offset + $limit) < $total
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors du chargement des avis'
            ]);
        }
    }
    
    /**
     * API - Voter pour un avis utile
     * Route: POST /api/reviews/{review_id}/vote
     */
    public function apiVoteHelpful(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Non authentifie']);
            return;
        }

        $reviewId = (int)$request->param('id');
        $userId = (int)$_SESSION['user']['id'];

        // Support reaction types: useful (default), funny, love
        $input = json_decode(file_get_contents('php://input'), true);
        $reactionType = $input['reaction'] ?? 'useful';
        $allowedReactions = ['useful', 'funny', 'love'];
        if (!in_array($reactionType, $allowedReactions, true)) {
            $reactionType = 'useful';
        }

        try {
            // Check if already reacted with this type
            $checkStmt = $this->db->prepare("
                SELECT id FROM review_votes
                WHERE review_id = :rid AND user_id = :uid AND reaction_type = :rtype
            ");
            $checkStmt->execute([':rid' => $reviewId, ':uid' => $userId, ':rtype' => $reactionType]);

            if ($checkStmt->fetch()) {
                // Toggle off: remove the reaction
                $this->db->prepare("
                    DELETE FROM review_votes
                    WHERE review_id = :rid AND user_id = :uid AND reaction_type = :rtype
                ")->execute([':rid' => $reviewId, ':uid' => $userId, ':rtype' => $reactionType]);
                $toggled = false;
            } else {
                // Add reaction
                $this->db->prepare("
                    INSERT INTO review_votes (review_id, user_id, vote, reaction_type, created_at)
                    VALUES (:rid, :uid, 1, :rtype, NOW())
                ")->execute([':rid' => $reviewId, ':uid' => $userId, ':rtype' => $reactionType]);
                $toggled = true;

                // Loyalty points for author (only on add, not remove)
                try {
                    $authorStmt = $this->db->prepare("SELECT user_id FROM reviews WHERE id = :rid");
                    $authorStmt->execute([':rid' => $reviewId]);
                    $authorId = $authorStmt->fetchColumn();
                    if ($authorId && (int)$authorId !== $userId) {
                        $loyaltyService = new \App\Services\LoyaltyService($this->db);
                        $loyaltyService->addPoints((int)$authorId, 'vote_received', $reviewId, 'review');
                    }
                } catch (\Exception $e) {
                    Logger::error("Loyalty vote error: " . $e->getMessage());
                }
            }

            // Update denormalized counters
            $counterMap = ['useful' => 'votes_utiles', 'funny' => 'votes_funny', 'love' => 'votes_love'];
            $column = $counterMap[$reactionType];
            $this->db->prepare("
                UPDATE reviews SET {$column} = (
                    SELECT COUNT(*) FROM review_votes WHERE review_id = :rid AND reaction_type = :rtype
                ) WHERE id = :rid2
            ")->execute([':rid' => $reviewId, ':rtype' => $reactionType, ':rid2' => $reviewId]);

            // Get all counts
            $countStmt = $this->db->prepare("SELECT votes_utiles, votes_funny, votes_love FROM reviews WHERE id = :rid");
            $countStmt->execute([':rid' => $reviewId]);
            $counts = $countStmt->fetch(\PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'toggled' => $toggled,
                'counts' => [
                    'useful' => (int)($counts['votes_utiles'] ?? 0),
                    'funny' => (int)($counts['votes_funny'] ?? 0),
                    'love' => (int)($counts['votes_love'] ?? 0),
                ]
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors du vote']);
        }
    }
    
    /**
     * API - Répondre à un avis (propriétaire uniquement)
     * Route: POST /api/reviews/{review_id}/respond
     */
    public function apiRespondToReview(Request $request): void
    {
        header('Content-Type: application/json');
        
        // Vérifier authentification
        if (!$this->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            return;
        }
        
        $reviewId = (int)$request->param('id');
        $userId = $_SESSION['user']['id'];
        
        // Récupérer le restaurant_id de l'avis
        $reviewStmt = $this->db->prepare("
            SELECT restaurant_id 
            FROM reviews 
            WHERE id = ? AND status = 'approved'
        ");
        $reviewStmt->execute([$reviewId]);
        $review = $reviewStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$review) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Avis introuvable']);
            return;
        }
        
        // Vérifier que l'utilisateur est le propriétaire du restaurant
        $ownerStmt = $this->db->prepare("
            SELECT owner_id      
            FROM restaurants 
            WHERE id = ?
        ");
        $ownerStmt->execute([$review['restaurant_id']]);
        $restaurant = $ownerStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$restaurant || (int)$restaurant['owner_id'] !== (int)$userId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Non autorisé. Vous devez être le propriétaire du restaurant.']);
            return;
        }
        
        // Récupérer la réponse depuis le body JSON
        $input = json_decode(file_get_contents('php://input'), true);
        $response = isset($input['response']) ? trim($input['response']) : '';
        
        // Validation
        if (empty($response) || strlen($response) < 20) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La réponse doit contenir au moins 20 caractères']);
            return;
        }
        
        if (strlen($response) > 2000) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La réponse ne peut pas dépasser 2000 caractères']);
            return;
        }
        
        try {
            // Enregistrer la réponse
            $updateStmt = $this->db->prepare("
                UPDATE reviews 
                SET owner_response = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$response, $reviewId]);

            // Notifier l'auteur de l'avis
            $authorStmt = $this->db->prepare("SELECT user_id FROM reviews WHERE id = ?");
            $authorStmt->execute([$reviewId]);
            $reviewAuthor = $authorStmt->fetch(\PDO::FETCH_ASSOC);
            if ($reviewAuthor) {
                $nameStmt = $this->db->prepare("SELECT nom FROM restaurants WHERE id = ?");
                $nameStmt->execute([$review['restaurant_id']]);
                $restoName = $nameStmt->fetch(\PDO::FETCH_ASSOC);
                $notifService = new NotificationService($this->db);
                $notifService->notifyOwnerResponse(
                    (int)$reviewAuthor['user_id'],
                    (int)$review['restaurant_id'],
                    $restoName['nom'] ?? 'Restaurant'
                );
            }

            echo json_encode([
                'success' => true,
                'message' => 'Réponse publiée avec succès'
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors de l\'enregistrement de la réponse'
            ]);
        }
    }
    
    /**
     * Calculer le temps relatif (Il y a X jours)
     */
    private function getRelativeTime(string $datetime): string
    {
        $now = new \DateTime();
        $past = new \DateTime($datetime);
        $diff = $now->diff($past);
        
        if ($diff->y > 0) return $diff->y === 1 ? 'Il y a 1 an' : "Il y a {$diff->y} ans";
        if ($diff->m > 0) return $diff->m === 1 ? 'Il y a 1 mois' : "Il y a {$diff->m} mois";
        if ($diff->d > 0) return $diff->d === 1 ? 'Il y a 1 jour' : "Il y a {$diff->d} jours";
        if ($diff->h > 0) return $diff->h === 1 ? 'Il y a 1 heure' : "Il y a {$diff->h} heures";
        if ($diff->i > 0) return $diff->i === 1 ? 'Il y a 1 minute' : "Il y a {$diff->i} minutes";
        return 'À l\'instant';
    }
    
    /**
     * Affiche le formulaire pour laisser un avis
     */
    public function create(Request $request): void
    {
        $restaurantId = $request->param('id');
        
        if (!$this->isAuthenticated()) {
            $this->redirect('/login?redirect=/restaurant/' . $restaurantId . '/review');
            return;
        }
        
        $restaurant = $this->restaurantModel->find((int)$restaurantId);
        
        if (!$restaurant) {
            $this->notFound('Restaurant non trouvé');
            return;
        }
        
        $existingReview = $this->reviewModel->getUserReviewForRestaurant(
            $_SESSION['user']['id'], 
            (int)$restaurantId
        );
        
        if ($existingReview) {
            $_SESSION['flash_error'] = 'Vous avez déjà laissé un avis pour ce restaurant';
            $this->redirect('/restaurant/' . $restaurantId);
            return;
        }
        
        $data = [
            'title' => 'Laisser un avis - ' . $restaurant['nom'],
            'restaurant' => $restaurant
        ];
        
        $this->render('reviews/create', $data);
    }
    
    /**
     * Enregistre un nouvel avis
     */
    public function store(Request $request): void
    {
        header('Content-Type: application/json');

        if (!verify_csrf()) {
            $this->json(['success' => false, 'message' => 'Token CSRF invalide'], 403);
            return;
        }

        $restaurantId = $request->param('id');

        if (!$this->isAuthenticated()) {
            $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
            return;
        }

        // Rate limiting : 3 avis par jour
        if (!RateLimiter::attempt('review_create', 3, 86400)) {
            $this->json(['success' => false, 'message' => 'Vous avez atteint la limite d\'avis pour aujourd\'hui. Réessayez demain.'], 429);
            return;
        }
        
        $noteGlobale = $request->post('note_globale');
        $message = $request->post('message');
        $visitMonth = $request->post('visit_month');
        $visitYear = $request->post('visit_year');
        $tripType = $request->post('trip_type');
        
   if (!$noteGlobale || $noteGlobale < 0.5 || $noteGlobale > 5) {
    $this->json(['success' => false, 'message' => 'Note globale invalide (0.5-5)'], 400);
    return;
}
        
        if (!$message || strlen(trim($message)) < 10) {
            $this->json(['success' => false, 'message' => 'Le message doit faire au moins 10 caractères'], 400);
            return;
        }
        
        if (!$visitMonth || !$visitYear || !$tripType) {
            $this->json(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
            return;
        }
        

// ✅ RECALCUL ET VALIDATION NOTES SUR 5
$noteNourriture = $request->post('note_nourriture') ? (float)$request->post('note_nourriture') : 0;
$noteService = $request->post('note_service') ? (float)$request->post('note_service') : 0;
$noteAmbiance = $request->post('note_ambiance') ? (float)$request->post('note_ambiance') : 0;
$notePrix = $request->post('note_prix') ? (float)$request->post('note_prix') : 0;

// Recalculer note globale côté serveur (sécurité)
$validNotes = array_filter([$noteNourriture, $noteService, $noteAmbiance, $notePrix]);
$noteGlobaleCalculee = count($validNotes) > 0 
    ? array_sum($validNotes) / count($validNotes) 
    : 0;

// Arrondir à 0.5 près et limiter entre 0.5 et 5
$noteGlobale = max(0.5, min(5, round($noteGlobaleCalculee * 2) / 2));
$noteNourriture = $noteNourriture > 0 ? max(0.5, min(5, round($noteNourriture * 2) / 2)) : null;
$noteService = $noteService > 0 ? max(0.5, min(5, round($noteService * 2) / 2)) : null;
$noteAmbiance = $noteAmbiance > 0 ? max(0.5, min(5, round($noteAmbiance * 2) / 2)) : null;
$notePrix = $notePrix > 0 ? max(0.5, min(5, round($notePrix * 2) / 2)) : null;


        // Pros/cons (optionnels, max 500 chars)
        $pros = $request->post('pros') ? mb_substr(trim($request->post('pros')), 0, 500) : null;
        $cons = $request->post('cons') ? mb_substr(trim($request->post('cons')), 0, 500) : null;

        $reviewData = [
            'restaurant_id' => (int)$restaurantId,
            'user_id' => $_SESSION['user']['id'],
            'title' => $request->post('title'),
            'message' => $message,
            'pros' => $pros,
            'cons' => $cons,
'note_globale' => $noteGlobale,  // ← Valeur recalculée et validée
'note_nourriture' => $noteNourriture,
'note_service' => $noteService,
'note_ambiance' => $noteAmbiance,
'note_prix' => $notePrix,
            'visit_month' => $visitMonth,
            'visit_year' => (int)$visitYear,
            'trip_type' => $tripType,
            'status' => 'pending'
        ];
        
        // 🤖 ANALYSE IA ANTI-SPAM
        $aiEnabled = true;
        
        // Vérifier que les fichiers IA existent
        $spamDetectorPath = __DIR__ . '/../Services/SpamDetector.php';
        $helperPath = __DIR__ . '/../Helpers/ReviewModerationHelper.php';
        
        if (!file_exists($spamDetectorPath) || !file_exists($helperPath)) {
            $aiEnabled = false;
            Logger::debug("AI files not found - falling back to manual moderation");
        }
        
        if ($aiEnabled) {
            try {
                // Récupérer nom utilisateur pour analyse
                $stmt = $this->db->prepare("SELECT prenom, nom FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user']['id']]);
                $user = $stmt->fetch(\PDO::FETCH_ASSOC);
                
                $authorName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
                $reviewData['author_name'] = $authorName ?: 'Utilisateur';
                
                // Analyser et enrichir avec métadonnées IA
                $reviewData = \App\Helpers\ReviewModerationHelper::autoModerate($reviewData, $this->db);
                
            } catch (\Exception $e) {
                // Si erreur IA, continuer avec modération manuelle
                Logger::error("AI Moderation error: " . $e->getMessage());
                $aiEnabled = false;
            }
        }
        
        // Fallback : valeurs par défaut si IA désactivée
        if (!$aiEnabled) {
            $reviewData['spam_score'] = 100;
            $reviewData['moderated_by'] = 'manual';
            $reviewData['spam_details'] = null;
            $reviewData['moderated_at'] = null;
            $reviewData['ai_rejected'] = 0;
            $reviewData['author_name'] = $reviewData['author_name'] ?? 'Utilisateur';
        }
 


        
// Créer l'avis dans une transaction
$this->db->beginTransaction();

try {
    $reviewId = $this->reviewModel->createReview($reviewData);

    if (!$reviewId) {
        $this->db->rollBack();
        Logger::debug("Review creation failed for user " . $_SESSION['user']['id']);
        $this->json([
            'success' => false,
            'message' => 'Erreur lors de l\'enregistrement de l\'avis'
        ], 500);
        return;
    }

    // Upload photos
    $uploadedPhotos = 0;

    if (isset($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
        $validFiles = false;
        foreach ($_FILES['photos']['name'] as $fileName) {
            if (!empty($fileName)) {
                $validFiles = true;
                break;
            }
        }

        if ($validFiles) {
            $uploadedPhotos = $this->handlePhotoUploads($reviewId, $_FILES['photos']);
        }
    }

    // Recalculer la note du restaurant
    $this->recalculateRestaurantRating((int)$restaurantId);

    // Save review tags
    if (!empty($_POST['tags']) && is_array($_POST['tags'])) {
        $validTags = ['romantique','familial','business','terrasse','vue','calme','anime','bon_rapport','grandes_portions','service_rapide','livraison'];
        $tagStmt = $this->db->prepare("INSERT INTO review_tags (review_id, tag) VALUES (:rid, :tag)");
        $ctxStmt = $this->db->prepare("
            INSERT INTO restaurant_context_tags (restaurant_id, tag, vote_count) VALUES (:rid, :tag, 1)
            ON DUPLICATE KEY UPDATE vote_count = vote_count + 1
        ");
        foreach ($_POST['tags'] as $tag) {
            $tag = trim($tag);
            if (in_array($tag, $validTags)) {
                $tagStmt->execute([':rid' => $reviewId, ':tag' => $tag]);
                $ctxStmt->execute([':rid' => (int)$restaurantId, ':tag' => $tag]);
            }
        }
    }

    $this->db->commit();
} catch (\Exception $e) {
    $this->db->rollBack();
    Logger::error("Review transaction error: " . $e->getMessage());
    $this->json([
        'success' => false,
        'message' => 'Erreur lors de l\'enregistrement de l\'avis'
    ], 500);
    return;
}

// Post-commit: loyalty & notifications (non-critical, outside transaction)
try {
    $loyaltyService = new LoyaltyService($this->db);
    $loyaltyResult = $loyaltyService->addPointsForReview(
        $_SESSION['user']['id'],
        $reviewId,
        $uploadedPhotos > 0,
        strlen($message)
    );

    if ($loyaltyResult['total_points_earned'] > 0) {
        $_SESSION['loyalty_notification'] = [
            'points' => $loyaltyResult['total_points_earned'],
            'new_badge' => $loyaltyResult['new_badge']
        ];
    }
    if (!empty($loyaltyResult['new_badge'])) {
        $notifS = new NotificationService($this->db);
        $notifS->notifyBadgeEarned((int)$_SESSION['user']['id'], $loyaltyResult['new_badge']);
    }
} catch (\Exception $e) {
    Logger::error("Loyalty error: " . $e->getMessage());
}

// Compléter parrainage si premier avis
try {
    ReferralController::completeReferral($this->db, (int)$_SESSION['user']['id']);
} catch (\Exception $e) {}

try {
    $notifService = new NotificationService($this->db);
    if (isset($reviewData['status']) && $reviewData['status'] === 'approved') {
        $authorName = $reviewData['author_name'] ?? 'Quelqu\'un';
        $notifService->notifyNewReview((int)$restaurantId, $authorName, (float)$noteGlobale);
    }
} catch (\Exception $e) {
    // Notification non critique
}

// Log dans le fil d'actualité
try {
    $feedService = new \App\Services\ActivityFeedService($this->db);
    $feedService->log(
        (int)$_SESSION['user']['id'],
        'review',
        'restaurant',
        (int)$restaurantId,
        ['restaurant_name' => $restaurantName ?? '', 'rating' => $noteGlobale, 'has_photo' => $uploadedPhotos > 0]
    );
} catch (\Exception $e) {
    // Feed non critique
}

// Auto-analyze review for concierge insights (non-critical)
try {
    $analyzer = new \App\Services\ReviewAnalyzerService($this->db);
    $analyzer->analyzeReview($reviewId);
} catch (\Exception $e) {
    // Analysis non critique
}

// Message adapté selon décision IA
$successMessage = 'Votre avis a été enregistré et sera publié après modération';

if (isset($reviewData['status'])) {
    if ($reviewData['status'] === 'approved') {
        $successMessage = 'Votre avis a été publié avec succès !';
    } elseif ($reviewData['status'] === 'rejected') {
        $successMessage = 'Votre avis a été rejeté automatiquement car il ne respecte pas nos critères de qualité.';
    }
}

$this->json([
    'success' => true,
    'message' => $successMessage,
    'review_id' => $reviewId,
    'photos_uploaded' => $uploadedPhotos
]);
    }

    /**
     * Affiche le formulaire d'édition d'un avis
     */
    public function edit(Request $request): void
    {
        if (!$this->isAuthenticated()) {
            $this->redirect('/login');
            return;
        }

        $reviewId = (int)$request->param('id');
        $userId = $_SESSION['user']['id'];

        // Récupérer l'avis
        $stmt = $this->db->prepare("
            SELECT rev.*, r.nom as restaurant_nom, r.id as restaurant_id
            FROM reviews rev
            INNER JOIN restaurants r ON r.id = rev.restaurant_id
            WHERE rev.id = ? AND rev.user_id = ?
        ");
        $stmt->execute([$reviewId, $userId]);
        $review = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$review) {
            $_SESSION['flash_error'] = 'Avis introuvable ou non autorisé';
            $this->redirect('/profil');
            return;
        }

        $restaurant = $this->restaurantModel->find($review['restaurant_id']);

        $this->render('reviews/edit', [
            'title' => 'Modifier mon avis - ' . $review['restaurant_nom'],
            'restaurant' => $restaurant,
            'review' => $review
        ]);
    }

    /**
     * API - Met à jour un avis existant
     * Route: POST /api/reviews/{review_id}/update
     */
    public function apiUpdateReview(Request $request): void
    {
        header('Content-Type: application/json');

        if (!$this->isAuthenticated()) {
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            return;
        }

        $reviewId = (int)$request->param('id');
        $userId = $_SESSION['user']['id'];

        // Vérifier que l'avis appartient à l'utilisateur
        $stmt = $this->db->prepare("SELECT id, restaurant_id, edit_count FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$reviewId, $userId]);
        $review = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$review) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Avis introuvable ou non autorisé']);
            return;
        }

        // Limiter les éditions (max 3)
        if ((int)($review['edit_count'] ?? 0) >= 3) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombre maximum de modifications atteint (3)']);
            return;
        }

        $rawBody = file_get_contents('php://input');
        $input = json_decode($rawBody, true);
        if (!is_array($input) || empty($input)) {
            $input = $_POST;
        }

        $title = trim($input['title'] ?? '');
        $message = trim($input['message'] ?? '');
        $noteGlobale = (float)($input['note_globale'] ?? 0);
        $noteNourriture = !empty($input['note_nourriture']) ? (float)$input['note_nourriture'] : null;
        $noteService = !empty($input['note_service']) ? (float)$input['note_service'] : null;
        $noteAmbiance = !empty($input['note_ambiance']) ? (float)$input['note_ambiance'] : null;
        $notePrix = !empty($input['note_prix']) ? (float)$input['note_prix'] : null;

        // Validation
        if (empty($message) || strlen($message) < 10) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Le message doit faire au moins 10 caractères']);
            return;
        }

        // Recalculer note globale
        $validNotes = array_filter([$noteNourriture, $noteService, $noteAmbiance, $notePrix]);
        if (count($validNotes) > 0) {
            $noteGlobale = array_sum($validNotes) / count($validNotes);
        }
        $noteGlobale = max(0.5, min(5, round($noteGlobale * 2) / 2));

        try {
            $updateStmt = $this->db->prepare("
                UPDATE reviews SET
                    title = ?,
                    message = ?,
                    note_globale = ?,
                    note_nourriture = ?,
                    note_service = ?,
                    note_ambiance = ?,
                    note_prix = ?,
                    edited_at = NOW(),
                    edit_count = edit_count + 1,
                    status = 'pending'
                WHERE id = ? AND user_id = ?
            ");
            $updateStmt->execute([
                $title, $message, $noteGlobale,
                $noteNourriture, $noteService, $noteAmbiance, $notePrix,
                $reviewId, $userId
            ]);

            // Recalculer la note moyenne du restaurant
            $this->recalculateRestaurantRating($review['restaurant_id']);

            echo json_encode([
                'success' => true,
                'message' => 'Votre avis a été modifié et sera re-vérifié par notre équipe.',
                'redirect' => '/restaurant/' . $review['restaurant_id']
            ]);
        } catch (\Exception $e) {
            Logger::error('Erreur mise à jour avis', ['error' => $e->getMessage()]);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * Recalcule la note moyenne d'un restaurant
     */
    private function recalculateRestaurantRating(int $restaurantId): void
    {
        $stmt = $this->db->prepare("
            UPDATE restaurants SET
                note_moyenne = (SELECT AVG(note_globale) FROM reviews WHERE restaurant_id = ? AND status = 'approved'),
                nb_avis = (SELECT COUNT(*) FROM reviews WHERE restaurant_id = ? AND status = 'approved')
            WHERE id = ?
        ");
        $stmt->execute([$restaurantId, $restaurantId, $restaurantId]);
    }


    /**
 * API - Signaler un avis
 * Route: POST /api/reviews/{review_id}/report
 */
public function apiReportReview(Request $request): void
{
    header('Content-Type: application/json');
    
    // Vérifier authentification
    if (!$this->isAuthenticated()) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'error' => 'Vous devez être connecté pour signaler un avis'
        ]);
        return;
    }
    
    $reviewId = (int)$request->param('id');
    $userId = $_SESSION['user']['id'];
    
    // Récupérer les données JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Données JSON invalides'
        ]);
        return;
    }
    
    $reason = isset($input['reason']) ? trim($input['reason']) : '';
    $details = isset($input['details']) ? trim($input['details']) : '';
    
    // Validation
    if (empty($reason)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Veuillez sélectionner une raison pour le signalement'
        ]);
        return;
    }
    
    $validReasons = ['spam', 'offensive', 'fake', 'harassment', 'personal', 'copyright', 'other'];
    if (!in_array($reason, $validReasons)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Raison de signalement invalide'
        ]);
        return;
    }
    
    try {
        // Vérifier que l'avis existe
        $reviewStmt = $this->db->prepare("
            SELECT id, restaurant_id, user_id 
            FROM reviews 
            WHERE id = ?
        ");
        $reviewStmt->execute([$reviewId]);
        $review = $reviewStmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$review) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Avis introuvable'
            ]);
            return;
        }
        
        // Empêcher de signaler son propre avis
        if ((int)$review['user_id'] === (int)$userId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Vous ne pouvez pas signaler votre propre avis'
            ]);
            return;
        }
        
        // Vérifier si l'utilisateur a déjà signalé cet avis
        $checkStmt = $this->db->prepare("
            SELECT id 
            FROM review_reports 
            WHERE review_id = ? AND user_id = ?
        ");
        $checkStmt->execute([$reviewId, $userId]);
        
        if ($checkStmt->fetch()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Vous avez déjà signalé cet avis'
            ]);
            return;
        }
        
        // Enregistrer le signalement
        $insertStmt = $this->db->prepare("
            INSERT INTO review_reports 
            (review_id, user_id, reason, details, status, created_at) 
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        
        $insertStmt->execute([
            $reviewId,
            $userId,
            $reason,
            $details ?: null
        ]);
        
        $reportId = $this->db->lastInsertId();
        
        // TODO: Envoyer une notification aux admins
        // $this->notifyAdmins($reportId, $reviewId, $reason);
        
        echo json_encode([
            'success' => true,
            'message' => 'Signalement envoyé avec succès. Notre équipe va l\'examiner.',
            'report_id' => $reportId
        ]);
        
    } catch (\PDOException $e) {
        Logger::error("Erreur SQL signalement: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l\'enregistrement du signalement'
        ]);
    } catch (\Exception $e) {
        Logger::error("Erreur signalement: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Une erreur est survenue. Veuillez réessayer.'
        ]);
    }
}

/**
 * API - Response templates for owners (F26)
 * GET /api/response-templates?category=positive|neutral|negative
 */
public function apiResponseTemplates(Request $request): void
{
    header('Content-Type: application/json');

    $category = trim($request->query('category', ''));
    $validCategories = ['positive', 'neutral', 'negative'];

    if ($category !== '' && !in_array($category, $validCategories, true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Catégorie invalide']);
        return;
    }

    if ($category !== '') {
        $stmt = $this->db->prepare("
            SELECT id, category, template_fr
            FROM review_response_templates
            WHERE category = :cat
            ORDER BY sort_order ASC
        ");
        $stmt->execute([':cat' => $category]);
    } else {
        $stmt = $this->db->query("
            SELECT id, category, template_fr
            FROM review_response_templates
            ORDER BY category, sort_order ASC
        ");
    }

    $templates = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'templates' => $templates]);
}

/**
 * Upload et conversion photos en WebP
 * Chemin: public/uploads/reviews/{year}/{month}/{review_id}/
 */
private function handlePhotoUploads(int $reviewId, array $files): int
{
    $this->logDebug("=== DÉBUT UPLOAD PHOTOS ===");
    $this->logDebug("Review ID: $reviewId");
    $this->logDebug("Nombre de fichiers détectés: " . count($files['name']));
    $this->logDebug("Files array: " . print_r($files, true));
    
    $uploadCount = 0;
    $maxPhotos = 5;
    $maxSize = 5 * 1024 * 1024; // 5 MB
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    
    // Créer chemin upload
    $year = date('Y');
    $month = date('m');
    $uploadDir = __DIR__ . '/../../public/uploads/reviews/' . $year . '/' . $month . '/' . $reviewId;
    
    $this->logDebug("Upload dir: $uploadDir");
    
    // Créer dossiers si inexistants
    if (!is_dir($uploadDir)) {
        $created = mkdir($uploadDir, 0755, true);
        $this->logDebug("Création dossier: " . ($created ? 'OK' : 'ERREUR'));
    } else {
        $this->logDebug("Dossier existe déjà");
    }
    
    // Traiter chaque photo
    $photoCount = count($files['name']);
    $this->logDebug("Nombre de photos à traiter: $photoCount");
    
    for ($i = 0; $i < min($photoCount, $maxPhotos); $i++) {
        $this->logDebug("--- Traitement photo $i ---");
            // ✅ AJOUTER : Ignorer fichiers vides
    if (empty($files['name'][$i]) || $files['size'][$i] == 0) {
        $this->logDebug("⏭️ Photo $i ignorée (vide)");
        continue;
    }
        // Vérifier erreurs upload
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $this->logDebug("❌ Erreur upload photo $i: " . $files['error'][$i]);
            continue;
        }
        
        $this->logDebug("✅ Pas d'erreur upload");
        
        // Vérifier type MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $files['tmp_name'][$i]);
        finfo_close($finfo);

        $this->logDebug("MIME type: $mimeType");

        if (!in_array($mimeType, $allowedTypes)) {
            $this->logDebug("❌ Type MIME non autorisé: $mimeType");
            continue;
        }

        // Double vérification : getimagesize confirme que c'est une vraie image
        $imageInfo = @getimagesize($files['tmp_name'][$i]);
        if ($imageInfo === false) {
            $this->logDebug("❌ getimagesize a échoué — fichier non valide comme image");
            continue;
        }

        $this->logDebug("✅ Type MIME OK + getimagesize OK");
        
        // Vérifier taille
        $this->logDebug("Taille fichier: " . $files['size'][$i] . " bytes");
        
        if ($files['size'][$i] > $maxSize) {
            $this->logDebug("❌ Fichier trop gros");
            continue;
        }
        
        $this->logDebug("✅ Taille OK");
        
        // Générer nom fichier unique
        $fileName = uniqid('photo_', true) . '.webp';
        $filePath = $uploadDir . '/' . $fileName;
        $relativePath = 'uploads/reviews/' . $year . '/' . $month . '/' . $reviewId . '/' . $fileName;
        
        $this->logDebug("Nom fichier: $fileName");
        $this->logDebug("Chemin complet: $filePath");
        $this->logDebug("Chemin relatif: $relativePath");
        
        // Convertir en WebP
        $this->logDebug("Début conversion WebP...");
        $converted = $this->convertToWebP($files['tmp_name'][$i], $filePath, $mimeType);
        
        if ($converted) {
            $this->logDebug("✅ Conversion WebP réussie");
            
            // Insérer en BDD
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO review_photos (review_id, photo_path, display_order, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $result = $stmt->execute([$reviewId, $relativePath, $i]);
                
                if ($result) {
                    $uploadCount++;
                    $photoId = (int)$this->db->lastInsertId();
                    $this->logDebug("✅ Photo $i insérée en BDD (total: $uploadCount)");

                    // Catégorisation IA (async, non-bloquant)
                    try {
                        $aiService = new \App\Services\PhotoAIService($this->db);
                        if ($aiService->canMakeApiCall()) {
                            $aiService->categorizeReviewPhoto($photoId, $relativePath);
                        }
                    } catch (\Exception $aiErr) {
                        // Silently fail - AI categorization is optional
                    }
                } else {
                    $this->logDebug("❌ Échec insertion BDD");
                }
            } catch (\Exception $e) {
                $this->logDebug("❌ Erreur BDD: " . $e->getMessage());
                @unlink($filePath);
            }
        } else {
            $this->logDebug("❌ Échec conversion WebP");
        }
    }
    
    $this->logDebug("=== FIN UPLOAD - Total uploadé: $uploadCount ===");
    $this->logDebug("");
    
    return $uploadCount;
}

/**
 * Convertit image en WebP
 */
private function convertToWebP(string $sourcePath, string $destPath, string $mimeType): bool
{
    $this->logDebug("convertToWebP appelé - Source: $sourcePath");
    $this->logDebug("MIME: $mimeType, Dest: $destPath");

    try {
        // Créer image source
        $image = match($mimeType) {
            'image/jpeg', 'image/jpg' => @\imagecreatefromjpeg($sourcePath),
            'image/png' => @\imagecreatefrompng($sourcePath),
            default => false
        };
                $this->logDebug("Image créée: " . ($image ? 'OUI' : 'NON'));

        if ($image === false) {
            Logger::debug("❌ Impossible de créer l'image depuis: $sourcePath");
            $this->logDebug("❌ Impossible de créer l'image");
            return false;
        }
        
        $this->logDebug("✅ Image créée avec succès");
        
        // Convertir en WebP (qualité 85)
        $success = \imagewebp($image, $destPath, 85);
        
        // Libérer mémoire
        \imagedestroy($image);
        
        if ($success) {
            chmod($destPath, 0644);
            $this->logDebug("✅ Conversion WebP réussie");
            return true;
        }
        
        $this->logDebug("❌ imagewebp() a échoué");
        return false;
        
    } catch (\Exception $e) {
        Logger::error("Erreur conversion WebP: " . $e->getMessage());
        $this->logDebug("❌ Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Log dans fichier personnalisé
 */
private function logDebug(string $message): void
{
    $logFile = __DIR__ . '/../../storage/logs/photo-upload.log';
    $logDir = dirname($logFile);
    
    // Créer dossier si inexistant
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

}
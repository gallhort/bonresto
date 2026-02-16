<?php
/**
 * VERSION DEBUG de la méthode store()
 * 
 * Remplace temporairement la méthode store() dans ReviewController.php
 * Cette version LOG chaque étape pour identifier où ça bloque
 */

public function store(Request $request): void
{
    error_log("=== DEBUT store() ===");
    
    $restaurantId = $request->param('id');
    error_log("Restaurant ID: " . $restaurantId);
    
    if (!$this->isAuthenticated()) {
        error_log("ERROR: Non authentifié");
        $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        return;
    }
    error_log("User authentifié: " . $_SESSION['user']['id']);
    
    $noteGlobale = $request->post('note_globale');
    $message = $request->post('message');
    $visitMonth = $request->post('visit_month');
    $visitYear = $request->post('visit_year');
    $tripType = $request->post('trip_type');
    
    error_log("Données reçues - Note: {$noteGlobale}, Message length: " . strlen($message));
    
    // Validations
    if (!$noteGlobale || $noteGlobale < 1 || $noteGlobale > 10) {
        error_log("ERROR: Note invalide");
        $this->json(['success' => false, 'message' => 'Note globale invalide (1-10)'], 400);
        return;
    }
    
    if (!$message || strlen(trim($message)) < 10) {
        error_log("ERROR: Message trop court");
        $this->json(['success' => false, 'message' => 'Le message doit faire au moins 10 caractères'], 400);
        return;
    }
    
    if (!$visitMonth || !$visitYear || !$tripType) {
        error_log("ERROR: Champs manquants");
        $this->json(['success' => false, 'message' => 'Tous les champs sont requis'], 400);
        return;
    }
    
    error_log("Validations passées");
    
    // Construction reviewData
    $reviewData = [
        'restaurant_id' => (int)$restaurantId,
        'user_id' => $_SESSION['user']['id'],
        'title' => $request->post('title'),
        'message' => $message,
        'note_globale' => (float)$noteGlobale,
        'note_nourriture' => $request->post('note_nourriture') ? (float)$request->post('note_nourriture') : null,
        'note_service' => $request->post('note_service') ? (float)$request->post('note_service') : null,
        'note_ambiance' => $request->post('note_ambiance') ? (float)$request->post('note_ambiance') : null,
        'note_prix' => $request->post('note_prix') ? (float)$request->post('note_prix') : null,
        'visit_month' => $visitMonth,
        'visit_year' => (int)$visitYear,
        'trip_type' => $tripType,
        'status' => 'pending'
    ];
    
    error_log("reviewData construit");
    
    // 🤖 ANALYSE IA - VERSION SIMPLIFIÉE POUR DEBUG
    $aiEnabled = false; // DÉSACTIVÉ temporairement
    
    if ($aiEnabled) {
        error_log("IA: Tentative analyse...");
        try {
            $stmt = $this->db->prepare("SELECT prenom, nom FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user']['id']]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $authorName = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
            $reviewData['author_name'] = $authorName ?: 'Utilisateur';
            
            error_log("IA: Nom auteur récupéré: " . $reviewData['author_name']);
            
            if (class_exists('\App\Helpers\ReviewModerationHelper')) {
                error_log("IA: Classe Helper trouvée");
                $reviewData = \App\Helpers\ReviewModerationHelper::autoModerate($reviewData, $this->db);
                error_log("IA: Analyse terminée - Score: " . ($reviewData['spam_score'] ?? 'N/A'));
            } else {
                error_log("IA: Classe Helper NON trouvée");
            }
            
        } catch (\Exception $e) {
            error_log("IA ERROR: " . $e->getMessage());
            error_log("IA TRACE: " . $e->getTraceAsString());
        }
    } else {
        error_log("IA: Désactivée (mode debug)");
    }
    
    // Valeurs par défaut IA
    if (!isset($reviewData['spam_score'])) {
        $reviewData['spam_score'] = 100;
        $reviewData['moderated_by'] = 'manual';
        $reviewData['spam_details'] = null;
        $reviewData['moderated_at'] = null;
        $reviewData['ai_rejected'] = 0;
        $reviewData['author_name'] = $reviewData['author_name'] ?? 'Utilisateur';
    }
    
    error_log("Valeurs IA définies - spam_score: " . $reviewData['spam_score']);
    
    // Insertion BDD
    try {
        error_log("Tentative insertion BDD...");
        error_log("reviewData complet: " . json_encode($reviewData));
        
        $reviewId = $this->reviewModel->createReview($reviewData);
        error_log("createReview() retourné: " . ($reviewId ? $reviewId : 'FALSE'));
        
        if ($reviewId) {
            error_log("SUCCESS: Avis créé avec ID = {$reviewId}");
            
            $successMessage = 'Votre avis a été enregistré et sera publié après modération';
            
            if (isset($reviewData['status'])) {
                if ($reviewData['status'] === 'approved') {
                    $successMessage = 'Votre avis a été publié avec succès !';
                } elseif ($reviewData['status'] === 'rejected') {
                    $successMessage = 'Votre avis a été rejeté automatiquement.';
                }
            }
            
            error_log("Message retour: " . $successMessage);
            
            $this->json([
                'success' => true,
                'message' => $successMessage,
                'redirect' => '/restaurant/' . $restaurantId
            ]);
        } else {
            error_log("ERROR: createReview() a retourné FALSE");
            error_log("PDO errorInfo: " . print_r($this->db->errorInfo(), true));
            
            $this->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de l\'avis'
            ], 500);
        }
        
    } catch (\Exception $e) {
        error_log("EXCEPTION lors de createReview: " . $e->getMessage());
        error_log("TRACE: " . $e->getTraceAsString());
        
        $this->json([
            'success' => false,
            'message' => 'Erreur technique: ' . $e->getMessage()
        ], 500);
    }
    
    error_log("=== FIN store() ===");
}

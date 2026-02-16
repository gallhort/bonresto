<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Restaurant;

/**
 * Controller Admin pour la gestion des restaurants
 */
class RestaurantController extends Controller
{
    private Restaurant $restaurantModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->restaurantModel = new Restaurant();
        
        // Vérifier que l'utilisateur est admin
        if (!$this->isAdmin()) {
            header('Location: /');
            exit;
        }
    }
    
    /**
     * Liste des restaurants en attente de validation
     */
    public function pending(Request $request): void
    {
        $restaurants = $this->restaurantModel->where('status', 'pending');
        
        $data = [
            'title' => 'Restaurants en attente - Admin',
            'restaurants' => $restaurants
        ];
        
        $this->render('admin/restaurants/pending', $data);
    }
    
    /**
     * Affiche les détails d'un restaurant en attente
     */
    public function view(Request $request): void
    {
        $id = $request->param('id');
        $restaurant = $this->restaurantModel->find((int)$id);
        
        if (!$restaurant) {
            $this->notFound('Restaurant non trouvé');
            return;
        }
        
        $data = [
            'title' => 'Modération - ' . $restaurant['nom'],
            'restaurant' => $restaurant
        ];
        
        $this->render('admin/restaurants/view', $data);
    }
    
    /**
     * Valide un restaurant
     */
    public function validate(Request $request): void
    {
        if (!\verify_csrf()) {
        $_SESSION['flash_error'] = 'Token CSRF invalide';
        $this->redirect('/admin/restaurants/pending');
        return;
    }
        $id = $request->param('id');
        
        $updated = $this->restaurantModel->update((int)$id, [
            'status' => 'validated',
            'validated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($updated) {
            $this->logRestaurantModeration('approve_restaurant', (int)$id);
            $_SESSION['flash_success'] = 'Restaurant validé avec succès';
            $this->redirect('/admin/restaurants/pending');
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la validation';
            $this->redirect('/admin/restaurants/' . $id . '/view');
        }
    }

    /**
     * Rejette un restaurant
     */
    public function reject(Request $request): void
    {
        if (!\verify_csrf()) {
            $_SESSION['flash_error'] = 'Token CSRF invalide';
            $this->redirect('/admin/restaurants/pending');
            return;
        }
        $id = $request->param('id');

        $updated = $this->restaurantModel->update((int)$id, [
            'status' => 'rejected'
        ]);

        if ($updated) {
            $this->logRestaurantModeration('reject_restaurant', (int)$id);
            $_SESSION['flash_success'] = 'Restaurant rejeté';
            $this->redirect('/admin/restaurants/pending');
        } else {
            $_SESSION['flash_error'] = 'Erreur lors du rejet';
            $this->redirect('/admin/restaurants/' . $id . '/view');
        }
    }

    private function logRestaurantModeration(string $action, int $targetId): void
    {
        try {
            $adminId = $_SESSION['user']['id'] ?? 0;
            $stmt = $this->db->prepare("
                INSERT INTO moderation_log (admin_id, action, target_type, target_id, created_at)
                VALUES (?, ?, 'restaurant', ?, NOW())
            ");
            $stmt->execute([$adminId, $action, $targetId]);
        } catch (\Exception $e) {
            // Table might not exist yet
        }
    }
    
    /**
     * Liste des restaurants validés
     */
    public function validated(Request $request): void
    {
        $restaurants = $this->restaurantModel->where('status', 'validated');
        
        $data = [
            'title' => 'Restaurants validés - Admin',
            'restaurants' => $restaurants
        ];
        
        $this->render('admin/restaurants/validated', $data);
    }
    
    /**
     * Formulaire d'édition d'un restaurant
     */
    public function edit(Request $request): void
    {
        $id = $request->param('id');
        $restaurant = $this->restaurantModel->find((int)$id);
        
        if (!$restaurant) {
            $this->notFound('Restaurant non trouvé');
            return;
        }
        
        $data = [
            'title' => 'Modifier - ' . $restaurant['nom'],
            'restaurant' => $restaurant
        ];
        
        $this->render('admin/restaurants/edit', $data);
    }
    
    /**
     * Met à jour un restaurant
     */
    public function update(Request $request): void
    {
            if (!\verify_csrf()) {
        $_SESSION['flash_error'] = 'Token CSRF invalide';
        $this->redirect('/admin/restaurants/pending');
        return;
    }
        $id = $request->param('id');
        
        // Récupérer les données
        $data = [
            'nom' => $request->post('nom'),
            'type_cuisine' => $request->post('type_cuisine'),
            'ville' => $request->post('ville'),
            'adresse' => $request->post('adresse'),
            'telephone' => $request->post('telephone'),
            'description' => $request->post('description'),
            'price_range' => $request->post('price_range'),
            'horaires' => $request->post('horaires')
        ];
        
        $updated = $this->restaurantModel->update((int)$id, $data);
        
        if ($updated) {
            $_SESSION['flash_success'] = 'Restaurant mis à jour avec succès';
        } else {
            $_SESSION['flash_error'] = 'Erreur lors de la mise à jour';
        }
        
        $this->redirect('/admin/restaurants/' . $id . '/edit');
    }
    
    /**
     * Toggle le statut "featured" d'un restaurant
     */
    public function toggleFeatured(Request $request): void
    {
        if (!\verify_csrf()) {
        $this->json(['success' => false, 'message' => 'Token CSRF invalide'], 403);
        return;
    }
    
        $id = $request->param('id');
        $restaurant = $this->restaurantModel->find((int)$id);
        
        if (!$restaurant) {
            $this->json(['success' => false, 'message' => 'Restaurant non trouvé'], 404);
            return;
        }
        
        $newStatus = $restaurant['is_featured'] ? 0 : 1;
        
        $updated = $this->restaurantModel->update((int)$id, [
            'is_featured' => $newStatus
        ]);
        
        if ($updated) {
            $this->json([
                'success' => true,
                'message' => $newStatus ? 'Mis en vedette' : 'Retiré de la vedette',
                'is_featured' => $newStatus
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Erreur'], 500);
        }
    }
}

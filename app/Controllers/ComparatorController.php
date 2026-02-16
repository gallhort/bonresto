<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class ComparatorController extends Controller
{
    /**
     * F19 - Page comparateur de restaurants
     * GET /comparateur
     */
    public function index(): void
    {
        $this->render('comparator.index', [
            'title' => 'Comparateur de restaurants | Le Bon Resto',
            'meta_description' => 'Comparez les restaurants cote a cote : notes, prix, amenites, avis. Trouvez le meilleur restaurant en Algerie.',
        ]);
    }

    /**
     * API - Donnees de comparaison
     * GET /api/comparateur?ids=1,40,374
     */
    public function apiCompare(): void
    {
        header('Content-Type: application/json');

        $idsRaw = $_GET['ids'] ?? '';
        $ids = array_filter(array_map('intval', explode(',', $idsRaw)));

        if (count($ids) < 2 || count($ids) > 3) {
            echo json_encode(['success' => false, 'error' => 'Selectionnez entre 2 et 3 restaurants']);
            return;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        // Restaurant base info
        $stmt = $this->db->prepare("
            SELECT r.id, r.nom, r.slug, r.ville, r.wilaya, r.type_cuisine, r.price_range,
                   r.note_moyenne, r.nb_avis, r.vues_total, r.popularity_score,
                   r.phone, r.website, r.orders_enabled, r.reservations_enabled,
                   rp.path as main_photo,
                   (SELECT COUNT(*) FROM reviews rv WHERE rv.restaurant_id = r.id AND rv.status = 'approved') as platform_reviews
            FROM restaurants r
            LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id AND rp.type = 'main' AND rp.ordre = 0
            WHERE r.id IN ({$placeholders}) AND r.status = 'validated'
            GROUP BY r.id
        ");
        $stmt->execute($ids);
        $restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($restaurants)) {
            echo json_encode(['success' => false, 'error' => 'Aucun restaurant trouve']);
            return;
        }

        // Index by id for enrichment
        $restoMap = [];
        foreach ($restaurants as &$r) {
            $r['options'] = [];
            $r['avg_notes'] = ['nourriture' => 0, 'service' => 0, 'ambiance' => 0, 'prix' => 0];
            $r['awards'] = [];
            $r['horaires_today'] = null;
            $r['pros_top'] = [];
            $r['cons_top'] = [];
            $restoMap[$r['id']] = &$r;
        }
        unset($r);

        $idList = array_keys($restoMap);
        $ph = implode(',', array_fill(0, count($idList), '?'));

        // Options/amenites (colonnes directes, pas key-value)
        $optStmt = $this->db->prepare("
            SELECT restaurant_id, wifi, terrace, parking, air_conditioning, delivery,
                   handicap_access, baby_chair, game_zone, takeaway, pets_allowed,
                   private_room, prayer_room, valet_service
            FROM restaurant_options
            WHERE restaurant_id IN ({$ph})
        ");
        $optStmt->execute($idList);
        // Map DB columns to display keys used in the view
        $optMapping = [
            'wifi' => 'wifi', 'terrace' => 'terrasse', 'parking' => 'parking',
            'air_conditioning' => 'climatisation', 'delivery' => 'livraison',
            'handicap_access' => 'accessible_pmr', 'baby_chair' => 'jeux_enfants',
            'game_zone' => 'jeux_enfants', 'takeaway' => 'emporter',
            'pets_allowed' => 'animaux', 'private_room' => 'salle_privee',
            'prayer_room' => 'salle_priere', 'valet_service' => 'voiturier',
        ];
        foreach ($optStmt->fetchAll(PDO::FETCH_ASSOC) as $opt) {
            $rid = $opt['restaurant_id'];
            if (isset($restoMap[$rid])) {
                foreach ($optMapping as $dbCol => $displayKey) {
                    if (!empty($opt[$dbCol])) {
                        $restoMap[$rid]['options'][$displayKey] = 1;
                    }
                }
            }
        }

        // Average sub-notes
        $notesStmt = $this->db->prepare("
            SELECT restaurant_id,
                   ROUND(AVG(note_nourriture), 1) as avg_nourriture,
                   ROUND(AVG(note_service), 1) as avg_service,
                   ROUND(AVG(note_ambiance), 1) as avg_ambiance,
                   ROUND(AVG(note_prix), 1) as avg_prix
            FROM reviews
            WHERE restaurant_id IN ({$ph}) AND status = 'approved'
            GROUP BY restaurant_id
        ");
        $notesStmt->execute($idList);
        foreach ($notesStmt->fetchAll(PDO::FETCH_ASSOC) as $n) {
            $rid = $n['restaurant_id'];
            if (isset($restoMap[$rid])) {
                $restoMap[$rid]['avg_notes'] = [
                    'nourriture' => (float)$n['avg_nourriture'],
                    'service' => (float)$n['avg_service'],
                    'ambiance' => (float)$n['avg_ambiance'],
                    'prix' => (float)$n['avg_prix'],
                ];
            }
        }

        // Awards
        $awStmt = $this->db->prepare("
            SELECT restaurant_id, award_type FROM restaurant_awards WHERE restaurant_id IN ({$ph})
        ");
        $awStmt->execute($idList);
        foreach ($awStmt->fetchAll(PDO::FETCH_ASSOC) as $aw) {
            $rid = $aw['restaurant_id'];
            if (isset($restoMap[$rid])) {
                $restoMap[$rid]['awards'][] = $aw['award_type'];
            }
        }

        // Today's hours (all positional params - never mix ? and :named)
        $dayOfWeek = (int)(new \DateTime('now', new \DateTimeZone('Africa/Algiers')))->format('N') - 1; // 0=Lun, 6=Dim
        $hStmt = $this->db->prepare("
            SELECT restaurant_id, ouverture_matin, fermeture_matin, ouverture_soir, fermeture_soir, service_continu, ferme
            FROM restaurant_horaires
            WHERE restaurant_id IN ({$ph}) AND jour_semaine = ?
        ");
        $hParams = $idList;
        $hParams[] = $dayOfWeek;
        $hStmt->execute($hParams);
        foreach ($hStmt->fetchAll(PDO::FETCH_ASSOC) as $h) {
            $rid = $h['restaurant_id'];
            if (isset($restoMap[$rid])) {
                $restoMap[$rid]['horaires_today'] = $h;
            }
        }

        // Note: pros/cons columns not yet in reviews table — skip for now

        echo json_encode([
            'success' => true,
            'restaurants' => array_values($restoMap),
        ]);
    }
}

<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use PDO;

class CitiesApiController extends Controller
{
    public function search(): void
    {
        header('Content-Type: application/json');
        
        $query = $_GET['q'] ?? '';
        
        if (strlen($query) < 2) {
            echo json_encode([]);
            return;
        }
        
        try {
            $sql = "SELECT commune_name_ascii as commune, wilaya_name_ascii as wilaya, gps FROM algeria_cities WHERE commune_name_ascii LIKE ? OR wilaya_name_ascii LIKE ? LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['%' . $query . '%', '%' . $query . '%']);
            
            $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cities = array_filter($cities, function($city) {
                return !empty($city['gps']) && strpos($city['gps'], ',') !== false;
            });
            
            echo json_encode(array_values($cities));
            
        } catch (\Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
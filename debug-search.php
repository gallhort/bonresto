<?php
header('Content-Type: application/json; charset=utf-8');

// Afficher EXACTEMENT ce qui est reçu
echo json_encode([
    'get_params' => $_GET,
    'post_params' => $_POST,
    'server_query_string' => $_SERVER['QUERY_STRING'],
    'server_request_uri' => $_SERVER['REQUEST_URI'],
    'parametres_attendus' => [
        'adresse' => 'string (ex: Alger)',
        'foodType' => 'string (ex: Tous)',
        'searchRadius' => 'integer (ex: 30)',
        'currentgps' => 'string format lat,lon (ex: 36.7372,3.0869)'
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
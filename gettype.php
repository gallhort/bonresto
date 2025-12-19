

        <?php

            // Charger la configuration centrale et fournir un message d'erreur journalisé
include_once __DIR__ . '/connect.php';
require_once __DIR__ . '/classes/DatabasePDO.php';

// Si la config BDD n'est pas disponible, logger et retourner une erreur générique
if ((!isset($dbh) || !($dbh instanceof PDO)) && (empty(getenv('DB_USER')) && empty(getenv('DB_PASS')))) {
    error_log('gettype.php: DB credentials not set or connect.php failed to initialize $dbh');
    http_response_code(500);
    echo json_encode(['error' => 'Erreur interne BDD']);
    exit;
}

try {
    $dbw = new DatabasePDO();
    $rows = $dbw->fetchAll("SELECT DISTINCT type FROM vendeur");
    $result = [];
    foreach ($rows as $r) {
        $result[] = ['type' => $r['type']];
    }
    echo json_encode($result);
} catch (Exception $e) {
    // Logger l'erreur complète pour diagnostic (ne pas exposer au client)
    error_log('gettype.php exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur interne BDD']);
}

         ?>












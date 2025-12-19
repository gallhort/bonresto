

        <?php

            require_once __DIR__ . '/classes/DatabasePDO.php';

try {
    $dbw = new DatabasePDO();
    $rows = $dbw->fetchAll("SELECT DISTINCT type FROM vendeur");
    $result = [];
    foreach ($rows as $r) {
        $result[] = ['type' => $r['type']];
    }
    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur interne BDD']);
}

         ?>












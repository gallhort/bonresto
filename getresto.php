

        <?php

include_once __DIR__ . '/connect.php';
$db = getenv('DB_NAME') ?: 'appsam';
$nom = trim($_POST['nom'] ?? '');
if ($nom === '') {
    // Pas de nom fourni → renvoyer un tableau vide
    echo json_encode([]);
    exit;
}

// Use DatabasePDO wrapper instead of raw mysqli
require_once __DIR__ . '/classes/DatabasePDO.php';
try {
    $dbw = new DatabasePDO();
    $rows = $dbw->fetchAll("SELECT gps, nom, type, adresse, codePostal, descriptif, ville FROM vendeur WHERE nom = ?", [$nom]);
    echo json_encode($rows);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur interne BDD']);
}

?>












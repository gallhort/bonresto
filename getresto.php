

        <?php

$servername = 'localhost';
$username = 'sam';
$password = '123';
$db='appsam';
$nom = trim($_POST['nom'] ?? '');
if ($nom === '') {
    // Pas de nom fourni → renvoyer un tableau vide
    echo json_encode([]);
    exit;
}

//On établit la connexion
$conn = new mysqli($servername, $username, $password, $db);

//On vérifie la connexion
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
    exit;
}
$conn->set_charset('utf8mb4');

// Requête préparée pour éviter l'injection SQL
$stmt = $conn->prepare("SELECT gps, nom, type, adresse, codePostal, descriptif, ville FROM vendeur WHERE nom = ?");
$stmt->bind_param('s', $nom);
$stmt->execute();
$resultSet = $stmt->get_result();
$result = [];
while ($ligne = $resultSet->fetch_assoc()) {
    $result[] = [
        'gps' => $ligne['gps'],
        'nom' => $ligne['nom'],
        'type' => $ligne['type'],
        'adresse' => $ligne['adresse'],
        'codePostal' => $ligne['codePostal'],
        'descriptif' => $ligne['descriptif'],
        'ville' => $ligne['ville']
    ];
}

echo json_encode($result);

?>












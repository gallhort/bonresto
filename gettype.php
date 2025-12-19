

        <?php

            $servername = 'localhost';
            $username = 'sam';
            $password = '123';
            $db='lebonresto';
            //je récupère le nom et les coordonnées gps dans la base vendeur
            $requete="SELECT distinct type FROM vendeur";

            //On établit la connexion
            $conn = new mysqli($servername, $username, $password,$db);
            $conn->set_charset("utf8mb4"); // charset plus complet

            //On vérifie la connexion
            if($conn->connect_error){
                http_response_code(500);
                echo json_encode(['error' => 'Erreur de connexion']);
                exit;
            }

$resultat = $conn->query($requete);
            $result = [];
            if ($resultat && $resultat->num_rows > 0) {
                while ($ligne = $resultat->fetch_assoc()) {
                    $result[] = ['type' => $ligne['type']];
                }
            }

            echo json_encode($result);
         ?>












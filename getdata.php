

        <?php

            include_once __DIR__ . '/connect.php';
require_once __DIR__ . '/classes/DatabasePDO.php';

if(isset($_GET['type'])){

            $radius = isset($_GET['radius']) ? (float)$_GET['radius'] : 10.0;
            $type = isset($_GET['type']) ? trim($_GET['type']) : 'Tous';
            $start = isset($_GET['start']) ? max(0, (int)$_GET['start']) : 0;
            $nb = isset($_GET['nb']) ? (int)$_GET['nb'] : 20;
            $nb = max(1, min(100, $nb));
            $lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 0.0;
            $lon = isset($_GET['lon']) ? (float)$_GET['lon'] : 0.0;
            $tri = isset($_GET['tri']) ? (int)$_GET['tri'] : 0;
            // Sécuriser mOptions (ne pas unserialiser aveuglément)
            $mOptions = null;
            if (!empty($_GET['mOptions'])) {
                $decoded = base64_decode($_GET['mOptions'], true);
                if ($decoded !== false) {
                    $un = @unserialize($decoded);
                    if (is_array($un)) {
                        $mOptions = array_filter($un, function($v){ return preg_match('/^[A-Za-z0-9_]+$/', $v); });
                    }
                }
            }
            //je récupère le nom et les coordonnées gps dans la base vendeur


// Construire la requête en échappant les valeurs non numériques
            // Initialize PDO wrapper
            try {
                $dbw = new DatabasePDO();
            } catch (Exception $e) {
                error_log('getdata: DB init failed: ' . $e->getMessage());
                echo json_encode([]);
                exit;
            }

            // Expression de distance (Haversine-like) - lat/lon insérés comme nombres (casts faits ci-dessus)
            $distanceExpr = "(((acos(sin((".$lat."*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((".$lat."*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((".$lon."- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344)";

            $params = ['radius' => $radius];
            if($type === 'Tous'){
                $requete = "SELECT {$distanceExpr} as distance, gps, v.nom,type,adresse,codePostal,descriptif,ville FROM vendeur v JOIN options o on v.Nom=o.Nom WHERE {$distanceExpr} <= :radius";
            } else {
                $requete = "SELECT {$distanceExpr} as distance, gps, v.nom,type,adresse,codePostal,descriptif,ville FROM vendeur v JOIN options o on v.Nom=o.Nom WHERE {$distanceExpr} <= :radius AND v.Type = :type";
                $params['type'] = $type;
            }


                                    if(!empty($mOptions)){
                                foreach($mOptions as $val){
                                    // $val validé par regex ci-dessus
                                    $requete .= " AND o.`".$val."`=1";
                                }
                                }


                                             

                          
                          
                          
                          
                          
                          

                                             
            switch ($tri) {
                case 1:
                    $requete .= " ORDER BY v.nom ASC LIMIT {$start}, {$nb} ";
                    break;
                case 2:
                    $requete .= " ORDER BY distance ASC LIMIT {$start}, {$nb} ";
                    break;
                default:
                    $requete .= " LIMIT {$start}, {$nb} ";
            }

            $rows = $dbw->fetchAll($requete, $params);
            $result = [];

            foreach ($rows as $ligne) {
                $arraybuf = ['gps'=>$ligne['gps'],'nom'=>$ligne['nom'],'type'=>$ligne['type'],
                    'adresse'=>$ligne['adresse'],'codePostal'=>$ligne['codePostal'],'descriptif'=>$ligne['descriptif'],'ville'=>$ligne['ville']];

                $result[] = $arraybuf;
            }

            echo json_encode($result);
            } elseif (isset($_POST['nom'])) {
                $nom = trim($_POST['nom'] ?? '');

                // Utiliser la connexion centralisée
                include_once __DIR__ . '/connect.php';
                if (!isset($dbh) || !$dbh) {
                    echo json_encode([]);
                    exit;
                }

                $stmt = $dbh->prepare("SELECT * from vendeur v JOIN photos p on v.Nom=p.Nom JOIN regime r on r.Nom=v.Nom JOIN options o on o.Nom=v.Nom WHERE r.NOM = ?");
                $stmt->execute([$nom]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                echo json_encode($result);
            }elseif(isset($_POST['options'])){
                    $result = [];
                    include_once __DIR__ . '/connect.php';
                    if (!isset($dbh) || !$dbh) {
                        echo json_encode($result);
                        exit;
                    }

                    $sqlCols = $dbh->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'options'");
                    $sqlCols->execute([getenv('DB_NAME') ?: 'lebonresto']);
                    foreach ($sqlCols->fetchAll(PDO::FETCH_ASSOC) as $row) {
                        if ($row["COLUMN_NAME"] == 'id' || $row["COLUMN_NAME"] == 'Nom') continue;
                        $result[] = $row["COLUMN_NAME"];
                    }
    




                    
                    echo json_encode($result);
                }
        ?>














        <?php

            $servername = 'localhost';
            $username = 'sam';
            $password = '123';
            $db='lebonresto';

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
            $typeEsc = $conn->real_escape_string($type);

            // Expression de distance (Haversine-like) - lat/lon insérés comme nombres (casts faits ci-dessus)
            $distanceExpr = "(((acos(sin((".$lat."*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((".$lat."*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((".$lon."- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344)";

            if($typeEsc === 'Tous'){
                $requete = "SELECT {$distanceExpr} as distance, gps, v.nom,type,adresse,codePostal,descriptif,ville FROM vendeur v JOIN options o on v.Nom=o.Nom WHERE {$distanceExpr} <= " . $radius;
            } else {
                $requete = "SELECT {$distanceExpr} as distance, gps, v.nom,type,adresse,codePostal,descriptif,ville FROM vendeur v JOIN options o on v.Nom=o.Nom WHERE {$distanceExpr} <= " . $radius . " AND v.Type='" . $typeEsc . "'";
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
            

            //On établit la connexion
            $conn = new mysqli($servername, $username, $password,$db);
            
            //On vérifie la connexion
            if($conn->connect_error){
                die('Erreur : ' .$conn->connect_error);
            }

        $resultat=mysqli_query($conn,$requete);
            $arraybuf=array();
        $result=array();

        // pour chaque élément renvoyé par ma requete sql je créé un tab de tab 
        // pour pouvoir renvoyer un resultat avec le format attendu par
    		while ($ligne = $resultat -> fetch_assoc()) {

          $arraybuf=array('gps'=>$ligne['gps'],'nom'=>$ligne['nom'],'type'=>$ligne['type'],
          'adresse'=>$ligne['adresse'],'codePostal'=>$ligne['codePostal'],'descriptif'=>$ligne['descriptif'],'ville'=>$ligne['ville']);



         array_push($result,$arraybuf);
               }
               echo json_encode($result);


            }elseif(isset($_POST['nom'])){
                $nom = trim($_POST['nom'] ?? '');

                $dbh = new PDO('mysql:host=localhost;dbname=lebonresto', "sam", "123", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                $stmt = $dbh->prepare("SELECT * from vendeur v JOIN photos p on v.Nom=p.Nom JOIN regime r on r.Nom=v.Nom JOIN options o on o.Nom=v.Nom WHERE r.NOM = ?");
                $stmt->execute([$nom]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                echo json_encode($result);
            }elseif(isset($_POST['options'])){
                                $result=[];
                    $dbh = new PDO('mysql:host=localhost;dbname=lebonresto', "sam", "123");
    
                    foreach($dbh->query("SELECT COLUMN_NAME
                    FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'lebonresto' AND TABLE_NAME = 'options'") as $row) {
                    
                    if($row["COLUMN_NAME"]=='id' || $row["COLUMN_NAME"]=='Nom'){
                    
                    }else{
                    
                        array_push($result,$row["COLUMN_NAME"] );
                    }
                    }
    




                    
                    echo json_encode($result);
                }
        ?>












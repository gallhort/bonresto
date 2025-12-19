

        <?php

            $servername = 'localhost';
            $username = 'sam';
            $password = '123';
            $db='lebonresto';

if(isset($_GET['type'])){

            $radius=$_GET['radius'];
            $type=$_GET['type'];
            $start=$_GET['start'];
            $nb=$_GET['nb'];
            $lat=$_GET['lat'];
            $lon=$_GET['lon'];
            $tri=$_GET['tri'];
            $mOptions= unserialize( base64_decode( $_GET['mOptions'] ) );
            //je récupère le nom et les coordonnées gps dans la base vendeur


// Je dois ajouter le tri pour que les marqkers correspondent a la liste des restos

            if($type=='Tous'){ $requete = "SELECT (((acos(sin((" . $lat . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $lat . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" 
                . $lon . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance,  gps, v.nom,type,adresse,codePostal,descriptif,ville FROM vendeur v JOIN options o on v.Nom=o.Nom WHERE (((acos(sin((" . $lat . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $lat . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" . $lon . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) <= " 
                . $radius;
            }else{ $requete = "SELECT (((acos(sin((" . $lat . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $lat . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" 
                . $lon . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) as distance,  gps, v.nom,type,adresse,codePostal,descriptif,ville FROM vendeur v JOIN options o on v.Nom=o.Nom WHERE (((acos(sin((" . $lat . "*pi()/180)) * sin((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) + cos((" . $lat . "*pi()/180)) * cos((SUBSTRING_INDEX(gps, ',', 1)*pi()/180)) * cos(((" . $lon . "- SUBSTRING_INDEX(gps, ',', -1)) * pi()/180)))) * 180/pi()) * 60 * 1.1515 * 1.609344) <= " . $radius
                ." AND Type='$type' ";}

                                if (isset($tri) || isset($tri)  || isset($tri) ) {
                                    if($mOptions!=NULL){             
                                foreach ($mOptions as $val){

                            $requete .=" AND $val=1";

                                }
                                }


                                     switch ($tri) {
                                         case 1:
                                             $requete .= "  ORDER BY nom ASC LIMIT {$start}, {$nb}  ";
                                             break;
                                         case 2:
                          
                          
                          
                          
                          
                          
                                            $requete .= " ORDER BY distance ASC LIMIT {$start}, {$nb}  ";
                                             break;
                                             case 3:
                                                $requete .= " LIMIT {$start}, {$nb}  ";
                                                break;
                                     }
                                 } else  $requete .= " LIMIT {$start}, {$nb}";
            

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
            $nom=$_POST['nom'];

                $dbh = new PDO('mysql:host=localhost;dbname=lebonresto', "sam", "123");

                foreach($dbh->query("SELECT * from vendeur v JOIN photos p on v.Nom=p.Nom JOIN regime r on r.Nom=v.Nom JOIN options o on o.Nom=v.Nom WHERE r.NOM='$nom'") as $ligne) {

                    $result=$ligne;

                }

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












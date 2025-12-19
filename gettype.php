

        <?php

            $servername = 'localhost';
            $username = 'sam';
            $password = '123';
            $db='lebonresto';
            //je récupère le nom et les coordonnées gps dans la base vendeur
            $requete="SELECT distinct type FROM vendeur";

            //On établit la connexion
            $conn = new mysqli($servername, $username, $password,$db);
            $conn->set_charset("utf8"); // <- important

            
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

          $arraybuf=array('type'=>$ligne['type']);

 

         array_push($result,$arraybuf);
               }


               echo json_encode($result);
         ?>












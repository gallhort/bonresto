

        <?php

$servername = 'localhost';
$username = 'sam';
$password = '123';
$db='appsam';
$nom=$_POST['nom'];
//je récupère le nom et les coordonnées gps dans la base vendeur
$requete="SELECT gps, nom,type,adresse,codePostal,descriptif,ville FROM vendeur WHERE nom ='$nom' ";

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

?>












<?php


var_dump($_POST);
$parking=0;
$gamezone=0;
$handi=0;
$baby=0;
$priere=0;
$private=0;
$voiturier=0;
$wifi=0;
$photomain = "" ;
$photoslide1="";
 $photoslide2="";
$photoslide3="";

    $nom=$_POST["nom"];
    $type=$_POST["type"];
    $adresse=$_POST["adresse"] ;
    $cp=$_POST["cp"];
    $ville=$_POST["ville"];
    $phone=$_POST["phone"] ;
    if(isset($_POST["description"]))  $desc=$_POST["description"];
    if(isset($_POST["web"]))  $web=$_POST["web"];
    if(isset($_POST["cert"]))  $cert=$_POST["cert"];

   if(isset($_POST["Parking"])) $parking=$_POST["Parking"];
   if(isset($_POST["gamezone"]))  $gamezone=$_POST["gamezone"];
   if(isset($_POST["Handi"]))  $handi=$_POST["Handi"];
   if(isset($_POST["baby"]))  $baby=$_POST["baby"];
   if(isset($_POST["priere"]))  $priere=$_POST["priere"];
   if(isset($_POST["private"]))  $private=$_POST["private"];
   if(isset($_POST["Voiturier"]))  $voiturier=$_POST["Voiturier"];
   if(isset($_POST["wifi"]))  $wifi=$_POST["wifi"];
  
   if(isset($_FILES['main']['name'])) $photomain = "images/vendeur/".$_FILES['main']['name'] ;
   if(isset($_FILES['slide1']['name'])) $photoslide1="images/vendeur/".$_FILES['slide1']['name'];
   if(isset($_FILES['slide2']['name'])) $photoslide2="images/vendeur/".$_FILES['slide2']['name'];
   if(isset($_FILES['slide3']['name'])) $photoslide3="images/vendeur/".$_FILES['slide3']['name'];





include_once __DIR__ . '/connect.php';
if (!isset($dbh) || !$dbh) {
    die('Erreur BDD');
}
$pdo = $dbh; // compatibilité



$sql = "INSERT INTO addresto (Nom, Type, adresse, codePostal, ville, descriptif, phone,main,slide1,slide2,slide3, web,  baby, gamezone, handi, parking, priere, private, voiturier, wifi) 
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
$pdo->prepare($sql)->execute([$nom, $type, $adresse,$cp,$ville,$desc,$phone,$photomain,$photoslide1,$photoslide2,$photoslide3,$web,$baby,$gamezone,$handi,$parking,$priere,$private,$voiturier,$wifi]);
echo("200");



// $sql = "INSERT INTO addresto (Nom, Type, adresse, codePostal, ville, descriptif, phone, web, main, slide1, slide2, slide3, baby, gamezone, handi, parking, priere, private, voiturier, wifi) 
// VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
// $pdo->prepare($sql)->execute([$nom, $type, $adresse,$cp,$ville,$desc,$phone,$web,$photomain,$photoslide1,$photoslide2,$photoslide3,$baby,$handi,$parking,$priere,$private,$voiturier,$wifi]);
// echo("200");
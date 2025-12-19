<?php
date_default_timezone_set('Europe/Paris'); //evite le decalage horraire

//Création d'un taleau pour récupérer l'abréviation du jour actuel en fonction 
// de date('N') si date('N') renvoie 1 alors nous sommes le premier jour de la semaine 
// donc $tabDay[date('N')] renverra lun
$tabDay=['','lun','mar','mer','jeu','ven','sam','dim'];


//date ('Gi') renvoie l'heure actuelle sous la forme 1200 pour midi ou 2030 pour 20h30
if(date ('Gi')>1200){
   $moment='ap';
}else $moment='mat';

$compare = $tabDay[date('N')].'_'. $moment;

$req="SELECT ".$compare." FROM horaires WHERE Nom='Ange'";

$dbh = new PDO('mysql:host=localhost;dbname=lebonresto', "sam", "123");
$result= $dbh->query($req)->fetchColumn();
$arr= explode("-",$result);

        /*HEURE D'OUVERTURE ET FEMETURE*/
function isWebsiteOpen($start,$end)
{
    if(date('Gi') >= $start && date('Gi') < $end )
    {
        return true;
    }
    else
    {
        return false;
    }
}
 
if(isWebsiteOpen($arr[0],$arr[1]) === true)
{
    $opening="<font color='#4CD4B0'  size='5em' >Actuellement ouvert</font>";

    // echo "<font color='#4CD4B0'>Actuellement ouvert</font>";
}
else
{
    $opening="<font color='#F24D16' size='5em'>Actuellement fermé</font>";
    
    // echo "<font color='#F24D16'>fermée</font>";
}
?>
<?php

include('../connect.php');

if(isset($_GET['user'])){


if($_GET['action']==='test'){

    $user = $_GET['user'] ?? '';
    $resto = $_GET['resto'] ?? '';
    $stmt = $dbh->prepare("SELECT COUNT(*) FROM wishlist WHERE user = ? AND resto = ?");
    $stmt->execute([$user, $resto]);
    echo (int) $stmt->fetchColumn();


}elseif($_GET['action']==='add'){

    $req = $dbh->prepare("INSERT INTO wishlist (resto,user) VALUES (?,?)");
    $req->execute([$_GET['resto'],$_GET['user']]);

}elseif($_GET['action']==='remove'){

    $req = $dbh->prepare("DELETE FROM wishlist WHERE resto = ? AND user = ?");
    $req->execute([$_GET['resto'],$_GET['user']]);
   
}

}

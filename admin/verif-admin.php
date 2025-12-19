<?php
session_start();
include_once('connect.php');

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin-login.php');
    exit;
}

// Récupérer les données du formulaire
$login = trim($_POST['login'] ?? '');
$passwd = trim($_POST['passwd'] ?? '');

// Vérifier que les champs ne sont pas vides
if (empty($login) || empty($passwd)) {
    header('Location: admin-login.php?error=empty');
    exit;
}

// Préparer la requête pour vérifier l'utilisateur
$stmt = $conn->prepare("SELECT id, fname, lname, mail, admin FROM users WHERE login = ? AND passwd = ? LIMIT 1");
$stmt->bind_param("ss", $login, $passwd);
$stmt->execute();
$result = $stmt->get_result();

// Vérifier si l'utilisateur existe
if ($result->num_rows === 0) {
    header('Location: admin-login.php?error=credentials');
    exit;
}

$user = $result->fetch_assoc();

// Vérifier que l'utilisateur est bien admin
if ($user['admin'] != 1) {
    header('Location: admin-login.php?error=access');
    exit;
}

// Tout est OK : créer la session admin
$_SESSION['admin_id'] = $user['id'];
$_SESSION['admin_name'] = $user['fname'] . ' ' . $user['lname'];
$_SESSION['admin_mail'] = $user['mail'];
$_SESSION['admin'] = 1;
$_SESSION['admin_login_time'] = time();

// Rediriger vers le dashboard
header('Location: admin-dashboard.php');
exit;
?>
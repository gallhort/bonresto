<?php

if (isset($_GET['logout'])) {
    require_once __DIR__ . '/../auth/auth.php';
    logoutUser();
    header('Location: ../index.php');
    exit;
}




	include("../classes/connect.php");
	include("../classes/signup.php");
	include("../classes/login.php");
	$first_name = "";
	$last_name = "";
	$log = "";
	$email = "";
	$genre = "";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {

if(isset($_POST['first_name'])){

		$signup = new Signup();
		$result = $signup->evaluate($_POST);

		if($result != "") {
			echo "<div style='text_align:center; font-size:12px; color:red;'>";
			echo "Les erreurs suivantes se sont produites :<br><br>";
			echo $result;
			echo "</div>";
		}
		
	$first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email = $_POST['email'];
	$log = $_POST['log'];
	$genre = $_POST['genre'];
}else{

	$login = new Login();
	$result = $login->evaluate($_POST);

}
	}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DWWM NetWork | Connexion - Inscription</title>
    <link rel="stylesheet" href="login_signup.css">
</head>
<body>
<div class="container" id="container">
	<div class="form-container sign-up-container">

		<form method="post" action="">
	
			<h2>Crée ton Compte</h2>
			<div class="social-container">
				<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
				<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
				<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<!-- <input name="pseudo" type="text" id="text" placeholder="Ton Pseudo"> -->
			<input value="<?php echo $first_name ?>" name="first_name" type="text" class="text" placeholder="Ton Prénom">
			<input value="<?php echo $last_name ?>" name="last_name" type="text" class="text" placeholder="Ton Nom">
			<input value="<?php echo $email ?>" name="email" type="email" class="text" placeholder="Ton Email">
			<input value="<?php echo $log ?>" name="log" type="text" class="text" placeholder="Ton login">
			<select class="text" name="genre">
				<option><?php echo $genre ?></option>
				<option>Homme</option>
				<option>Femme</option>
			</select>
        	<input name="password" type="password" class="text" placeholder="Ton Mot de Passe">
			<input name="check_pass" type="password" class="text" placeholder="Confirme ton Mot de Passe">
			<button>Inscription</button><br>
	
		 <input type="submit" id="button" value="Inscription">
		</form>
	</div>
	<div class="form-container sign-in-container">
		<form action="" method="post">
			<h2>Connecte toi à ton Compte</h2>
			<div class="social-container">
				<a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
				<a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
				<a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
			</div>
			<span>Utilise ton adresse Mail et ton Mot de Passe <br> pour accéder à ton compte</span>
			<input type="text" placeholder="Ton login" name="login_name"/>
			<input type="password" placeholder="Ton Mot de Passe" name="login_pass" />
			<a href="#">Mot de Passe oublié ?</a>
			<button>Connexion</button>
		</form>
	
	</div>
	<div class="overlay-container">
		<div class="overlay">
			<div class="overlay-panel overlay-left">
				<h2>Welcome Back!</h2>
				<p>Pour nous rejoindre, Connecte toi avec tes informations personnelles</p>
				<button class="ghost" id="signIn">Pour se connecter</button>
			</div>
			<div class="overlay-panel overlay-right">
				<h2>Hello, Friend!</h2>
				<p>Entre tes informations personnelles et commence ton voyage avec nous</p>
				<button class="ghost" id="signUp">Pour s'inscrire</button>
			</div>
		</div>
	</div>
</div>


<script>
const signUpButton = document.getElementById('signUp');
const signInButton = document.getElementById('signIn');
const container = document.getElementById('container');

signUpButton.addEventListener('click', () => {
	container.classList.add("right-panel-active");
});

signInButton.addEventListener('click', () => {
	container.classList.remove("right-panel-active");
});
</script>

<script src="https://kit.fontawesome.com/e90cc14ef1.js" crossorigin="anonymous"></script>

</body>
</html>





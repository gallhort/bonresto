<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Sam tech Lebonresto - Retrouvez et réservez les meilleurs restaurants Halal et sans alcool partout en France avec le bon resto halal">
    <meta name="author" content="Ansonika">
    <title>Lebonresto - Retrouvez et réservez les meilleurs restaurants Halal et sans alcool partout en France avec le bon resto halal</title>

    <!-- Favicons-->
    <link rel="shortcut icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" type="image/x-icon" href="img/apple-touch-icon-57x57-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="72x72" href="img/apple-touch-icon-72x72-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="114x114" href="img/apple-touch-icon-114x114-precomposed.png">
    <link rel="apple-touch-icon" type="image/x-icon" sizes="144x144" href="img/apple-touch-icon-144x144-precomposed.png">

    <!-- GOOGLE WEB FONT -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">

    <!-- BASE CSS -->
    <link href="css/bootstrap_customized.min.css" rel="stylesheet">
    <link href="css/style2.css" rel="stylesheet">

    <!-- SPECIFIC CSS -->
    <link href="css/review.css" rel="stylesheet">

</head>

<body>
				
	<header class="header_in clearfix">
		<div class="container">
		<div id="logo">
			<a href="index.php">
				<img src="images/icons/logo.png" width="140" height="35" alt="">
			</a>
		</div>
	
		<!-- /top_menu -->
		<a href="#0" class="open_close">
			<i class="icon_menu"></i><span>Menu</span>
		</a>
		<nav class="main-menu">
			<div id="header_menu">
				<a href="#0" class="open_close">
					<i class="icon_close"></i><span>Menu</span>
				</a>
				<a href="index.html"><img src="img/logo.svg" width="140" height="35" alt=""></a>
			</div>
			<ul>
				<li class="submenu">
					<a href="#0" class="show-submenu">Retour</a>
				</li>
							
				
			</ul>
		</nav>
	</div>
	</header>
	<!-- /header -->
	
	<main class="bg_gray pattern">
		
		<div class="container margin_60_40">
		   <div class="row justify-content-center">
				<div class="col-lg-8">
					<div class="box_general write_review">
						<h1 class="add_bottom_15">Ecrire un commentaire pour "<?php echo htmlspecialchars($_GET['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"</h1>
<form action="rev/ajax-comments.php" method='post'  enctype="multipart/form-data">
<input type="text" name="nom" value="<?php echo htmlspecialchars($_GET['nom'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
<input type="text" name="name" value="<?php echo $_SESSION['user'];?>">
						<label class="d-block add_bottom_15">Note Globale </label>
						<div class="row">
							<div class="col-md-3 add_bottom_25">
							   <div class="add_bottom_15">Qualité du repas <strong class="food_quality_val"></strong></div>
			                   <input type="range" min="1" max="5" step="0.5" value="0" data-orientation="horizontal" id="food_quality" name="food_quality">
							</div>
							<div class="col-md-3 add_bottom_25">
								<div class="add_bottom_15">Service <strong class="service_val"></strong></div>
			                   <input type="range" min="1" max="5" step="0.5" value="0" data-orientation="horizontal" id="service" name="service">
							</div>
							<div class="col-md-3 add_bottom_25">
								<div class="add_bottom_15">Emplacement <strong class="location_val"></strong></div>
			                   <input type="range" min="1" max="5" step="0.5" value="0" data-orientation="horizontal" id="location" name="location">
							</div>
							<div class="col-md-3 add_bottom_25">
								<div class="add_bottom_15">Prix <strong class="price_val"></strong></div>
			                   <input type="range" min="1" max="5" step="0.5" value="0" data-orientation="horizontal" id="price" name="price">
							</div>
						</div>
						
						<div class="form-group">
							<label>Titre du commentaire</label>
							<input class="form-control" type="text" name="title" placeholder="Si vous pouviez le dire en une phrase , que diriez-vous?">
						</div>
						<div class="form-group">
							<label>Votre commentaire</label>
							<textarea class="form-control" name="msg"  style="height: 180px;" placeholder="Laissez un commentaire afin d'aider les autres utilisateurs à se faire un avis sur cet établissement."></textarea>
						</div>
<div class="form-group">
	<label>Ajouter des photos (optionnel - max 5 photos)</label>
	<div class="fileupload">
		<input type="file" name="fileupload[]" accept="image/*" multiple id="photoInput">
	</div>
	<small class="form-text text-muted">
		Formats acceptés : JPG, PNG, GIF, WEBP • Taille max par photo : 5MB
	</small>
	<div id="photoPreview" class="mt-3" style="display: flex; gap: 10px; flex-wrap: wrap;"></div>
</div>

<script>
// Prévisualisation des photos avant upload
document.getElementById('photoInput').addEventListener('change', function(e) {
	const preview = document.getElementById('photoPreview');
	preview.innerHTML = '';
	
	const files = Array.from(e.target.files).slice(0, 5); // Max 5 photos
	
	files.forEach((file, index) => {
		const reader = new FileReader();
		reader.onload = function(e) {
			const div = document.createElement('div');
			div.style.cssText = 'position: relative; width: 100px; height: 100px;';
			div.innerHTML = `
				<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
				<button type="button" onclick="removePhoto(${index})" style="position: absolute; top: -8px; right: -8px; background: #ff4757; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px;">×</button>
			`;
			preview.appendChild(div);
		};
		reader.readAsDataURL(file);
	});
	
	if (files.length >= 5) {
		alert('Maximum 5 photos autorisées');
	}
});

function removePhoto(index) {
	const input = document.getElementById('photoInput');
	const dt = new DataTransfer();
	const files = Array.from(input.files);
	
	files.forEach((file, i) => {
		if (i !== index) dt.items.add(file);
	});
	
	input.files = dt.files;
	input.dispatchEvent(new Event('change'));
}
</script>
						<div class="form-group">
							<div class="checkboxes float-left add_bottom_15 add_top_15">

							</div>
						</div>
						<input type="submit" class="btn_1" style="display : block; margin : auto;" value="Poster le commentaire">
						
						</form>
					</div>
				</div>
		</div>
		<!-- /row -->
		</div>
		<!-- /container -->
		
	</main>
	<!-- /main -->

	<footer>
		<div class="container">
			<div class="row">
				<div class="col-lg-3 col-md-6">
					<h3 data-target="#collapse_1">Quick Links</h3>
					<div class="collapse dont-collapse-sm links" id="collapse_1">
						<ul>
							<li><a href="about.html">About us</a></li>
							<li><a href="help.html">Add your restaurant</a></li>
							<li><a href="help.html">Help</a></li>
							<li><a href="account.html">My account</a></li>
							<li><a href="blog.html">Blog</a></li>
							<li><a href="contacts.html">Contacts</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<h3 data-target="#collapse_2">Categories</h3>
					<div class="collapse dont-collapse-sm links" id="collapse_2">
						<ul>
							<li><a href="listing-grid-1-full.html">Top Categories</a></li>
							<li><a href="listing-grid-2-full.html">Best Rated</a></li>
							<li><a href="listing-grid-1-full.html">Best Price</a></li>
							<li><a href="listing-grid-3.html">Latest Submissions</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
						<h3 data-target="#collapse_3">Contacts</h3>
					<div class="collapse dont-collapse-sm contacts" id="collapse_3">
						<ul>
							<li><i class="icon_house_alt"></i>97845 Baker st. 567<br>Los Angeles - US</li>
							<li><i class="icon_mobile"></i>+94 423-23-221</li>
							<li><i class="icon_mail_alt"></i><a href="#0">info@domain.com</a></li>
						</ul>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
						<h3 data-target="#collapse_4">Keep in touch</h3>
					<div class="collapse dont-collapse-sm" id="collapse_4">
						<div id="newsletter">
							<div id="message-newsletter"></div>
							<form method="post" action="assets/newsletter.php" name="newsletter_form" id="newsletter_form">
								<div class="form-group">
									<input type="email" name="email_newsletter" id="email_newsletter" class="form-control" placeholder="Your email">
									<button type="submit" id="submit-newsletter"><i class="arrow_carrot-right"></i></button>
								</div>
							</form>
						</div>
						<div class="follow_us">
							<h5>Follow Us</h5>
							<ul>
								<li><a href="#0"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="img/twitter_icon.svg" alt="" class="lazy"></a></li>
								<li><a href="#0"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="img/facebook_icon.svg" alt="" class="lazy"></a></li>
								<li><a href="#0"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="img/instagram_icon.svg" alt="" class="lazy"></a></li>
								<li><a href="#0"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="img/youtube_icon.svg" alt="" class="lazy"></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			<!-- /row-->
			<hr>
			<div class="row add_bottom_25">
				<div class="col-lg-6">
					<ul class="footer-selector clearfix">
						<li>
							<div class="styled-select lang-selector">
								<select>
									<option value="English" selected>English</option>
									<option value="French">French</option>
									<option value="Spanish">Spanish</option>
									<option value="Russian">Russian</option>
								</select>
							</div>
						</li>
						<li>
							<div class="styled-select currency-selector">
								<select>
									<option value="US Dollars" selected>US Dollars</option>
									<option value="Euro">Euro</option>
								</select>
							</div>
						</li>
						<li><img src="data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-src="img/cards_all.svg" alt="" width="198" height="30" class="lazy"></li>
					</ul>
				</div>
				<div class="col-lg-6">
					<ul class="additional_links">
						<li><a href="#0">Terms and conditions</a></li>
						<li><a href="#0">Privacy</a></li>
						<li><span>© 2021 Samtech</span></li>
					</ul>
				</div>
			</div>
		</div>
	</footer>
	<!--/footer-->

	<div id="toTop"></div><!-- Back to top button -->
	
	<div class="layer"></div><!-- Opacity Mask Menu Mobile -->
	
	<!-- Sign In Modal -->
	<div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">
		<div class="modal_header">
			<h3>Sign In</h3>
		</div>
		<form>
			<div class="sign-in-wrapper">
				<a href="#0" class="social_bt facebook">Login with Facebook</a>
				<a href="#0" class="social_bt google">Login with Google</a>
				<div class="divider"><span>Or</span></div>
				<div class="form-group">
					<label>Email</label>
					<input type="email" class="form-control" name="email" id="email">
					<i class="icon_mail_alt"></i>
				</div>
				<div class="form-group">
					<label>Password</label>
					<input type="password" class="form-control" name="password" id="password" value="">
					<i class="icon_lock_alt"></i>
				</div>
				<div class="clearfix add_bottom_15">
					<div class="checkboxes float-left">
						<label class="container_check">Remember me
						  <input type="checkbox">
						  <span class="checkmark"></span>
						</label>
					</div>
					<div class="float-right"><a id="forgot" href="javascript:void(0);">Forgot Password?</a></div>
				</div>
				<div class="text-center">
					<input type="submit" value="Log In" class="btn_1 full-width mb_5">
					Don’t have an account? <a href="account.html">Sign up</a>
				</div>
				<div id="forgot_pw">
					<div class="form-group">
						<label>Please confirm login email below</label>
						<input type="email" class="form-control" name="email_forgot" id="email_forgot">
						<i class="icon_mail_alt"></i>
					</div>
					<p>You will receive an email containing a link allowing you to reset your password to a new preferred one.</p>
					<div class="text-center"><input type="submit" value="Reset Password" class="btn_1"></div>
				</div>
			</div>
		</form>
		<!--form -->
	</div>
	<!-- /Sign In Modal -->
	
	<!-- COMMON SCRIPTS -->
    <script src="js/common_scripts.min.js"></script>
    <script src="js/common_func.js"></script>
    <script src="assets/validate.js"></script>

    <!-- SPECIFIC SCRIPTS -->
    <script src="js/specific_review.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    
    const sliders = [
        { id: "food_quality", label: ".food_quality_val" },
        { id: "service", label: ".service_val" },
        { id: "location", label: ".location_val" },
        { id: "price", label: ".price_val" }
    ];

    sliders.forEach(sl => {
        const slider = document.getElementById(sl.id);
        const label = document.querySelector(sl.label);

        // valeur initiale
        label.innerText = slider.value + " / 5";

        // mise à jour lorsqu'on bouge le slider
        slider.addEventListener("input", () => {
            label.innerText = slider.value + " / 5";
        });
    });

});
</script>

</body>
</html>
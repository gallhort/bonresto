<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= $title ?? 'Le Bon Resto - Annuaire des restaurants Halal en France' ?></title>
    
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/icons/logo.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset('images/icons/logo.png') ?>">
    
    <!-- Meta SEO -->
    <meta name="description" content="<?= htmlspecialchars($meta_description ?? 'Retrouvez les meilleurs restaurants en Algérie — avis, photos, menus. Le Bon Resto, votre guide restaurant DZ.') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= htmlspecialchars($og_title ?? $title ?? 'Le Bon Resto') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($og_description ?? $meta_description ?? 'Découvrez les meilleurs restaurants en Algérie.') ?>">
    <meta property="og:type" content="<?= $og_type ?? 'website' ?>">
    <meta property="og:url" content="<?= htmlspecialchars($og_url ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? 'lebonresto.dz') . ($_SERVER['REQUEST_URI'] ?? '/'))) ?>">
    <?php if (!empty($og_image)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($og_image) ?>">
    <?php endif; ?>
    <meta property="og:site_name" content="Le Bon Resto">
    <meta property="og:locale" content="fr_DZ">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    
    <!-- CSS Core -->
    <link rel="stylesheet" href="<?= asset('css/core/reset.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/core/layout.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/core/components.css') ?>">
    
    <!-- CSS Pages spécifiques -->
    <?php if(isset($pageCSS) && is_array($pageCSS)): ?>
        <?php foreach($pageCSS as $css): ?>
            <link rel="stylesheet" href="<?= asset("css/pages/{$css}.css") ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- PWA -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#00635a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Schema.org JSON-LD -->
    <?php if (!empty($schema_json)): ?>
    <script type="application/ld+json"><?= $schema_json ?></script>
    <?php endif; ?>

    <!-- Dark Mode CSS -->
    <style>
        body.dark-mode { --bg: #0f172a; --bg-card: #1e293b; --text: #e2e8f0; --text-muted: #94a3b8; --border: #334155; }
        body.dark-mode { background: var(--bg) !important; color: var(--text) !important; }
        body.dark-mode .thefork-header { background: #1e293b !important; border-color: #334155 !important; }
        body.dark-mode .card, body.dark-mode .review-card, body.dark-mode .horaires-list,
        body.dark-mode .reviews-stats, body.dark-mode .write-review-prompt { background: var(--bg-card) !important; border-color: var(--border) !important; color: var(--text) !important; }
        body.dark-mode a { color: var(--text); }
        body.dark-mode .section-title, body.dark-mode .resto-title { color: var(--text) !important; }
        body.dark-mode .resto-subtitle, body.dark-mode .description-text, body.dark-mode .review-content,
        body.dark-mode .contact-label, body.dark-mode .stats-count, body.dark-mode .rating-meta { color: var(--text-muted) !important; }
        body.dark-mode .user-dropdown { background: #1e293b !important; border-color: #334155 !important; }
        body.dark-mode .dropdown-item { color: var(--text) !important; }
        body.dark-mode .dropdown-item:hover { background: #334155 !important; }
        body.dark-mode .rating-large, body.dark-mode .horaire-row, body.dark-mode .contact-icon { background: #334155 !important; }
        body.dark-mode .horaire-row.today { background: #1a3a2a !important; }
        body.dark-mode input, body.dark-mode select, body.dark-mode textarea { background: #334155 !important; color: var(--text) !important; border-color: #475569 !important; }
        body.dark-mode .breadcrumbs { color: var(--text-muted); }
        body.dark-mode .similar-card, body.dark-mode .offer-banner { background: var(--bg-card) !important; border-color: var(--border) !important; }
        body.dark-mode .page-wrapper, body.dark-mode main { background: var(--bg) !important; }
        body.dark-mode footer { background: #0f172a !important; }
    </style>
    <script>
        // Apply dark mode before render to prevent flash
        (function() {
            if (localStorage.getItem('lbr_dark_mode') === '1') {
                document.documentElement.classList.add('dark-mode');
                document.addEventListener('DOMContentLoaded', function() {
                    document.body.classList.add('dark-mode');
                    var label = document.getElementById('darkModeLabel');
                    if (label) label.textContent = 'Mode clair';
                });
            }
        })();
        function toggleDarkMode() {
            var isDark = document.body.classList.toggle('dark-mode');
            document.documentElement.classList.toggle('dark-mode', isDark);
            localStorage.setItem('lbr_dark_mode', isDark ? '1' : '0');
            var label = document.getElementById('darkModeLabel');
            if (label) label.textContent = isDark ? 'Mode clair' : 'Mode sombre';
        }
    </script>
</head>
<body>
    <div class="page-wrapper">
        <!-- Header -->
    <?php include __DIR__ . '/../partials/header.php'; ?>
         <!-- Contenu principal -->
        <main id="main-content" role="main">
            <?= $content ?>
        </main>
        
        <!-- Footer -->
        <?php if (empty($noFooter)): ?>
        <footer role="contentinfo" style="background:#1a1a1a;color:#ccc;padding:48px 24px 24px;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;">
            <div style="max-width:1100px;margin:0 auto;display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:32px;">
                <div>
                    <div style="font-size:20px;font-weight:700;color:#fff;margin-bottom:12px;">Le Bon Resto</div>
                    <p style="font-size:13px;line-height:1.6;color:#999;">Le guide des meilleurs restaurants en Alg&eacute;rie. Avis v&eacute;rifi&eacute;s, photos, menus.</p>
                </div>
                <div>
                    <div style="font-weight:600;color:#fff;margin-bottom:12px;font-size:14px;">Decouvrir</div>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;font-size:13px;">
                        <li><a href="/collections" style="color:#999;text-decoration:none;">Collections</a></li>
                        <li><a href="/feed" style="color:#999;text-decoration:none;">Fil d'actualite</a></li>
                        <li><a href="/api/docs" style="color:#999;text-decoration:none;">API</a></li>
                        <li><a href="/search" style="color:#999;text-decoration:none;">Restaurants</a></li>
                        <li><a href="/search?sort=rating" style="color:#999;text-decoration:none;">Meilleures notes</a></li>
                        <li><a href="/classement-restaurants" style="color:#999;text-decoration:none;">Top restaurants</a></li>
                        <li><a href="/comparateur" style="color:#999;text-decoration:none;">Comparateur</a></li>
                        <li><a href="/stats" style="color:#999;text-decoration:none;">Statistiques</a></li>
                        <li><a href="/evenements" style="color:#999;text-decoration:none;">Événements</a></li>
                        <li><a href="/concierge" style="color:#999;text-decoration:none;">Assistant IA</a></li>
                        <li><a href="/premium" style="color:#999;text-decoration:none;">Premium</a></li>
                        <li><a href="/add-restaurant" style="color:#999;text-decoration:none;">Ajouter un restaurant</a></li>
                    </ul>
                </div>
                <div>
                    <div style="font-weight:600;color:#fff;margin-bottom:12px;font-size:14px;">Villes</div>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;font-size:13px;">
                        <li><a href="/search?ville=Alger" style="color:#999;text-decoration:none;">Alger</a></li>
                        <li><a href="/search?ville=Oran" style="color:#999;text-decoration:none;">Oran</a></li>
                        <li><a href="/search?ville=Constantine" style="color:#999;text-decoration:none;">Constantine</a></li>
                    </ul>
                </div>
                <div>
                    <div style="font-weight:600;color:#fff;margin-bottom:12px;font-size:14px;">Informations</div>
                    <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:8px;font-size:13px;">
                        <li><a href="/cgu" style="color:#999;text-decoration:none;">CGU</a></li>
                        <li><a href="/confidentialite" style="color:#999;text-decoration:none;">Confidentialit&eacute;</a></li>
                        <li><a href="/contact" style="color:#999;text-decoration:none;">Contact</a></li>
                    </ul>
                    <div style="margin-top:16px;">
                        <div style="font-weight:600;color:#fff;margin-bottom:8px;font-size:13px;">Newsletter</div>
                        <form id="footerNewsletter" style="display:flex;gap:6px;">
                            <input type="email" id="nlEmail" placeholder="Votre email" required style="flex:1;padding:8px 12px;border:1px solid #444;border-radius:6px;background:#222;color:#ccc;font-size:13px;">
                            <button type="submit" style="padding:8px 14px;background:#00635a;color:#fff;border:none;border-radius:6px;font-size:13px;cursor:pointer;">OK</button>
                        </form>
                        <p id="nlMsg" style="font-size:11px;color:#999;margin-top:4px;display:none;"></p>
                    </div>
                </div>
            </div>
            <div style="border-top:1px solid #333;margin-top:32px;padding-top:20px;text-align:center;font-size:12px;color:#666;">
                &copy; <?= date('Y') ?> Le Bon Resto &mdash; Tous droits r&eacute;serv&eacute;s
            </div>
        </footer>
        <?php endif; ?>
     </div>
    
    <!-- JS Core -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="<?= asset('js/core/app.js') ?>"></script>
    
    <!-- JS Pages spécifiques -->
    <?php if(isset($pageJS) && is_array($pageJS)): ?>
        <?php foreach($pageJS as $js): ?>
            <script src="<?= asset("js/pages/{$js}.js") ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
    </script>
    
<?php if (isset($_SESSION['loyalty_notification'])): 
    $notif = $_SESSION['loyalty_notification'];
    unset($_SESSION['loyalty_notification']);
?>
<div id="loyaltyToast" style="
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    animation: slideInRight 0.5s ease;
">
    <div style="
        background: linear-gradient(135deg, #00635a, #00897b);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        min-width: 280px;
    ">
        <span style="font-size: 28px;">🎉</span>
        <div style="flex: 1;">
            <strong>+<?= $notif['points'] ?> points !</strong>
            <?php if (!empty($notif['new_badge'])): ?>
                <br><small>Nouveau badge: <?= $notif['new_badge']['icon'] ?> <?= $notif['new_badge']['name'] ?></small>
            <?php endif; ?>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" style="
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.7;
        ">&times;</button>
    </div>
</div>

<style>
@keyframes slideInRight {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}
</style>

<script>
setTimeout(() => {
    const toast = document.getElementById('loyaltyToast');
    if (toast) {
        toast.style.animation = 'slideOutRight 0.5s ease forwards';
        setTimeout(() => toast.remove(), 500);
    }
}, 5000);
</script>

<style>
@keyframes slideOutRight {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
}
</style>
<?php endif; ?>

<!-- Cookie Consent Banner -->
<div id="cookieBanner" role="alert" aria-label="Consentement cookies" style="display:none; position:fixed; bottom:0; left:0; right:0; background:#1f2937; color:white; padding:16px 24px; z-index:9998; box-shadow:0 -4px 20px rgba(0,0,0,0.15);">
    <div style="max-width:1200px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
        <p style="margin:0; font-size:14px; flex:1; min-width:200px;">
            Ce site utilise des cookies essentiels pour son fonctionnement et des cookies analytiques pour améliorer votre expérience.
            <a href="/confidentialite" style="color:#5eead4; text-decoration:underline;">En savoir plus</a>
        </p>
        <div style="display:flex; gap:10px;">
            <button onclick="acceptCookies('essential')" style="padding:8px 16px; border:1px solid #5eead4; background:transparent; color:#5eead4; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600;">Essentiels uniquement</button>
            <button onclick="acceptCookies('all')" style="padding:8px 16px; background:#00635a; color:white; border:none; border-radius:6px; cursor:pointer; font-size:13px; font-weight:600;">Tout accepter</button>
        </div>
    </div>
</div>
<script>
(function() {
    if (!localStorage.getItem('cookie_consent')) {
        document.getElementById('cookieBanner').style.display = 'block';
    }
})();
function acceptCookies(level) {
    localStorage.setItem('cookie_consent', level);
    localStorage.setItem('cookie_consent_date', new Date().toISOString());
    document.getElementById('cookieBanner').style.display = 'none';
}
</script>

<!-- PWA Service Worker Registration -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
</script>

<?php include __DIR__ . '/../partials/_compare_widget.php'; ?>
<?php include __DIR__ . '/../partials/_share_modal.php'; ?>

<script>
// Footer newsletter
document.getElementById('footerNewsletter')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = document.getElementById('nlEmail').value;
    const msg = document.getElementById('nlMsg');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch('/api/newsletter/subscribe', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': csrf},
        body: JSON.stringify({email: email})
    }).then(r => r.json()).then(d => {
        msg.style.display = 'block';
        msg.style.color = d.success ? '#4ade80' : '#f87171';
        msg.textContent = d.message || (d.success ? 'Inscrit !' : 'Erreur');
        if (d.success) document.getElementById('nlEmail').value = '';
    }).catch(() => { msg.style.display='block'; msg.style.color='#f87171'; msg.textContent='Erreur réseau'; });
});
</script>

</body>
</html>

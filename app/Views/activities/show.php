<?php
$categoryLabels = [
    'plage' => 'Plage', 'parc' => 'Parc', 'monument' => 'Monument', 'musee' => 'Musée',
    'shopping' => 'Shopping', 'divertissement' => 'Divertissement', 'nightlife' => 'Vie nocturne',
    'cafe' => 'Café', 'nature' => 'Nature', 'sport' => 'Sport', 'religieux' => 'Religieux', 'culturel' => 'Culturel',
];
$categoryIcons = [
    'plage' => 'fa-umbrella-beach', 'parc' => 'fa-tree', 'monument' => 'fa-landmark',
    'musee' => 'fa-building-columns', 'shopping' => 'fa-bag-shopping', 'divertissement' => 'fa-masks-theater',
    'nightlife' => 'fa-moon', 'cafe' => 'fa-mug-hot', 'nature' => 'fa-mountain-sun',
    'sport' => 'fa-futbol', 'religieux' => 'fa-mosque', 'culturel' => 'fa-palette',
];
$priceLabels = ['gratuit' => 'Gratuit', 'pas_cher' => 'Pas cher', 'moyen' => 'Prix moyen', 'cher' => 'Cher'];
$cat = $activity['category'] ?? '';
$catLabel = $categoryLabels[$cat] ?? ucfirst($cat);
$catIcon = $categoryIcons[$cat] ?? 'fa-map-pin';
$priceLabel = $priceLabels[$activity['price_range'] ?? ''] ?? '';
$note = min($activity['note_moyenne'] ?? 0, 5);
$currentUser = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($activity['nom']) ?> - LeBonResto</title>
    <meta name="description" content="<?= htmlspecialchars(mb_substr($activity['description'] ?? '', 0, 160)) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($activity['nom']) ?> - LeBonResto">
    <meta property="og:description" content="<?= htmlspecialchars(mb_substr($activity['description'] ?? '', 0, 200)) ?>">
    <meta property="og:type" content="place">
    <?php if (!empty($photos[0]['path'])): ?><meta property="og:image" content="<?= htmlspecialchars($photos[0]['path']) ?>"><?php endif; ?>
    <link rel="canonical" href="/activite/<?= htmlspecialchars($activity['slug']) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00635a; --primary-dark: #004d44; --primary-light: #e8f5f3;
            --accent: #f59e0b; --dark: #1a1a1a;
            --gray-900: #2d2d2d; --gray-700: #555; --gray-500: #888; --gray-300: #d0d0d0;
            --gray-200: #e8e8e8; --gray-100: #f4f4f4; --white: #fff;
            --font-serif: 'DM Serif Display', Georgia, serif;
            --font-sans: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --radius: 12px; --radius-sm: 8px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.08); --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: var(--font-sans); background: var(--gray-100); color: var(--gray-900); }
        a { text-decoration: none; color: inherit; }
        img { max-width: 100%; display: block; }
        .container { max-width: 1100px; margin: 0 auto; padding: 0 20px; }

        /* ═══════ GALLERY ═══════ */
        .gallery {
            display: grid; grid-template-columns: 2fr 1fr 1fr;
            gap: 4px; height: 380px; overflow: hidden; border-radius: 0 0 var(--radius) var(--radius);
        }
        .gallery-item { overflow: hidden; cursor: pointer; position: relative; }
        .gallery-item:first-child { grid-row: 1 / 3; }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
        .gallery-item:hover img { transform: scale(1.05); }
        .gallery-placeholder {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            background: var(--gray-200); color: var(--gray-500); font-size: 48px;
        }
        .gallery-count {
            position: absolute; bottom: 10px; right: 10px;
            background: rgba(0,0,0,0.7); color: white; padding: 6px 12px;
            border-radius: 20px; font-size: 12px; font-weight: 600;
        }

        @media (max-width: 768px) {
            .gallery { grid-template-columns: 1fr; height: 260px; }
            .gallery-item:not(:first-child) { display: none; }
            .gallery-item:first-child { grid-row: auto; }
        }

        /* ═══════ CONTENT ═══════ */
        .content-grid {
            display: grid; grid-template-columns: 1fr 360px; gap: 32px;
            margin-top: 24px;
        }
        @media (max-width: 900px) {
            .content-grid { grid-template-columns: 1fr; }
        }

        /* ═══════ HEADER ═══════ */
        .act-header { margin-bottom: 24px; }
        .act-breadcrumb {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: var(--gray-500); margin-bottom: 12px;
        }
        .act-breadcrumb a { color: var(--primary); }
        .act-breadcrumb a:hover { text-decoration: underline; }

        .act-title-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
        .act-title {
            font-family: var(--font-serif); font-size: 32px; color: var(--gray-900);
            line-height: 1.2; margin-bottom: 8px;
        }
        .act-actions { display: flex; gap: 8px; flex-shrink: 0; }
        .act-action-btn {
            display: flex; align-items: center; gap: 6px;
            padding: 10px 16px; border-radius: 20px; font-size: 13px; font-weight: 600;
            border: 1px solid var(--gray-200); background: white; cursor: pointer;
            transition: all 0.2s;
        }
        .act-action-btn:hover { border-color: var(--primary); color: var(--primary); }
        .act-action-btn.wishlisted { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
        .act-action-btn.wishlisted i { font-weight: 900; }

        .act-meta {
            display: flex; align-items: center; flex-wrap: wrap; gap: 16px;
            font-size: 14px; color: var(--gray-700);
        }
        .act-meta-item { display: flex; align-items: center; gap: 6px; }
        .act-meta-item i { color: var(--primary); font-size: 14px; }
        .act-rating-big {
            display: flex; align-items: center; gap: 6px;
            background: var(--primary); color: white; padding: 4px 12px;
            border-radius: var(--radius-sm); font-weight: 700;
        }
        .act-rating-big i { font-size: 12px; }

        /* ═══════ SECTIONS ═══════ */
        .section-card {
            background: white; border-radius: var(--radius); padding: 24px;
            margin-bottom: 20px; box-shadow: var(--shadow-sm);
        }
        .section-title {
            font-family: var(--font-serif); font-size: 22px; color: var(--gray-900);
            margin-bottom: 16px;
        }
        .section-text { font-size: 15px; line-height: 1.7; color: var(--gray-700); }

        /* ═══════ SIDEBAR ═══════ */
        .sidebar-card {
            background: white; border-radius: var(--radius); padding: 20px;
            margin-bottom: 16px; box-shadow: var(--shadow-sm);
        }
        .sidebar-title { font-weight: 700; font-size: 16px; margin-bottom: 12px; }

        .info-row {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid var(--gray-100);
            font-size: 14px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row i { color: var(--primary); width: 18px; text-align: center; margin-top: 2px; }
        .info-row span { color: var(--gray-700); }

        .checkin-btn {
            width: 100%; padding: 12px; border: none; border-radius: var(--radius-sm);
            background: var(--primary); color: white; font-size: 14px; font-weight: 700;
            cursor: pointer; transition: background 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .checkin-btn:hover { background: var(--primary-dark); }
        .checkin-btn:disabled { opacity: 0.5; cursor: default; }

        /* ═══════ MAP ═══════ */
        .map-container { height: 200px; border-radius: var(--radius-sm); overflow: hidden; margin-top: 12px; }

        /* ═══════ MAP ACTION BUTTONS ═══════ */
        .map-actions {
            display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px;
        }
        .map-action-link {
            display: flex; align-items: center; justify-content: center; gap: 6px;
            padding: 10px 8px; border-radius: var(--radius-sm);
            font-size: 12px; font-weight: 600; text-align: center;
            border: 1px solid var(--gray-200); background: white; color: var(--gray-700);
            transition: all 0.2s; cursor: pointer; text-decoration: none;
        }
        .map-action-link:hover { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
        .map-action-link i { font-size: 14px; }
        .map-action-link.directions { color: #2563eb; }
        .map-action-link.directions:hover { border-color: #2563eb; background: #eff6ff; }
        .map-action-link.streetview { color: #f59e0b; }
        .map-action-link.streetview:hover { border-color: #f59e0b; background: #fffbeb; }

        /* ═══════ STREET VIEW EMBED ═══════ */
        .streetview-container {
            margin-top: 12px; border-radius: var(--radius-sm); overflow: hidden;
            position: relative; background: var(--gray-200);
        }
        .streetview-container iframe {
            width: 100%; height: 200px; border: none; display: block;
        }

        /* ═══════ REVIEWS ═══════ */
        .review-item {
            padding: 16px 0; border-bottom: 1px solid var(--gray-100);
        }
        .review-item:last-child { border-bottom: none; }
        .review-header { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
        .review-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--primary-light); display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: var(--primary);
        }
        .review-author { font-weight: 600; font-size: 14px; }
        .review-date { font-size: 12px; color: var(--gray-500); }
        .review-stars { color: var(--accent); font-size: 13px; }
        .review-text { font-size: 14px; line-height: 1.6; color: var(--gray-700); }

        .review-form { margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--gray-200); }
        .review-form h3 { font-size: 16px; font-weight: 700; margin-bottom: 12px; }
        .star-input { display: flex; gap: 4px; margin-bottom: 12px; }
        .star-input i { font-size: 24px; color: var(--gray-300); cursor: pointer; transition: color 0.15s; }
        .star-input i.active { color: var(--accent); }
        .review-textarea {
            width: 100%; min-height: 80px; padding: 12px; border: 1px solid var(--gray-200);
            border-radius: var(--radius-sm); font-size: 14px; font-family: var(--font-sans);
            resize: vertical;
        }
        .review-textarea:focus { border-color: var(--primary); outline: none; }
        .review-submit {
            margin-top: 8px; padding: 10px 24px; border: none; border-radius: var(--radius-sm);
            background: var(--primary); color: white; font-weight: 600; font-size: 14px;
            cursor: pointer;
        }
        .review-submit:hover { background: var(--primary-dark); }

        /* ═══════ TIPS ═══════ */
        .tip-item {
            display: flex; gap: 10px; padding: 10px 0;
            border-bottom: 1px solid var(--gray-100); font-size: 14px;
        }
        .tip-item:last-child { border-bottom: none; }
        .tip-icon { color: #10b981; font-size: 16px; margin-top: 2px; }
        .tip-text { flex: 1; color: var(--gray-700); }
        .tip-author { font-size: 12px; color: var(--gray-500); }

        .tip-form { display: flex; gap: 8px; margin-top: 12px; }
        .tip-input {
            flex: 1; padding: 10px 14px; border: 1px solid var(--gray-200);
            border-radius: 20px; font-size: 13px; font-family: var(--font-sans);
        }
        .tip-input:focus { border-color: var(--primary); outline: none; }
        .tip-send {
            padding: 10px 16px; border: none; border-radius: 20px;
            background: var(--primary); color: white; font-weight: 600; font-size: 13px;
            cursor: pointer;
        }

        /* ═══════ SIMILAR ═══════ */
        .similar-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }
        .similar-card {
            background: white; border-radius: var(--radius); overflow: hidden;
            box-shadow: var(--shadow-sm); transition: transform 0.2s;
        }
        .similar-card:hover { transform: translateY(-3px); }
        .similar-card-img { height: 120px; overflow: hidden; background: var(--gray-200); }
        .similar-card-img img { width: 100%; height: 100%; object-fit: cover; }
        .similar-card-body { padding: 12px; }
        .similar-card-name { font-size: 14px; font-weight: 700; margin-bottom: 4px; }
        .similar-card-meta { font-size: 12px; color: var(--gray-500); }

        /* ═══════ RATING DISTRIBUTION ═══════ */
        .rating-dist { margin-bottom: 20px; }
        .rating-dist-row {
            display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-size: 13px;
        }
        .rating-dist-label { width: 16px; text-align: right; font-weight: 600; color: var(--gray-700); }
        .rating-dist-bar { flex: 1; height: 8px; background: var(--gray-100); border-radius: 4px; overflow: hidden; }
        .rating-dist-fill { height: 100%; border-radius: 4px; background: var(--accent); transition: width 0.6s ease-out; }
        .rating-dist-count { width: 28px; font-size: 12px; color: var(--gray-500); }

        .rating-summary {
            display: flex; align-items: center; gap: 16px; margin-bottom: 20px;
            padding-bottom: 16px; border-bottom: 1px solid var(--gray-100);
        }
        .rating-big-num { font-size: 48px; font-weight: 700; color: var(--gray-900); line-height: 1; }
        .rating-big-stars { color: var(--accent); font-size: 16px; margin-bottom: 2px; }
        .rating-big-count { font-size: 13px; color: var(--gray-500); }

        /* ═══════ LIGHTBOX ═══════ */
        .lightbox {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.92); justify-content: center; align-items: center;
        }
        .lightbox.open { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 8px; }
        .lightbox-close {
            position: absolute; top: 20px; right: 20px; color: white; font-size: 28px;
            cursor: pointer; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.1); border-radius: 50%; border: none; transition: background 0.2s;
        }
        .lightbox-close:hover { background: rgba(255,255,255,0.25); }
        .lightbox-nav {
            position: absolute; top: 50%; transform: translateY(-50%); color: white; font-size: 24px;
            cursor: pointer; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.1); border-radius: 50%; border: none; transition: background 0.2s;
        }
        .lightbox-nav:hover { background: rgba(255,255,255,0.25); }
        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }
        .lightbox-counter {
            position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%);
            color: rgba(255,255,255,0.7); font-size: 14px;
        }

        /* ═══════ TOAST ═══════ */
        .toast {
            position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(100px);
            background: var(--dark); color: white; padding: 12px 24px; border-radius: 8px;
            font-size: 14px; font-weight: 500; z-index: 9999; opacity: 0;
            transition: all 0.3s;
        }
        .toast.show { transform: translateX(-50%) translateY(0); opacity: 1; }
    </style>
</head>
<body>

<?php include __DIR__ . '/../partials/header.php'; ?>

<!-- GALLERY -->
<div class="gallery" role="region" aria-label="Photos de <?= htmlspecialchars($activity['nom']) ?>">
    <?php if (!empty($photos)): ?>
        <?php foreach (array_slice($photos, 0, 3) as $i => $photo): ?>
            <div class="gallery-item" style="cursor:pointer;" onclick="openLightbox(<?= $i ?>)">
                <img src="<?= htmlspecialchars($photo['path']) ?>" alt="<?= htmlspecialchars($photo['caption'] ?? $activity['nom'] . ' - Photo ' . ($i + 1)) ?>"
                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=800&h=400&fit=crop'">
                <?php if ($i === 2 && count($photos) > 3): ?>
                    <span class="gallery-count"><i class="fas fa-images"></i> Voir les <?= count($photos) ?> photos</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php for ($i = count($photos); $i < 3; $i++): ?>
            <div class="gallery-item"><div class="gallery-placeholder"><i class="fas <?= $catIcon ?>" aria-hidden="true"></i></div></div>
        <?php endfor; ?>
    <?php else: ?>
        <div class="gallery-item"><div class="gallery-placeholder"><i class="fas <?= $catIcon ?>" aria-hidden="true"></i></div></div>
        <div class="gallery-item"><div class="gallery-placeholder"><i class="fas fa-camera" aria-hidden="true"></i></div></div>
        <div class="gallery-item"><div class="gallery-placeholder"><i class="fas fa-image" aria-hidden="true"></i></div></div>
    <?php endif; ?>
</div>

<!-- LIGHTBOX -->
<?php if (!empty($photos)): ?>
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Galerie photos">
    <button class="lightbox-close" onclick="closeLightbox()" aria-label="Fermer"><i class="fas fa-times"></i></button>
    <button class="lightbox-nav lightbox-prev" onclick="navLightbox(-1)" aria-label="Photo précédente"><i class="fas fa-chevron-left"></i></button>
    <img id="lightboxImg" src="" alt="">
    <button class="lightbox-nav lightbox-next" onclick="navLightbox(1)" aria-label="Photo suivante"><i class="fas fa-chevron-right"></i></button>
    <div class="lightbox-counter" id="lightboxCounter"></div>
</div>
<?php endif; ?>

<div class="container">
    <div class="content-grid">
        <!-- MAIN CONTENT -->
        <div>
            <!-- Header -->
            <div class="act-header">
                <div class="act-breadcrumb">
                    <a href="/">Accueil</a> <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <a href="/activites">Activités</a> <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <a href="/activites?ville=<?= urlencode($activity['ville']) ?>"><?= htmlspecialchars($activity['ville']) ?></a> <i class="fas fa-chevron-right" style="font-size:10px;"></i>
                    <span><?= htmlspecialchars($activity['nom']) ?></span>
                </div>

                <div class="act-title-row">
                    <h1 class="act-title"><?= htmlspecialchars($activity['nom']) ?></h1>
                    <div class="act-actions">
                        <button class="act-action-btn <?= $inWishlist ? 'wishlisted' : '' ?>" id="wishlistBtn" onclick="toggleActivityWishlist(<?= $activity['id'] ?>)">
                            <i class="<?= $inWishlist ? 'fas' : 'far' ?> fa-heart"></i>
                            <span><?= $inWishlist ? 'Sauvegardé' : 'Sauvegarder' ?></span>
                        </button>
                        <button class="act-action-btn" onclick="shareActivity()">
                            <i class="fas fa-share-nodes"></i> Partager
                        </button>
                    </div>
                </div>

                <div class="act-meta">
                    <?php if ($note > 0): ?>
                        <div class="act-rating-big"><?= number_format($note, 1) ?> <i class="fas fa-star"></i></div>
                        <span><?= (int)$activity['nb_avis'] ?> avis</span>
                    <?php endif; ?>
                    <span class="act-meta-item"><i class="fas <?= $catIcon ?>"></i> <?= $catLabel ?></span>
                    <span class="act-meta-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($activity['ville']) ?></span>
                    <?php if ($priceLabel): ?>
                        <span class="act-meta-item"><i class="fas fa-tag"></i> <?= $priceLabel ?></span>
                    <?php endif; ?>
                    <?php if (!empty($activity['duration_avg'])): ?>
                        <span class="act-meta-item"><i class="fas fa-clock"></i> <?= htmlspecialchars($activity['duration_avg']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Description -->
            <?php if (!empty($activity['description'])): ?>
                <div class="section-card">
                    <h2 class="section-title">À propos</h2>
                    <div class="section-text"><?= nl2br(htmlspecialchars($activity['description'])) ?></div>
                </div>
            <?php endif; ?>

            <!-- Reviews -->
            <div class="section-card">
                <h2 class="section-title">Avis (<?= count($reviews) ?>)</h2>

                <?php if (empty($reviews)): ?>
                    <p style="color:var(--gray-500); font-size:14px;">Aucun avis pour le moment. Soyez le premier !</p>
                <?php else: ?>
                    <!-- Rating summary + distribution -->
                    <?php $totalReviews = array_sum($ratingDist); ?>
                    <div class="rating-summary">
                        <div>
                            <div class="rating-big-num"><?= number_format($note, 1) ?></div>
                        </div>
                        <div>
                            <div class="rating-big-stars">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="<?= $s <= round($note) ? 'fas' : 'far' ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <div class="rating-big-count"><?= $totalReviews ?> avis</div>
                        </div>
                    </div>
                    <div class="rating-dist">
                        <?php for ($s = 5; $s >= 1; $s--): ?>
                            <?php $pct = $totalReviews > 0 ? round(($ratingDist[$s] / $totalReviews) * 100) : 0; ?>
                            <div class="rating-dist-row">
                                <span class="rating-dist-label"><?= $s ?></span>
                                <i class="fas fa-star" style="font-size:11px;color:var(--accent);" aria-hidden="true"></i>
                                <div class="rating-dist-bar"><div class="rating-dist-fill" style="width:<?= $pct ?>%"></div></div>
                                <span class="rating-dist-count"><?= $ratingDist[$s] ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="review-avatar"><?= strtoupper(mb_substr($review['prenom'] ?? 'U', 0, 1)) ?></div>
                                <div>
                                    <div class="review-author"><?= htmlspecialchars($review['prenom'] ?? 'Utilisateur') ?></div>
                                    <div class="review-date"><?= date('d/m/Y', strtotime($review['created_at'])) ?></div>
                                </div>
                                <div class="review-stars" style="margin-left:auto;">
                                    <?= str_repeat('<i class="fas fa-star"></i>', min(5, (int)$review['note_globale'])) ?>
                                    <?= str_repeat('<i class="far fa-star"></i>', 5 - min(5, (int)$review['note_globale'])) ?>
                                </div>
                            </div>
                            <div class="review-text"><?= nl2br(htmlspecialchars($review['message'] ?? '')) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Review form -->
                <?php if ($currentUser): ?>
                    <div class="review-form">
                        <h3>Laisser un avis</h3>
                        <div class="star-input" id="starInput">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="far fa-star" data-value="<?= $s ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <textarea class="review-textarea" id="reviewMessage" placeholder="Partagez votre expérience..."></textarea>
                        <button class="review-submit" onclick="submitReview()">Publier l'avis</button>
                    </div>
                <?php else: ?>
                    <p style="margin-top:16px; font-size:14px; color:var(--gray-500);">
                        <a href="#" onclick="openAuthModal(); return false;" style="color:var(--primary); font-weight:600;">Connectez-vous</a> pour laisser un avis.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Tips -->
            <div class="section-card">
                <h2 class="section-title">Conseils des visiteurs</h2>
                <?php if (empty($tips)): ?>
                    <p style="color:var(--gray-500); font-size:14px;">Aucun conseil pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($tips as $tip): ?>
                        <div class="tip-item">
                            <i class="fas fa-lightbulb tip-icon"></i>
                            <div>
                                <div class="tip-text"><?= htmlspecialchars($tip['message']) ?></div>
                                <div class="tip-author">— <?= htmlspecialchars($tip['prenom'] ?? 'Anonyme') ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <div class="tip-form">
                        <input type="text" class="tip-input" id="tipInput" placeholder="Un conseil pour les visiteurs..." maxlength="200">
                        <button class="tip-send" onclick="submitTip()">Envoyer</button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Similar -->
            <?php if (!empty($similar)): ?>
                <div class="section-card">
                    <h2 class="section-title">À voir aussi</h2>
                    <div class="similar-grid">
                        <?php foreach ($similar as $sim): ?>
                            <a href="/activite/<?= htmlspecialchars($sim['slug']) ?>" class="similar-card">
                                <div class="similar-card-img">
                                    <?php if (!empty($sim['main_photo'])): ?>
                                        <img src="<?= htmlspecialchars($sim['main_photo']) ?>" alt="<?= htmlspecialchars($sim['nom']) ?>"
                                             onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1469474968028-56623f02e42e?w=300&h=150&fit=crop'">
                                    <?php endif; ?>
                                </div>
                                <div class="similar-card-body">
                                    <div class="similar-card-name"><?= htmlspecialchars($sim['nom']) ?></div>
                                    <div class="similar-card-meta">
                                        <?= htmlspecialchars($sim['ville']) ?>
                                        <?php if ($sim['note_moyenne'] > 0): ?> · <?= number_format(min($sim['note_moyenne'], 5), 1) ?> <i class="fas fa-star" style="color:var(--accent);font-size:10px;"></i><?php endif; ?>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- SIDEBAR -->
        <div>
            <!-- Info card -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">Informations pratiques</h3>
                <?php if (!empty($activity['adresse'])): ?>
                    <div class="info-row"><i class="fas fa-location-dot"></i> <span><?= htmlspecialchars($activity['adresse']) ?></span></div>
                <?php endif; ?>
                <div class="info-row"><i class="fas fa-city"></i> <span><?= htmlspecialchars($activity['ville']) ?><?= !empty($activity['wilaya']) ? ', ' . htmlspecialchars($activity['wilaya']) : '' ?></span></div>
                <?php if (!empty($activity['phone'])): ?>
                    <div class="info-row"><i class="fas fa-phone"></i> <span><?= htmlspecialchars($activity['phone']) ?></span></div>
                <?php endif; ?>
                <?php if (!empty($activity['website']) && preg_match('#^https?://#i', $activity['website'])): ?>
                    <div class="info-row"><i class="fas fa-globe"></i> <span><a href="<?= htmlspecialchars($activity['website']) ?>" target="_blank" rel="noopener noreferrer" style="color:var(--primary);">Site web</a></span></div>
                <?php endif; ?>
                <?php if (!empty($activity['horaires_info'])): ?>
                    <div class="info-row"><i class="fas fa-clock"></i> <span><?= htmlspecialchars($activity['horaires_info']) ?></span></div>
                <?php endif; ?>
                <?php if ($priceLabel): ?>
                    <div class="info-row"><i class="fas fa-tag"></i> <span><?= $priceLabel ?></span></div>
                <?php endif; ?>
                <?php if (!empty($activity['duration_avg'])): ?>
                    <div class="info-row"><i class="fas fa-hourglass-half"></i> <span>Durée : <?= htmlspecialchars($activity['duration_avg']) ?></span></div>
                <?php endif; ?>
            </div>

            <!-- Check-in + Map + Actions -->
            <?php if ($activity['gps_latitude'] && $activity['gps_longitude']): ?>
                <?php
                    $lat = (float)$activity['gps_latitude'];
                    $lng = (float)$activity['gps_longitude'];
                    $encodedName = urlencode($activity['nom'] . ', ' . $activity['ville']);
                ?>
                <div class="sidebar-card">
                    <button class="checkin-btn" id="checkinBtn" onclick="doCheckin()" <?= $currentUser ? '' : 'disabled title="Connectez-vous"' ?>>
                        <i class="fas fa-map-pin"></i> Check-in (+20 pts)
                    </button>

                    <!-- Leaflet Map -->
                    <div class="map-container" id="detailMap"></div>

                    <!-- Google Maps embed -->
                    <div class="streetview-container">
                        <iframe
                            src="https://maps.google.com/maps?q=<?= $lat ?>,<?= $lng ?>&z=17&output=embed"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="Google Maps - <?= htmlspecialchars($activity['nom']) ?>"
                        ></iframe>
                    </div>

                    <!-- 3 action buttons -->
                    <div class="map-actions" style="grid-template-columns: 1fr 1fr 1fr;">
                        <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $lat ?>,<?= $lng ?>&travelmode=driving"
                           target="_blank" rel="noopener noreferrer" class="map-action-link directions"
                           aria-label="Itinéraire vers <?= htmlspecialchars($activity['nom']) ?>">
                            <i class="fas fa-diamond-turn-right"></i> Itinéraire
                        </a>
                        <a href="https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=<?= $lat ?>,<?= $lng ?>"
                           target="_blank" rel="noopener noreferrer" class="map-action-link streetview"
                           aria-label="Street View">
                            <i class="fas fa-street-view"></i> Street View
                        </a>
                        <a href="https://www.google.com/maps/search/?api=1&query=<?= $lat ?>,<?= $lng ?>"
                           target="_blank" rel="noopener noreferrer" class="map-action-link"
                           aria-label="Ouvrir dans Google Maps">
                            <i class="fas fa-map-location-dot"></i> Maps
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Photos count -->
            <div class="sidebar-card">
                <h3 class="sidebar-title"><i class="fas fa-camera" style="color:var(--primary);"></i> <?= count($photos) ?> photo<?= count($photos) > 1 ? 's' : '' ?></h3>
                <p style="font-size:13px; color:var(--gray-500);"><?= (int)$activity['nb_avis'] ?> avis de la communauté</p>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const ACTIVITY_ID = <?= (int)$activity['id'] ?>;
const ACTIVITY_LAT = <?= (float)($activity['gps_latitude'] ?? 0) ?>;
const ACTIVITY_LNG = <?= (float)($activity['gps_longitude'] ?? 0) ?>;

// ═══════ MAP ═══════
if (document.getElementById('detailMap') && ACTIVITY_LAT && ACTIVITY_LNG) {
    const map = L.map('detailMap', { zoomControl: false, scrollWheelZoom: false }).setView([ACTIVITY_LAT, ACTIVITY_LNG], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
    L.marker([ACTIVITY_LAT, ACTIVITY_LNG]).addTo(map)
        .bindPopup(<?= json_encode(htmlspecialchars($activity['nom']), JSON_HEX_TAG | JSON_HEX_AMP) ?>);
}

// ═══════ STAR INPUT ═══════
let selectedRating = 0;
document.querySelectorAll('#starInput i').forEach(star => {
    star.addEventListener('click', function() {
        selectedRating = parseInt(this.dataset.value);
        document.querySelectorAll('#starInput i').forEach((s, i) => {
            s.classList.toggle('fas', i < selectedRating);
            s.classList.toggle('far', i >= selectedRating);
            s.classList.toggle('active', i < selectedRating);
        });
    });
    star.addEventListener('mouseenter', function() {
        const val = parseInt(this.dataset.value);
        document.querySelectorAll('#starInput i').forEach((s, i) => {
            s.classList.toggle('fas', i < val);
            s.classList.toggle('far', i >= val);
        });
    });
});
document.getElementById('starInput')?.addEventListener('mouseleave', function() {
    document.querySelectorAll('#starInput i').forEach((s, i) => {
        s.classList.toggle('fas', i < selectedRating);
        s.classList.toggle('far', i >= selectedRating);
    });
});

// ═══════ SUBMIT REVIEW ═══════
async function submitReview() {
    const message = document.getElementById('reviewMessage').value.trim();
    if (!selectedRating) { showToast('Sélectionnez une note'); return; }
    if (message.length < 10) { showToast('Avis trop court (min 10 car.)'); return; }

    const form = new FormData();
    form.append('note', selectedRating);
    form.append('message', message);

    try {
        const res = await fetch(`/activite/${ACTIVITY_ID}/review`, { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Avis publié !');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(data.error || 'Erreur');
        }
    } catch(e) { showToast('Erreur de connexion'); }
}

// ═══════ SUBMIT TIP ═══════
async function submitTip() {
    const msg = document.getElementById('tipInput').value.trim();
    if (msg.length < 5) { showToast('Tip trop court'); return; }

    try {
        const res = await fetch(`/api/activite/${ACTIVITY_ID}/tip`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: msg })
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Tip ajouté !');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.error || 'Erreur');
        }
    } catch(e) { showToast('Erreur'); }
}

// ═══════ WISHLIST ═══════
async function toggleActivityWishlist(id) {
    try {
        const res = await fetch('/api/activity-wishlist/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ activity_id: id })
        });
        const data = await res.json();
        if (data.success) {
            const btn = document.getElementById('wishlistBtn');
            btn.classList.toggle('wishlisted', data.added);
            const icon = btn.querySelector('i');
            icon.classList.toggle('fas', data.added);
            icon.classList.toggle('far', !data.added);
            btn.querySelector('span').textContent = data.added ? 'Sauvegardé' : 'Sauvegarder';
            showToast(data.added ? 'Ajouté aux favoris' : 'Retiré des favoris');
        } else if (data.error === 'Connexion requise') {
            if (typeof openAuthModal === 'function') openAuthModal();
        }
    } catch(e) {}
}

// ═══════ CHECK-IN ═══════
async function doCheckin() {
    const btn = document.getElementById('checkinBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';

    if (!navigator.geolocation) {
        showToast('Géolocalisation non disponible');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-map-pin"></i> Check-in (+20 pts)';
        return;
    }

    navigator.geolocation.getCurrentPosition(async (pos) => {
        try {
            const res = await fetch(`/api/activite/${ACTIVITY_ID}/checkin`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ lat: pos.coords.latitude, lng: pos.coords.longitude })
            });
            const data = await res.json();
            showToast(data.success ? data.message : data.error);
        } catch(e) { showToast('Erreur'); }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-map-pin"></i> Check-in (+20 pts)';
    }, () => {
        showToast('Impossible d\'obtenir votre position');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-map-pin"></i> Check-in (+20 pts)';
    }, { enableHighAccuracy: true, timeout: 10000 });
}

// ═══════ SHARE ═══════
function shareActivity() {
    if (navigator.share) {
        navigator.share({ title: document.title, url: location.href });
    } else {
        navigator.clipboard.writeText(location.href);
        showToast('Lien copié !');
    }
}

// ═══════ LIGHTBOX ═══════
var lightboxPhotos = <?= json_encode(array_map(function($p) { return $p['path']; }, $photos ?? []), JSON_HEX_TAG) ?>;
var lightboxIdx = 0;

function openLightbox(idx) {
    if (!lightboxPhotos.length) return;
    lightboxIdx = idx;
    updateLightbox();
    document.getElementById('lightbox').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('open');
    document.body.style.overflow = '';
}
function navLightbox(dir) {
    lightboxIdx = (lightboxIdx + dir + lightboxPhotos.length) % lightboxPhotos.length;
    updateLightbox();
}
function updateLightbox() {
    document.getElementById('lightboxImg').src = lightboxPhotos[lightboxIdx];
    document.getElementById('lightboxImg').alt = 'Photo ' + (lightboxIdx + 1);
    document.getElementById('lightboxCounter').textContent = (lightboxIdx + 1) + ' / ' + lightboxPhotos.length;
}
document.addEventListener('keydown', function(e) {
    var lb = document.getElementById('lightbox');
    if (!lb || !lb.classList.contains('open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navLightbox(-1);
    if (e.key === 'ArrowRight') navLightbox(1);
});

// ═══════ TOAST ═══════
function showToast(msg) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(function() { t.classList.remove('show'); }, 3000);
}
</script>

</body>
</html>

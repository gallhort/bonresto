<?php
/**
 * WIDGET EMBED - Standalone embeddable restaurant card
 * This is a FULL HTML page (not wrapped in app.php layout)
 *
 * Variables:
 *   $restaurant - array with id, nom, ville, note_globale, nb_avis, slug
 *   $reviews    - array of latest reviews (up to 3), each with auteur/prenom, note, commentaire
 *   $theme      - 'light' or 'dark'
 *   $widget     - array with optional config (border, max_reviews, etc.)
 */

$r = $restaurant ?? [];
$revs = $reviews ?? [];
$isDark = ($theme ?? 'light') === 'dark';
$maxReviews = min((int)($widget['max_reviews'] ?? 3), 3);

$rating = (float)($r['note_globale'] ?? 0);
$nbAvis = (int)($r['nb_avis'] ?? 0);
$restoUrl = '/restaurant/' . ($r['id'] ?? 0);

// Build star string
function lbr_widget_stars(float $rating): string {
    $out = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= round($rating) ? "\u{2605}" : "\u{2606}";
    }
    return $out;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($r['nom'] ?? 'Restaurant') ?> - LeBonResto Widget</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #00635a;
            --primary-light: #e6f2f0;
            --accent: #f59e0b;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-600: #6b7280;
            --white: #fff;
            --radius: 12px;
            --shadow: 0 1px 3px rgba(0,0,0,.1);
            --shadow-lg: 0 4px 12px rgba(0,0,0,.12);
        }

        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Barlow', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: transparent;
            padding: 0;
            margin: 0;
        }

        /* ---- LIGHT THEME ---- */
        .lbr-widget {
            max-width: 400px;
            width: 100%;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            background: var(--white);
            color: #1f2937;
            border: 1px solid var(--gray-200);
        }

        /* ---- DARK THEME ---- */
        .lbr-widget--dark {
            background: #1a1a2e;
            color: #e5e7eb;
            border-color: #2d2d44;
        }

        .lbr-widget--dark .lbr-widget__city {
            color: #9ca3af;
        }

        .lbr-widget--dark .lbr-widget__rating-count {
            color: #9ca3af;
        }

        .lbr-widget--dark .lbr-widget__review {
            border-bottom-color: #2d2d44;
        }

        .lbr-widget--dark .lbr-widget__review-author {
            color: #d1d5db;
        }

        .lbr-widget--dark .lbr-widget__review-text {
            color: #9ca3af;
        }

        .lbr-widget--dark .lbr-widget__footer {
            background: #16162b;
            border-top-color: #2d2d44;
        }

        .lbr-widget--dark .lbr-widget__powered {
            color: #6b7280;
        }

        /* ---- HEADER ---- */
        .lbr-widget__header {
            padding: 20px 20px 16px;
        }

        .lbr-widget__name {
            font-size: 18px;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .lbr-widget__city {
            font-size: 13px;
            color: var(--gray-600);
            margin-bottom: 12px;
        }

        .lbr-widget__rating {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lbr-widget__stars {
            font-size: 18px;
            color: var(--accent);
            letter-spacing: 1px;
            line-height: 1;
        }

        .lbr-widget__rating-score {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
        }

        .lbr-widget--dark .lbr-widget__rating-score {
            color: #4ade80;
        }

        .lbr-widget__rating-count {
            font-size: 13px;
            color: var(--gray-600);
        }

        /* ---- REVIEWS ---- */
        .lbr-widget__reviews {
            padding: 0 20px;
        }

        .lbr-widget__review {
            padding: 12px 0;
            border-bottom: 1px solid var(--gray-100);
        }

        .lbr-widget__review:last-child {
            border-bottom: none;
        }

        .lbr-widget__review-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .lbr-widget__review-author {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
        }

        .lbr-widget__review-stars {
            font-size: 12px;
            color: var(--accent);
            letter-spacing: 0.5px;
        }

        .lbr-widget__review-text {
            font-size: 13px;
            color: var(--gray-600);
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .lbr-widget__no-reviews {
            padding: 16px 0;
            font-size: 13px;
            color: var(--gray-600);
            text-align: center;
            font-style: italic;
        }

        /* ---- FOOTER ---- */
        .lbr-widget__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            background: var(--gray-100);
            border-top: 1px solid var(--gray-200);
            margin-top: 4px;
        }

        .lbr-widget__cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 18px;
            background: var(--primary);
            color: var(--white);
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s, transform 0.15s;
        }

        .lbr-widget__cta:hover {
            background: #004d44;
            transform: translateY(-1px);
        }

        .lbr-widget__cta-arrow {
            font-size: 14px;
            transition: transform 0.2s;
        }

        .lbr-widget__cta:hover .lbr-widget__cta-arrow {
            transform: translateX(2px);
        }

        .lbr-widget__powered {
            font-size: 11px;
            color: var(--gray-600);
        }

        .lbr-widget__powered a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .lbr-widget--dark .lbr-widget__powered a {
            color: #4ade80;
        }

        .lbr-widget__powered a:hover {
            text-decoration: underline;
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 420px) {
            .lbr-widget__header {
                padding: 16px 16px 12px;
            }
            .lbr-widget__reviews {
                padding: 0 16px;
            }
            .lbr-widget__footer {
                padding: 12px 16px;
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="lbr-widget<?= $isDark ? ' lbr-widget--dark' : '' ?>">
    <!-- Header: Name, City, Rating -->
    <div class="lbr-widget__header">
        <div class="lbr-widget__name"><?= htmlspecialchars($r['nom'] ?? 'Restaurant') ?></div>
        <?php if (!empty($r['ville'])): ?>
            <div class="lbr-widget__city"><?= htmlspecialchars($r['ville']) ?></div>
        <?php endif; ?>

        <div class="lbr-widget__rating">
            <span class="lbr-widget__stars"><?= lbr_widget_stars($rating) ?></span>
            <span class="lbr-widget__rating-score"><?= number_format($rating, 1) ?></span>
            <span class="lbr-widget__rating-count">(<?= $nbAvis ?> avis)</span>
        </div>
    </div>

    <!-- Reviews -->
    <?php if (!empty($revs)): ?>
        <div class="lbr-widget__reviews">
            <?php foreach (array_slice($revs, 0, $maxReviews) as $rev): ?>
                <div class="lbr-widget__review">
                    <div class="lbr-widget__review-header">
                        <span class="lbr-widget__review-author"><?= htmlspecialchars($rev['prenom'] ?? $rev['auteur'] ?? 'Anonyme') ?></span>
                        <span class="lbr-widget__review-stars"><?= lbr_widget_stars((float)($rev['note'] ?? 0)) ?></span>
                    </div>
                    <?php
                        $comment = $rev['commentaire'] ?? '';
                        $excerpt = mb_strlen($comment) > 100 ? mb_substr($comment, 0, 100) . '...' : $comment;
                    ?>
                    <?php if ($excerpt): ?>
                        <div class="lbr-widget__review-text"><?= htmlspecialchars($excerpt) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="lbr-widget__reviews">
            <div class="lbr-widget__no-reviews">Aucun avis pour le moment</div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="lbr-widget__footer">
        <a class="lbr-widget__cta" href="<?= htmlspecialchars($restoUrl) ?>" target="_blank" rel="noopener">
            Voir sur LeBonResto
            <span class="lbr-widget__cta-arrow">&rarr;</span>
        </a>
        <span class="lbr-widget__powered">
            par <a href="/" target="_blank" rel="noopener">LeBonResto</a>
        </span>
    </div>
</div>

</body>
</html>

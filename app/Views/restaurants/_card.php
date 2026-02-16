<a class="resto-card"
     data-id="<?= $resto['id'] ?>"
     data-lat="<?= $resto['gps_latitude'] ?>"
     data-lng="<?= $resto['gps_longitude'] ?>"
     href="/restaurant/<?= $resto['id'] ?>">

    <div class="resto-photo">
        <?php if(!empty($resto['main_photo'])): ?>
            <img loading="lazy" src="/<?= htmlspecialchars($resto['main_photo']) ?>" alt="<?= htmlspecialchars($resto['nom']) ?>">
        <?php else: ?>
            <div class="resto-no-photo">🍽️</div>
        <?php endif; ?>

        <button class="cw-card-btn" data-id="<?= $resto['id'] ?>"
                onclick="event.preventDefault(); event.stopPropagation(); cwToggleResto(<?= $resto['id'] ?>, '<?= addslashes(htmlspecialchars($resto['nom'], ENT_QUOTES)) ?>', '<?= addslashes($resto['main_photo'] ?? '') ?>', '<?= addslashes(htmlspecialchars($resto['ville'] ?? '', ENT_QUOTES)) ?>');"
                title="Ajouter au comparateur">
            <i class="fas fa-balance-scale"></i>
        </button>

        <button class="wishlist-btn" onclick="event.preventDefault(); event.stopPropagation();" aria-label="Ajouter aux favoris">
            <i class="far fa-heart" aria-hidden="true"></i>
        </button>

        <!-- Icône marker (vue carte uniquement) -->
        <button class="map-marker-btn" onclick="event.preventDefault(); event.stopPropagation();" aria-label="Voir sur la carte">
            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
        </button>
    </div>

    <div class="resto-info">
        <div class="resto-info-top">
            <?php if(!empty($resto['ville'])): ?>
                <span class="resto-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($resto['ville']) ?><?php if(!empty($resto['distance'])): ?> · <?= number_format($resto['distance'], 1) ?> km<?php endif; ?></span>
            <?php endif; ?>

            <h3 class="resto-name"><?= htmlspecialchars($resto['nom']) ?></h3>

            <div class="resto-meta">
                <?php if($resto['type_cuisine']): ?>
                    <?= htmlspecialchars($resto['type_cuisine']) ?>
                <?php endif; ?>
                <?php if($resto['price_range']): ?>
                    · <?= htmlspecialchars($resto['price_range']) ?>
                <?php endif; ?>
                <?php if (!empty($resto['cuisine_rank'])): ?>
                    · <span class="resto-rank"><i class="fas fa-trophy"></i> N°<?= $resto['cuisine_rank']['rank'] ?> / <?= $resto['cuisine_rank']['total'] ?> <?= htmlspecialchars($resto['cuisine_rank']['cuisine']) ?> &mdash; <?= htmlspecialchars($resto['cuisine_rank']['region']) ?></span>
                <?php endif; ?>
            </div>

            <?php
            $awardLabel = match($resto['top_award'] ?? null) {
                'travelers_choice' => "Travelers' Choice",
                'top_city' => 'Top ' . htmlspecialchars($resto['ville'] ?? ''),
                'best_cuisine' => 'Meilleur ' . htmlspecialchars($resto['type_cuisine'] ?? ''),
                'trending' => 'Tendance',
                'newcomer' => 'Nouveau',
                default => null,
            };
            ?>
            <?php if ($awardLabel): ?>
                <span class="resto-award-tag"><i class="fas fa-trophy"></i> <?= $awardLabel ?></span>
            <?php endif; ?>
            <?php if (!empty($resto['orders_enabled']) && !empty($resto['owner_id'])): ?>
                <span class="resto-award-tag" style="background:rgba(0,99,90,0.85)"><i class="fas fa-shopping-bag"></i> Commande en ligne</span>
            <?php endif; ?>
        </div>

        <div class="resto-info-bottom">
            <?php $cappedNote = min($resto['note_moyenne'], 5); ?>
            <?php if($cappedNote > 0): ?>
                <div class="resto-rating">
                    <span class="rating-value"><?= number_format($cappedNote, 1) ?></span>
                    <span class="rating-label">
                        <?php
                        if ($cappedNote >= 4.5) echo 'Excellent';
                        elseif ($cappedNote >= 4.0) echo 'Très bien';
                        elseif ($cappedNote >= 3.0) echo 'Bien';
                        elseif ($cappedNote >= 2.0) echo 'Moyen';
                        else echo 'Décevant';
                        ?>
                    </span>
                    <span class="reviews-count"><?= $resto['nb_avis'] ?> avis</span>
                </div>
            <?php else: ?>
                <div class="resto-no-rating">
                    <i class="far fa-star"></i> Pas encore de note &mdash; soyez le premier !
                </div>
            <?php endif; ?>

            <?php if (!empty($resto['reviews_titles'])): ?>
                <div class="card-reviews">
                    <?php foreach ($resto['reviews_titles'] as $titlerev): ?>
                        <p class="review-title">"<?= htmlspecialchars($titlerev, ENT_QUOTES, 'UTF-8') ?>"</p>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="resto-no-reviews">
                    <i class="far fa-comment"></i> Aucun avis pour le moment &mdash; partagez votre exp&eacute;rience !
                </div>
            <?php endif; ?>
        </div>
    </div>
</a>

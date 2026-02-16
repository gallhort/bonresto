<?php
$currentUser = $_SESSION['user'] ?? null;
?>

<section class="leaderboard-page">
    <div class="leaderboard-container">

        <!-- Header -->
        <div class="lb-header">
            <h1><i class="fas fa-trophy"></i> Classement des contributeurs</h1>
            <p>Decouvrez les membres les plus actifs de la communaute LeBonResto<?= !empty($currentVille) ? ' a <strong>' . htmlspecialchars($currentVille) . '</strong>' : '' ?>.</p>
        </div>

        <!-- Filters bar -->
        <div class="lb-filters">
            <!-- Period toggle -->
            <div class="lb-period-toggle">
                <a href="?<?= !empty($currentVille) ? 'ville=' . urlencode($currentVille) . '&' : '' ?>period=month"
                   class="lb-period-btn <?= ($currentPeriod === 'month') ? 'active' : '' ?>">
                    <i class="fas fa-calendar-alt"></i> Ce mois
                </a>
                <a href="?<?= !empty($currentVille) ? 'ville=' . urlencode($currentVille) . '&' : '' ?>period=all"
                   class="lb-period-btn <?= ($currentPeriod === 'all') ? 'active' : '' ?>">
                    <i class="fas fa-infinity"></i> Tout temps
                </a>
            </div>

            <!-- City chips -->
            <div class="lb-city-chips">
                <a href="/classement?period=<?= htmlspecialchars($currentPeriod) ?>"
                   class="lb-city-chip <?= empty($currentVille) ? 'active' : '' ?>">
                    Toutes les villes
                </a>
                <?php foreach ($cities as $city): ?>
                    <a href="/classement?ville=<?= urlencode($city['ville']) ?>&period=<?= htmlspecialchars($currentPeriod) ?>"
                       class="lb-city-chip <?= ($currentVille === $city['ville']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($city['ville']) ?>
                        <span class="chip-count"><?= (int)$city['contributors'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (empty($leaderboard)): ?>
            <!-- Empty state -->
            <div class="lb-empty">
                <i class="fas fa-users-slash"></i>
                <h3>Aucun contributeur trouve</h3>
                <p>Aucun avis approuve <?= ($currentPeriod === 'month') ? 'ce mois-ci' : '' ?><?= !empty($currentVille) ? ' pour ' . htmlspecialchars($currentVille) : '' ?>. Soyez le premier !</p>
                <a href="/restaurants" class="lb-cta-btn">Ecrire un avis</a>
            </div>
        <?php else: ?>

            <!-- Podium: Top 3 -->
            <?php if (count($leaderboard) >= 3): ?>
            <div class="lb-podium">
                <?php
                $podiumOrder = [1, 0, 2]; // Silver, Gold, Bronze display order
                $medals = ['gold', 'silver', 'bronze'];
                $medalIcons = ['&#129351;', '&#129352;', '&#129353;'];
                foreach ($podiumOrder as $displayIdx => $rankIdx):
                    $user = $leaderboard[$rankIdx];
                    $initial = strtoupper(mb_substr($user['prenom'], 0, 1));
                    $medalClass = $medals[$rankIdx];
                ?>
                <div class="lb-podium-card <?= $medalClass ?> <?= ($rankIdx === 0) ? 'first' : '' ?>">
                    <div class="podium-medal"><?= $medalIcons[$rankIdx] ?></div>
                    <div class="podium-rank">#<?= $rankIdx + 1 ?></div>
                    <a href="/user/<?= (int)$user['id'] ?>" class="podium-avatar-link">
                        <?php if (!empty($user['photo_profil'])): ?>
                            <img src="<?= htmlspecialchars($user['photo_profil']) ?>" alt="" class="podium-avatar">
                        <?php else: ?>
                            <div class="podium-avatar podium-avatar-letter"><?= $initial ?></div>
                        <?php endif; ?>
                    </a>
                    <a href="/user/<?= (int)$user['id'] ?>" class="podium-name">
                        <?= htmlspecialchars($user['prenom'] . ' ' . mb_substr($user['nom'], 0, 1) . '.') ?>
                    </a>
                    <?php if (!empty($user['badge'])): ?>
                        <span class="podium-badge"><?= htmlspecialchars($user['badge']) ?></span>
                    <?php endif; ?>
                    <div class="podium-stats">
                        <div class="podium-stat">
                            <strong><?= (int)$user['review_count'] ?></strong>
                            <span>avis</span>
                        </div>
                        <div class="podium-stat">
                            <strong><?= (int)$user['photo_count'] ?></strong>
                            <span>photos</span>
                        </div>
                        <div class="podium-stat">
                            <strong><?= (int)$user['restaurants_visited'] ?></strong>
                            <span>restos</span>
                        </div>
                    </div>
                    <?php if (!empty($user['avg_rating'])): ?>
                        <div class="podium-avg">
                            <i class="fas fa-star"></i> <?= number_format((float)$user['avg_rating'], 1) ?> moy.
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Leaderboard table: Ranks 4-30 (or 1-30 if less than 3) -->
            <?php
            $startIndex = (count($leaderboard) >= 3) ? 3 : 0;
            $tableEntries = array_slice($leaderboard, $startIndex);
            ?>
            <?php if (!empty($tableEntries)): ?>
            <div class="lb-table-wrapper">
                <table class="lb-table">
                    <thead>
                        <tr>
                            <th class="col-rank">#</th>
                            <th class="col-user">Contributeur</th>
                            <th class="col-stat">Avis</th>
                            <th class="col-stat">Photos</th>
                            <th class="col-stat hide-mobile">Restos visites</th>
                            <th class="col-stat hide-mobile">Note moy.</th>
                            <th class="col-stat hide-mobile">Points</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tableEntries as $idx => $user):
                            $rank = $startIndex + $idx + 1;
                            $initial = strtoupper(mb_substr($user['prenom'], 0, 1));
                            $isMe = $currentUser && ((int)$user['id'] === (int)$currentUser['id']);
                        ?>
                        <tr class="<?= $isMe ? 'lb-row-me' : '' ?>">
                            <td class="col-rank">
                                <span class="rank-number"><?= $rank ?></span>
                            </td>
                            <td class="col-user">
                                <a href="/user/<?= (int)$user['id'] ?>" class="lb-user-cell">
                                    <?php if (!empty($user['photo_profil'])): ?>
                                        <img src="<?= htmlspecialchars($user['photo_profil']) ?>" alt="" class="lb-avatar">
                                    <?php else: ?>
                                        <div class="lb-avatar lb-avatar-letter"><?= $initial ?></div>
                                    <?php endif; ?>
                                    <div class="lb-user-info">
                                        <span class="lb-user-name">
                                            <?= htmlspecialchars($user['prenom'] . ' ' . mb_substr($user['nom'], 0, 1) . '.') ?>
                                            <?php if ($isMe): ?><span class="lb-you-tag">Vous</span><?php endif; ?>
                                        </span>
                                        <?php if (!empty($user['badge'])): ?>
                                            <span class="lb-user-badge"><?= htmlspecialchars($user['badge']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </td>
                            <td class="col-stat"><strong><?= (int)$user['review_count'] ?></strong></td>
                            <td class="col-stat"><?= (int)$user['photo_count'] ?></td>
                            <td class="col-stat hide-mobile"><?= (int)$user['restaurants_visited'] ?></td>
                            <td class="col-stat hide-mobile">
                                <?php if (!empty($user['avg_rating'])): ?>
                                    <i class="fas fa-star" style="color:#f59e0b;font-size:11px;"></i> <?= number_format((float)$user['avg_rating'], 1) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="col-stat hide-mobile"><?= number_format((int)$user['points']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <!-- My rank (if authenticated and not in top 30) -->
            <?php if ($currentUser && $myRank === null): ?>
            <div class="lb-my-rank-outside">
                <i class="fas fa-info-circle"></i>
                Vous n'apparaissez pas encore dans le top 30. Continuez a contribuer pour monter dans le classement !
                <a href="/restaurants" class="lb-cta-inline">Ecrire un avis</a>
            </div>
            <?php elseif ($currentUser && $myRank !== null): ?>
            <div class="lb-my-rank-inside">
                <i class="fas fa-medal"></i>
                Vous etes <strong>#<?= $myRank ?></strong> dans ce classement. <?= $myRank <= 3 ? 'Bravo !' : 'Continuez comme ca !' ?>
            </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>

<style>
/* ═══════════════════════════════════════════════════════════════════ */
/* LEADERBOARD PAGE                                                    */
/* ═══════════════════════════════════════════════════════════════════ */

.leaderboard-page {
    min-height: 80vh;
    background: linear-gradient(180deg, #f0fdf4 0%, #f9fafb 40%);
    padding: 40px 20px 80px;
}

.leaderboard-container {
    max-width: 900px;
    margin: 0 auto;
}

/* Header */
.lb-header {
    text-align: center;
    margin-bottom: 32px;
}

.lb-header h1 {
    font-size: 32px;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 8px;
}

.lb-header h1 i {
    color: #f59e0b;
    margin-right: 8px;
}

.lb-header p {
    font-size: 16px;
    color: #6b7280;
    max-width: 500px;
    margin: 0 auto;
}

/* Filters */
.lb-filters {
    margin-bottom: 36px;
}

.lb-period-toggle {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 16px;
}

.lb-period-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 24px;
    border-radius: 24px;
    font-size: 14px;
    font-weight: 600;
    color: #4b5563;
    background: white;
    border: 2px solid #e5e7eb;
    text-decoration: none;
    transition: all 0.2s;
}

.lb-period-btn:hover {
    border-color: #00635a;
    color: #00635a;
}

.lb-period-btn.active {
    background: #00635a;
    color: white;
    border-color: #00635a;
}

.lb-city-chips {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}

.lb-city-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #374151;
    background: white;
    border: 1px solid #e5e7eb;
    text-decoration: none;
    transition: all 0.2s;
}

.lb-city-chip:hover {
    border-color: #00635a;
    color: #00635a;
    background: #f0fdf4;
}

.lb-city-chip.active {
    background: #00635a;
    color: white;
    border-color: #00635a;
}

.lb-city-chip.active .chip-count {
    background: rgba(255,255,255,0.25);
    color: white;
}

.chip-count {
    background: #f3f4f6;
    color: #6b7280;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 10px;
}

/* Empty state */
.lb-empty {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.lb-empty i {
    font-size: 48px;
    color: #d1d5db;
    margin-bottom: 16px;
}

.lb-empty h3 {
    font-size: 20px;
    color: #374151;
    margin-bottom: 8px;
}

.lb-empty p {
    color: #6b7280;
    margin-bottom: 24px;
}

.lb-cta-btn {
    display: inline-block;
    padding: 12px 32px;
    background: #00635a;
    color: white;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: background 0.2s;
}

.lb-cta-btn:hover {
    background: #004d46;
}

/* ═══════════════════════════════════════════════════════════════════ */
/* PODIUM                                                              */
/* ═══════════════════════════════════════════════════════════════════ */

.lb-podium {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-bottom: 32px;
    align-items: end;
}

.lb-podium-card {
    background: white;
    border-radius: 16px;
    padding: 24px 16px;
    text-align: center;
    position: relative;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: transform 0.2s, box-shadow 0.2s;
}

.lb-podium-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1);
}

.lb-podium-card.first {
    padding: 32px 16px;
}

/* Gold */
.lb-podium-card.gold {
    border: 2px solid #f59e0b;
    background: linear-gradient(180deg, #fffbeb 0%, #ffffff 40%);
}

/* Silver */
.lb-podium-card.silver {
    border: 2px solid #9ca3af;
    background: linear-gradient(180deg, #f9fafb 0%, #ffffff 40%);
}

/* Bronze */
.lb-podium-card.bronze {
    border: 2px solid #d97706;
    background: linear-gradient(180deg, #fffbeb 0%, #ffffff 40%);
}

.podium-medal {
    font-size: 36px;
    margin-bottom: 4px;
}

.lb-podium-card.first .podium-medal {
    font-size: 44px;
}

.podium-rank {
    font-size: 13px;
    font-weight: 700;
    color: #9ca3af;
    margin-bottom: 12px;
}

.podium-avatar-link {
    display: inline-block;
    text-decoration: none;
}

.podium-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    object-fit: cover;
    margin: 0 auto 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.lb-podium-card.first .podium-avatar {
    width: 88px;
    height: 88px;
}

img.podium-avatar {
    display: block;
}

.podium-avatar-letter {
    background: linear-gradient(135deg, #00635a, #00897b);
    color: white;
    font-size: 28px;
    font-weight: 700;
}

.lb-podium-card.first .podium-avatar-letter {
    font-size: 34px;
}

.podium-name {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #1f2937;
    text-decoration: none;
    margin-bottom: 4px;
}

.podium-name:hover {
    color: #00635a;
}

.lb-podium-card.first .podium-name {
    font-size: 18px;
}

.podium-badge {
    display: inline-block;
    font-size: 11px;
    font-weight: 600;
    color: #00635a;
    background: #ecfdf5;
    padding: 3px 10px;
    border-radius: 10px;
    margin-bottom: 12px;
}

.podium-stats {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 12px;
}

.podium-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.podium-stat strong {
    font-size: 18px;
    font-weight: 800;
    color: #1f2937;
}

.podium-stat span {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
}

.podium-avg {
    margin-top: 8px;
    font-size: 13px;
    color: #f59e0b;
    font-weight: 600;
}

.podium-avg i {
    font-size: 12px;
}

/* ═══════════════════════════════════════════════════════════════════ */
/* TABLE                                                               */
/* ═══════════════════════════════════════════════════════════════════ */

.lb-table-wrapper {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    overflow: hidden;
    margin-bottom: 24px;
}

.lb-table {
    width: 100%;
    border-collapse: collapse;
}

.lb-table thead {
    background: #f9fafb;
}

.lb-table th {
    padding: 14px 16px;
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: left;
    border-bottom: 1px solid #f3f4f6;
}

.lb-table th.col-stat,
.lb-table td.col-stat {
    text-align: center;
}

.lb-table th.col-rank,
.lb-table td.col-rank {
    width: 60px;
    text-align: center;
}

.lb-table tbody tr {
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.15s;
}

.lb-table tbody tr:hover {
    background: #f9fafb;
}

.lb-table tbody tr:last-child {
    border-bottom: none;
}

.lb-table td {
    padding: 14px 16px;
    font-size: 14px;
    color: #374151;
    vertical-align: middle;
}

.rank-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #f3f4f6;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
}

.lb-row-me {
    background: #f0fdf4 !important;
}

.lb-row-me .rank-number {
    background: #00635a;
    color: white;
}

.lb-user-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: inherit;
}

.lb-user-cell:hover .lb-user-name {
    color: #00635a;
}

.lb-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

img.lb-avatar {
    display: block;
}

.lb-avatar-letter {
    background: linear-gradient(135deg, #00635a, #00897b);
    color: white;
    font-size: 16px;
    font-weight: 700;
}

.lb-user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.lb-user-name {
    font-weight: 600;
    font-size: 14px;
    color: #1f2937;
    transition: color 0.2s;
}

.lb-you-tag {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    color: #00635a;
    background: #ecfdf5;
    padding: 1px 8px;
    border-radius: 8px;
    margin-left: 6px;
    vertical-align: middle;
}

.lb-user-badge {
    font-size: 11px;
    color: #9ca3af;
    font-weight: 500;
}

/* ═══════════════════════════════════════════════════════════════════ */
/* MY RANK BAR                                                         */
/* ═══════════════════════════════════════════════════════════════════ */

.lb-my-rank-outside,
.lb-my-rank-inside {
    background: white;
    border: 2px dashed #d1d5db;
    border-radius: 12px;
    padding: 16px 24px;
    text-align: center;
    font-size: 14px;
    color: #6b7280;
}

.lb-my-rank-outside i,
.lb-my-rank-inside i {
    color: #00635a;
    margin-right: 6px;
}

.lb-my-rank-inside {
    border-color: #00635a;
    border-style: solid;
    background: #f0fdf4;
    color: #1f2937;
}

.lb-my-rank-inside strong {
    color: #00635a;
}

.lb-cta-inline {
    color: #00635a;
    font-weight: 600;
    text-decoration: underline;
    margin-left: 4px;
}

/* ═══════════════════════════════════════════════════════════════════ */
/* RESPONSIVE                                                          */
/* ═══════════════════════════════════════════════════════════════════ */

@media (max-width: 768px) {
    .leaderboard-page {
        padding: 24px 12px 60px;
    }

    .lb-header h1 {
        font-size: 24px;
    }

    .lb-header p {
        font-size: 14px;
    }

    .lb-podium {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .lb-podium-card {
        padding: 20px 16px;
    }

    .lb-podium-card.first {
        order: -1;
    }

    .lb-podium-card.silver {
        order: 0;
    }

    .lb-podium-card.bronze {
        order: 1;
    }

    .podium-avatar {
        width: 56px;
        height: 56px;
    }

    .lb-podium-card.first .podium-avatar {
        width: 72px;
        height: 72px;
    }

    .lb-city-chips {
        justify-content: flex-start;
        overflow-x: auto;
        flex-wrap: nowrap;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 8px;
    }

    .lb-city-chip {
        white-space: nowrap;
        flex-shrink: 0;
    }

    .hide-mobile {
        display: none;
    }

    .lb-table td {
        padding: 12px 10px;
        font-size: 13px;
    }

    .lb-table th {
        padding: 12px 10px;
        font-size: 11px;
    }

    .lb-avatar {
        width: 34px;
        height: 34px;
        font-size: 14px;
    }

    .lb-user-name {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .lb-period-toggle {
        flex-direction: column;
        align-items: stretch;
    }

    .lb-period-btn {
        justify-content: center;
    }
}
</style>

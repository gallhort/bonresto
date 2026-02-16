<?php
$flashSuccess = $success ?? $_SESSION['flash_success'] ?? null;
$flashError = !empty($errors) ? (is_array($errors) ? implode('<br>', $errors) : $errors) : ($_SESSION['flash_error'] ?? null);
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
$approvedCount = $approvedCount ?? 0;
$mySuggestions = $suggestions ?? $mySuggestions ?? [];
$old = $old ?? [];
?>

<style>
    /* ===== Page container ===== */
    .suggest-page {
        max-width: 600px;
        margin: 0 auto;
        padding: 28px 16px 60px;
    }

    /* ===== Hero ===== */
    .suggest-hero {
        background: linear-gradient(135deg, #00635a 0%, #004d40 50%, #00352e 100%);
        border-radius: 20px;
        padding: 36px 28px 32px;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }
    .suggest-hero::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 180px;
        height: 180px;
        background: rgba(255,255,255,0.06);
        border-radius: 50%;
    }
    .suggest-hero::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: 25%;
        width: 120px;
        height: 120px;
        background: rgba(255,255,255,0.04);
        border-radius: 50%;
    }
    .suggest-hero h1 {
        font-size: 26px;
        font-weight: 800;
        color: #fff;
        margin: 0 0 8px;
        letter-spacing: -0.5px;
        position: relative;
        z-index: 1;
    }
    .suggest-hero h1 i {
        margin-right: 8px;
        font-size: 22px;
        opacity: 0.9;
    }
    .suggest-hero p {
        color: rgba(255,255,255,0.75);
        margin: 0;
        font-size: 14px;
        line-height: 1.5;
        position: relative;
        z-index: 1;
    }
    .suggest-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 16px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    .suggest-hero-badge.earned {
        background: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.3);
    }
    .suggest-hero-badge.progress {
        background: rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.85);
        border: 1px solid rgba(255,255,255,0.15);
    }

    /* ===== Rewards card ===== */
    .suggest-rewards {
        background: #fff;
        border-radius: 16px;
        padding: 20px 24px;
        margin-bottom: 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .suggest-rewards-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .suggest-rewards-icon i {
        font-size: 20px;
        color: #00635a;
    }
    .suggest-rewards-body {
        flex: 1;
        min-width: 0;
    }
    .suggest-rewards-pts {
        font-size: 14px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 4px;
    }
    .suggest-rewards-pts span {
        color: #00635a;
    }
    .suggest-rewards-badge {
        font-size: 13px;
        color: #6b7280;
        margin: 0;
    }
    .suggest-rewards-badge.earned {
        color: #b45309;
        font-weight: 700;
    }
    .suggest-rewards-badge.earned i {
        color: #f59e0b;
    }
    .suggest-rewards-progress {
        display: flex;
        gap: 4px;
        margin-top: 6px;
    }
    .suggest-rewards-dot {
        width: 28px;
        height: 6px;
        border-radius: 3px;
        background: #e5e7eb;
    }
    .suggest-rewards-dot.filled {
        background: #00635a;
    }

    /* ===== Flash messages ===== */
    .suggest-flash {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 14px;
        line-height: 1.5;
    }
    .suggest-flash i {
        font-size: 18px;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .suggest-flash-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    .suggest-flash-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    /* ===== Form card ===== */
    .suggest-form-card {
        background: #fff;
        border-radius: 16px;
        padding: 28px 24px 32px;
        margin-bottom: 28px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04);
    }
    .suggest-form-card h2 {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin: 0 0 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .suggest-form-card h2 i {
        color: #00635a;
        font-size: 16px;
    }

    .suggest-form .form-group {
        margin-bottom: 18px;
    }
    .suggest-form .form-group:last-of-type {
        margin-bottom: 24px;
    }
    .suggest-form label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
    }
    .suggest-form label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .suggest-form input[type="text"],
    .suggest-form textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        transition: all 0.2s;
        background: #fafafa;
        color: #111827;
        box-sizing: border-box;
    }
    .suggest-form input[type="text"]:focus,
    .suggest-form textarea:focus {
        outline: none;
        border-color: #00635a;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(0, 99, 90, 0.08);
    }
    .suggest-form input[type="text"]::placeholder,
    .suggest-form textarea::placeholder {
        color: #9ca3af;
    }
    .suggest-form textarea {
        resize: vertical;
        min-height: 80px;
    }

    .suggest-submit {
        width: 100%;
        padding: 14px 28px;
        background: linear-gradient(135deg, #00635a, #004d40);
        color: #fff;
        border: none;
        border-radius: 12px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s;
        font-family: inherit;
        letter-spacing: 0.3px;
    }
    .suggest-submit:hover {
        opacity: 0.92;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(0, 99, 90, 0.35);
    }

    /* ===== History section ===== */
    .suggest-history {
        margin-top: 0;
    }
    .suggest-history-title {
        font-size: 13px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .suggest-history-title i {
        font-size: 12px;
    }

    .suggest-history-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .suggest-history-item {
        background: #fff;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05), 0 2px 8px rgba(0,0,0,0.03);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .suggest-history-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.08), 0 4px 16px rgba(0,0,0,0.05);
    }
    .suggest-history-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 6px;
    }
    .suggest-history-name {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
        min-width: 0;
    }
    .suggest-history-status {
        font-size: 11px;
        font-weight: 700;
        padding: 4px 10px;
        border-radius: 20px;
        flex-shrink: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .status-pending {
        background: #f3f4f6;
        color: #6b7280;
    }
    .status-approved {
        background: #d1fae5;
        color: #065f46;
    }
    .status-rejected {
        background: #fee2e2;
        color: #991b1b;
    }
    .status-duplicate {
        background: #fef3c7;
        color: #92400e;
    }
    .suggest-history-meta {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: #9ca3af;
    }
    .suggest-history-meta i {
        font-size: 11px;
        margin-right: 3px;
    }
    .suggest-history-note {
        margin-top: 10px;
        padding: 10px 14px;
        background: #fef2f2;
        border-radius: 8px;
        border-left: 3px solid #fca5a5;
        font-size: 13px;
        color: #991b1b;
        line-height: 1.5;
    }
    .suggest-history-note i {
        margin-right: 6px;
        font-size: 12px;
    }

    /* ===== Empty state ===== */
    .suggest-empty {
        text-align: center;
        padding: 48px 20px;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .suggest-empty-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
    }
    .suggest-empty-icon i {
        font-size: 24px;
        color: #d1d5db;
    }
    .suggest-empty p {
        color: #9ca3af;
        font-size: 14px;
        margin: 0;
        line-height: 1.5;
    }

    /* ===== Responsive ===== */
    @media (max-width: 480px) {
        .suggest-page {
            padding: 16px 12px 40px;
        }
        .suggest-hero {
            padding: 28px 20px 24px;
            border-radius: 16px;
        }
        .suggest-hero h1 {
            font-size: 22px;
        }
        .suggest-form-card {
            padding: 22px 18px 26px;
        }
        .suggest-rewards {
            padding: 16px 18px;
        }
        .suggest-history-item {
            padding: 14px 16px;
        }
        .suggest-history-top {
            flex-wrap: wrap;
            gap: 8px;
        }
    }
</style>

<div class="suggest-page">

    <!-- ===== HERO ===== -->
    <div class="suggest-hero">
        <h1><i class="fas fa-lightbulb"></i> <?= htmlspecialchars($title ?? 'Proposer un restaurant') ?></h1>
        <p>Vous connaissez un bon restaurant qui n'est pas encore sur LeBonResto ? Partagez-le avec la communaute !</p>
        <p style="font-size: 13px; color: rgba(255,255,255,0.55); margin-top: 8px; position: relative; z-index: 1;">Vous etes proprietaire ? <a href="/add-restaurant" style="color: rgba(255,255,255,0.85); text-decoration: underline;">Inscrivez votre restaurant ici</a></p>
        <?php if ($approvedCount >= 3): ?>
            <div class="suggest-hero-badge earned">
                <i class="fas fa-award"></i> Badge Eclaireur obtenu !
            </div>
        <?php elseif ($approvedCount > 0): ?>
            <div class="suggest-hero-badge progress">
                <i class="fas fa-compass"></i> <?= (int)$approvedCount ?>/3 vers le badge Eclaireur
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== REWARDS INFO ===== -->
    <div class="suggest-rewards">
        <div class="suggest-rewards-icon">
            <i class="fas fa-coins"></i>
        </div>
        <div class="suggest-rewards-body">
            <p class="suggest-rewards-pts"><span>+10 pts</span> par suggestion, <span>+50 pts</span> si validee</p>
            <?php if ($approvedCount >= 3): ?>
                <p class="suggest-rewards-badge earned">
                    <i class="fas fa-trophy"></i> Badge Eclaireur obtenu !
                </p>
            <?php else: ?>
                <p class="suggest-rewards-badge">
                    <?= (int)$approvedCount ?>/3 suggestions validees pour le badge Eclaireur
                </p>
                <div class="suggest-rewards-progress">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="suggest-rewards-dot <?= $i < $approvedCount ? 'filled' : '' ?>"></div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===== FLASH MESSAGES ===== -->
    <?php if ($flashSuccess): ?>
        <div class="suggest-flash suggest-flash-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div class="suggest-flash suggest-flash-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
        </div>
    <?php endif; ?>

    <!-- ===== SUGGESTION FORM ===== -->
    <div class="suggest-form-card">
        <h2><i class="fas fa-utensils"></i> Informations du restaurant</h2>
        <form action="/proposer-restaurant" method="POST" class="suggest-form">

            <div class="form-group">
                <label for="suggest-nom">Nom du restaurant <span class="required">*</span></label>
                <input type="text" id="suggest-nom" name="nom" required maxlength="255" placeholder="Le nom du restaurant" value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="suggest-ville">Ville <span class="required">*</span></label>
                <input type="text" id="suggest-ville" name="ville" required maxlength="100" placeholder="Ex: Alger, Oran, Constantine..." value="<?= htmlspecialchars($old['ville'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="suggest-cuisine">Type de cuisine</label>
                <input type="text" id="suggest-cuisine" name="type_cuisine" maxlength="100" placeholder="Ex: Burger, Pizza, Cuisine algerienne..." value="<?= htmlspecialchars($old['type_cuisine'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="suggest-adresse">Adresse</label>
                <input type="text" id="suggest-adresse" name="adresse" maxlength="255" placeholder="Adresse ou quartier du restaurant" value="<?= htmlspecialchars($old['adresse'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="suggest-raison">Pourquoi le recommandez-vous ?</label>
                <textarea id="suggest-raison" name="pourquoi" rows="3" maxlength="1000" placeholder="Ce qui rend ce restaurant special..."><?= htmlspecialchars($old['pourquoi'] ?? '') ?></textarea>
            </div>

            <button type="submit" class="suggest-submit">
                <i class="fas fa-paper-plane"></i> Proposer ce restaurant
            </button>
        </form>
    </div>

    <!-- ===== HISTORY ===== -->
    <div class="suggest-history">
        <p class="suggest-history-title"><i class="fas fa-clock-rotate-left"></i> Mes suggestions</p>

        <?php if (!empty($mySuggestions)): ?>
            <div class="suggest-history-list">
                <?php foreach ($mySuggestions as $suggestion): ?>
                    <div class="suggest-history-item">
                        <div class="suggest-history-top">
                            <div class="suggest-history-name">
                                <?= htmlspecialchars($suggestion['nom']) ?>
                            </div>
                            <?php
                                $status = $suggestion['status'] ?? 'pending';
                                $statusLabels = [
                                    'pending'   => 'En attente',
                                    'approved'  => 'Validee',
                                    'rejected'  => 'Refusee',
                                    'duplicate'  => 'Doublon',
                                ];
                                $statusClass = 'status-' . htmlspecialchars($status);
                                $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                            ?>
                            <span class="suggest-history-status <?= $statusClass ?>">
                                <?= htmlspecialchars($statusLabel) ?>
                            </span>
                        </div>
                        <div class="suggest-history-meta">
                            <?php if (!empty($suggestion['ville'])): ?>
                                <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars($suggestion['ville']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($suggestion['type_cuisine'])): ?>
                                <span><i class="fas fa-bowl-food"></i> <?= htmlspecialchars($suggestion['type_cuisine']) ?></span>
                            <?php endif; ?>
                            <?php if (!empty($suggestion['created_at'])): ?>
                                <span><i class="fas fa-calendar"></i> <?= date('d/m/Y', strtotime($suggestion['created_at'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($status === 'rejected' && !empty($suggestion['admin_note'])): ?>
                            <div class="suggest-history-note">
                                <i class="fas fa-comment-dots"></i>
                                <?= htmlspecialchars($suggestion['admin_note']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="suggest-empty">
                <div class="suggest-empty-icon">
                    <i class="fas fa-seedling"></i>
                </div>
                <p>Vous n'avez pas encore propose de restaurant.<br>Faites votre premiere suggestion !</p>
            </div>
        <?php endif; ?>
    </div>

</div>

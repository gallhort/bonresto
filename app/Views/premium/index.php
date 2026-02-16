<style>
    /* Hero */
    .prm-hero {
        background: linear-gradient(135deg, #00635a 0%, #004d40 60%, #003d33 100%);
        color: #fff;
        padding: 64px 0 80px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .prm-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(245,158,11,0.15), transparent 70%);
        border-radius: 50%;
    }
    .prm-hero-inner { max-width: 700px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 1; }
    .prm-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(245,158,11,0.2);
        color: #f59e0b;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 16px;
    }
    .prm-hero h1 {
        font-size: 36px;
        font-weight: 800;
        margin: 0 0 12px;
    }
    .prm-hero p {
        font-size: 17px;
        opacity: 0.85;
        margin: 0;
        line-height: 1.6;
    }

    /* Toggle */
    .prm-toggle-wrap {
        display: flex;
        justify-content: center;
        margin: -24px 0 32px;
        position: relative;
        z-index: 10;
    }
    .prm-toggle {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #fff;
        padding: 6px 8px;
        border-radius: 50px;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .prm-toggle-btn {
        padding: 10px 22px;
        border: none;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        background: transparent;
        color: #6b7280;
        font-family: inherit;
    }
    .prm-toggle-btn.active {
        background: #00635a;
        color: #fff;
    }
    .prm-toggle-discount {
        background: #fef3c7;
        color: #92400e;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    /* Plans grid */
    .prm-container { max-width: 1100px; margin: 0 auto; padding: 0 20px 60px; }
    .prm-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        align-items: start;
    }

    /* Plan card */
    .prm-card {
        background: #fff;
        border-radius: 16px;
        padding: 32px 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        position: relative;
        transition: transform 0.3s, box-shadow 0.3s;
        border: 2px solid transparent;
    }
    .prm-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .prm-card.featured {
        border-color: #00635a;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
        transform: scale(1.03);
        z-index: 2;
    }
    .prm-card.featured:hover {
        transform: scale(1.03) translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,99,90,0.2);
    }
    .prm-popular-badge {
        position: absolute;
        top: -14px;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #f59e0b, #f97316);
        color: #fff;
        padding: 5px 20px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        box-shadow: 0 2px 8px rgba(245,158,11,0.3);
    }
    .prm-card-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        margin-bottom: 16px;
    }
    .prm-card-name {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 6px;
    }
    .prm-card-desc {
        font-size: 13px;
        color: #6b7280;
        margin: 0 0 20px;
        line-height: 1.5;
    }
    .prm-card-price {
        margin-bottom: 24px;
    }
    .prm-price-amount {
        font-size: 38px;
        font-weight: 800;
        color: #00635a;
        line-height: 1;
    }
    .prm-price-currency {
        font-size: 18px;
        font-weight: 600;
    }
    .prm-price-period {
        font-size: 14px;
        color: #6b7280;
        font-weight: 400;
    }
    .prm-price-yearly {
        display: none;
    }
    .prm-price-monthly { display: block; }

    body.yearly-billing .prm-price-yearly { display: block; }
    body.yearly-billing .prm-price-monthly { display: none; }

    .prm-price-save {
        display: inline-block;
        background: #ecfdf5;
        color: #059669;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        margin-top: 6px;
    }

    /* Features list */
    .prm-features {
        list-style: none;
        margin: 0 0 24px;
        padding: 0;
    }
    .prm-features li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 8px 0;
        font-size: 14px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
    }
    .prm-features li:last-child { border-bottom: none; }
    .prm-features li i {
        margin-top: 2px;
        font-size: 13px;
        flex-shrink: 0;
    }
    .prm-features li i.fa-check { color: #10b981; }
    .prm-features li i.fa-xmark { color: #d1d5db; }
    .prm-features li.disabled { color: #d1d5db; }

    /* CTA */
    .prm-btn-cta {
        display: block;
        width: 100%;
        padding: 14px;
        border: 2px solid #00635a;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        text-align: center;
        transition: all 0.2s;
        text-decoration: none;
        font-family: inherit;
        background: #fff;
        color: #00635a;
    }
    .prm-btn-cta:hover {
        background: #e6f2f0;
    }
    .prm-btn-cta.primary {
        background: #00635a;
        color: #fff;
    }
    .prm-btn-cta.primary:hover { background: #004d40; }
    .prm-btn-cta.current {
        background: #f3f4f6;
        color: #9ca3af;
        border-color: #e5e7eb;
        cursor: default;
        pointer-events: none;
    }

    /* FAQ */
    .prm-faq {
        max-width: 800px;
        margin: 40px auto 0;
        padding: 0 20px;
    }
    .prm-faq h2 {
        text-align: center;
        font-size: 24px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 28px;
    }
    .prm-faq-item {
        background: #fff;
        border-radius: 12px;
        margin-bottom: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        overflow: hidden;
    }
    .prm-faq-q {
        padding: 18px 20px;
        font-size: 15px;
        font-weight: 600;
        color: #111827;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background 0.2s;
        user-select: none;
    }
    .prm-faq-q:hover { background: #f9fafb; }
    .prm-faq-q i {
        font-size: 12px;
        color: #6b7280;
        transition: transform 0.3s;
    }
    .prm-faq-item.open .prm-faq-q i { transform: rotate(180deg); }
    .prm-faq-a {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease;
    }
    .prm-faq-item.open .prm-faq-a { max-height: 300px; }
    .prm-faq-a-inner {
        padding: 0 20px 18px;
        font-size: 14px;
        color: #6b7280;
        line-height: 1.6;
    }

    @media (max-width: 900px) {
        .prm-grid {
            grid-template-columns: 1fr;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }
        .prm-card.featured { transform: none; }
        .prm-card.featured:hover { transform: translateY(-4px); }
        .prm-hero h1 { font-size: 28px; }
    }
</style>

<!-- Hero -->
<div class="prm-hero">
    <div class="prm-hero-inner">
        <div class="prm-hero-badge"><i class="fas fa-crown"></i> Premium</div>
        <h1>LeBonResto Premium</h1>
        <p>Boostez la visibilite de votre restaurant, attirez plus de clients et accedez a des outils exclusifs pour developper votre activite.</p>
    </div>
</div>

<!-- Billing toggle -->
<div class="prm-toggle-wrap">
    <div class="prm-toggle">
        <button class="prm-toggle-btn active" id="toggleMonthly" onclick="setBilling('monthly')">Mensuel</button>
        <button class="prm-toggle-btn" id="toggleYearly" onclick="setBilling('yearly')">Annuel</button>
        <span class="prm-toggle-discount">-20%</span>
    </div>
</div>

<!-- Plans -->
<div class="prm-container">
    <div class="prm-grid">
        <?php
        $planIcons = [
            ['icon' => 'fa-seedling', 'bg' => '#e6f2f0', 'color' => '#00635a'],
            ['icon' => 'fa-rocket', 'bg' => '#fef3c7', 'color' => '#f59e0b'],
            ['icon' => 'fa-gem', 'bg' => '#ede9fe', 'color' => '#8b5cf6'],
        ];
        $planDescs = [
            'Ideal pour demarrer et tester les fonctionnalites de base.',
            'Le choix le plus populaire pour les restaurateurs ambitieux.',
            'Toute la puissance de LeBonResto pour dominer votre marche.',
        ];
        $plans = $plans ?? [];
        foreach ($plans as $i => $plan):
            $isFeatured = $i === 1;
            $iconData = $planIcons[$i] ?? $planIcons[0];
            $desc = $planDescs[$i] ?? '';
            $priceMonthly = $plan['price_monthly'] ?? 0;
            $priceYearly = $plan['price_yearly'] ?? 0;
            $priceYearlyMonth = $priceYearly > 0 ? round($priceYearly / 12) : 0;
            $features = is_string($plan['features'] ?? null) ? json_decode($plan['features'], true) : ($plan['features'] ?? []);
            $isCurrent = !empty($currentSubscription) && ($currentSubscription['plan_id'] ?? null) == ($plan['id'] ?? null);
            $savingsPercent = $priceMonthly > 0 ? round((1 - ($priceYearlyMonth / $priceMonthly)) * 100) : 0;
        ?>
        <div class="prm-card <?= $isFeatured ? 'featured' : '' ?>" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>">
            <?php if ($isFeatured): ?>
                <div class="prm-popular-badge"><i class="fas fa-fire"></i> Populaire</div>
            <?php endif; ?>

            <div class="prm-card-icon" style="background: <?= $iconData['bg'] ?>; color: <?= $iconData['color'] ?>;">
                <i class="fas <?= $iconData['icon'] ?>"></i>
            </div>
            <h3 class="prm-card-name"><?= htmlspecialchars($plan['name'] ?? '') ?></h3>
            <p class="prm-card-desc"><?= $desc ?></p>

            <!-- Price -->
            <div class="prm-card-price">
                <div class="prm-price-monthly">
                    <?php if ($priceMonthly > 0): ?>
                        <span class="prm-price-amount"><?= number_format($priceMonthly, 0, ',', ' ') ?></span>
                        <span class="prm-price-currency">DZD</span>
                        <span class="prm-price-period"> / mois</span>
                    <?php else: ?>
                        <span class="prm-price-amount" style="font-size:28px;">Gratuit</span>
                    <?php endif; ?>
                </div>
                <div class="prm-price-yearly">
                    <?php if ($priceYearly > 0): ?>
                        <span class="prm-price-amount"><?= number_format($priceYearlyMonth, 0, ',', ' ') ?></span>
                        <span class="prm-price-currency">DZD</span>
                        <span class="prm-price-period"> / mois</span>
                        <?php if ($savingsPercent > 0): ?>
                            <div class="prm-price-save"><i class="fas fa-tag"></i> Economisez <?= $savingsPercent ?>%</div>
                        <?php endif; ?>
                    <?php elseif ($priceMonthly == 0): ?>
                        <span class="prm-price-amount" style="font-size:28px;">Gratuit</span>
                    <?php else: ?>
                        <span class="prm-price-amount"><?= number_format($priceYearly, 0, ',', ' ') ?></span>
                        <span class="prm-price-currency">DZD</span>
                        <span class="prm-price-period"> / an</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Features -->
            <?php if (!empty($features)): ?>
            <ul class="prm-features">
                <?php foreach ($features as $feat): ?>
                    <?php
                        $isIncluded = true;
                        $featText = $feat;
                        if (is_array($feat)) {
                            $featText = $feat['text'] ?? $feat['label'] ?? '';
                            $isIncluded = $feat['included'] ?? true;
                        }
                    ?>
                    <li class="<?= $isIncluded ? '' : 'disabled' ?>">
                        <i class="fas <?= $isIncluded ? 'fa-check' : 'fa-xmark' ?>"></i>
                        <span><?= htmlspecialchars($featText) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <!-- CTA -->
            <?php if ($isCurrent): ?>
                <button class="prm-btn-cta current"><i class="fas fa-check-circle"></i> Plan actuel</button>
            <?php elseif ($priceMonthly == 0): ?>
                <a href="/premium/subscribe/<?= htmlspecialchars($plan['slug'] ?? '') ?>" class="prm-btn-cta">Commencer gratuitement</a>
            <?php else: ?>
                <a href="/premium/subscribe/<?= htmlspecialchars($plan['slug'] ?? '') ?>" class="prm-btn-cta <?= $isFeatured ? 'primary' : '' ?>">
                    Choisir ce plan
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- FAQ -->
    <div class="prm-faq">
        <h2>Questions frequentes</h2>

        <div class="prm-faq-item">
            <div class="prm-faq-q" onclick="toggleFaq(this)">
                Puis-je changer de plan a tout moment ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="prm-faq-a">
                <div class="prm-faq-a-inner">
                    Oui, vous pouvez passer a un plan superieur ou inferieur a tout moment. Le changement prendra effet immediatement et la facturation sera ajustee au prorata.
                </div>
            </div>
        </div>

        <div class="prm-faq-item">
            <div class="prm-faq-q" onclick="toggleFaq(this)">
                Y a-t-il un engagement minimum ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="prm-faq-a">
                <div class="prm-faq-a-inner">
                    Non, aucun engagement. Vous pouvez annuler votre abonnement a tout moment. Si vous choisissez la facturation annuelle, vous beneficiez d'une reduction mais pouvez toujours annuler avec un remboursement au prorata.
                </div>
            </div>
        </div>

        <div class="prm-faq-item">
            <div class="prm-faq-q" onclick="toggleFaq(this)">
                Comment fonctionne le boost de visibilite ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="prm-faq-a">
                <div class="prm-faq-a-inner">
                    Votre restaurant apparaitra en priorite dans les resultats de recherche, avec un badge Premium visible par les utilisateurs. Selon votre plan, vous beneficiez aussi de mises en avant sur la page d'accueil et dans les newsletters.
                </div>
            </div>
        </div>

        <div class="prm-faq-item">
            <div class="prm-faq-q" onclick="toggleFaq(this)">
                Les statistiques avancees incluent quoi exactement ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="prm-faq-a">
                <div class="prm-faq-a-inner">
                    Vous avez acces a des tableaux de bord detailles : nombre de vues, clics, taux de conversion, analyse des avis, comparaison avec vos concurrents, et des rapports hebdomadaires par email.
                </div>
            </div>
        </div>

        <div class="prm-faq-item">
            <div class="prm-faq-q" onclick="toggleFaq(this)">
                Quels modes de paiement acceptez-vous ?
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="prm-faq-a">
                <div class="prm-faq-a-inner">
                    Nous acceptons les cartes CIB/Edahabia, les virements bancaires, et le paiement en especes dans nos bureaux. Contactez-nous pour les modalites de paiement.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function setBilling(mode) {
    var monthlyBtn = document.getElementById('toggleMonthly');
    var yearlyBtn = document.getElementById('toggleYearly');

    if (mode === 'yearly') {
        document.body.classList.add('yearly-billing');
        yearlyBtn.classList.add('active');
        monthlyBtn.classList.remove('active');
    } else {
        document.body.classList.remove('yearly-billing');
        monthlyBtn.classList.add('active');
        yearlyBtn.classList.remove('active');
    }
}

function toggleFaq(el) {
    var item = el.closest('.prm-faq-item');
    var wasOpen = item.classList.contains('open');

    // Close all
    document.querySelectorAll('.prm-faq-item.open').forEach(function(i) {
        i.classList.remove('open');
    });

    // Toggle clicked
    if (!wasOpen) {
        item.classList.add('open');
    }
}
</script>

<style>
.st-page { max-width: 1100px; margin: 0 auto; padding: 24px 20px 60px; }
.st-hero { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); border-radius: 20px; padding: 32px 28px; color: #fff; margin-bottom: 24px; position: relative; overflow: hidden; }
.st-hero::before { content: ''; position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.04); border-radius: 50%; }
.st-hero h1 { font-size: 26px; font-weight: 800; margin: 0 0 6px; }
.st-hero p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }

/* KPIs */
.st-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-bottom: 32px; }
.st-kpi { background: #fff; border-radius: 14px; padding: 20px 16px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.st-kpi-icon { width: 44px; height: 44px; border-radius: 12px; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; color: #fff; }
.st-kpi-val { font-size: 28px; font-weight: 900; color: #111827; letter-spacing: -0.5px; }
.st-kpi-label { font-size: 12px; color: #6b7280; margin-top: 2px; }

/* Grids */
.st-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.st-card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.st-card-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }

/* Chart */
.st-chart-wrap { height: 280px; position: relative; }

/* Bar horizontal */
.st-hbar { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.st-hbar-label { font-size: 13px; font-weight: 500; min-width: 100px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.st-hbar-track { flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.st-hbar-fill { height: 100%; border-radius: 4px; transition: width 0.6s; }
.st-hbar-count { font-size: 13px; font-weight: 700; min-width: 40px; text-align: right; }

/* Top restos */
.st-top-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-decoration: none; color: inherit; }
.st-top-item:last-child { border-bottom: none; }
.st-top-item:hover { opacity: 0.8; }
.st-top-rank { width: 28px; height: 28px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #6b7280; flex-shrink: 0; }
.st-top-item:nth-child(-n+3) .st-top-rank { background: linear-gradient(135deg, #7c3aed, #a78bfa); color: #fff; }
.st-top-photo { width: 44px; height: 44px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.st-top-info { flex: 1; min-width: 0; }
.st-top-name { font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.st-top-meta { font-size: 12px; color: #6b7280; }
.st-top-note { font-size: 15px; font-weight: 800; color: #7c3aed; }

/* City cards */
.st-cities { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 24px; }
.st-city-card { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); text-decoration: none; color: inherit; transition: all 0.2s; text-align: center; }
.st-city-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.st-city-name { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 4px; }
.st-city-count { font-size: 13px; color: #6b7280; }
.st-city-note { font-size: 13px; color: #7c3aed; font-weight: 600; }

@media (max-width: 900px) { .st-grid-2 { grid-template-columns: 1fr; } }
@media (max-width: 600px) { .st-kpis { grid-template-columns: repeat(2, 1fr); } .st-cities { grid-template-columns: repeat(2, 1fr); } }
</style>

<div class="st-page">

<div class="st-hero" data-aos="fade-up">
    <h1><i class="fas fa-chart-pie"></i> Statistiques Le Bon Resto</h1>
    <p>Les chiffres cles de la plateforme en Algerie</p>
</div>

<!-- KPIs -->
<div class="st-kpis">
    <div class="st-kpi" data-aos="fade-up">
        <div class="st-kpi-icon" style="background:linear-gradient(135deg,#7c3aed,#a78bfa)"><i class="fas fa-store"></i></div>
        <div class="st-kpi-val"><?= number_format($stats['total_restaurants']) ?></div>
        <div class="st-kpi-label">Restaurants</div>
    </div>
    <div class="st-kpi" data-aos="fade-up" data-aos-delay="50">
        <div class="st-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-star"></i></div>
        <div class="st-kpi-val"><?= number_format($stats['total_avis']) ?></div>
        <div class="st-kpi-label">Avis</div>
    </div>
    <div class="st-kpi" data-aos="fade-up" data-aos-delay="100">
        <div class="st-kpi-icon" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8)"><i class="fas fa-users"></i></div>
        <div class="st-kpi-val"><?= number_format($stats['total_users']) ?></div>
        <div class="st-kpi-label">Utilisateurs</div>
    </div>
    <div class="st-kpi" data-aos="fade-up" data-aos-delay="150">
        <div class="st-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-shopping-bag"></i></div>
        <div class="st-kpi-val"><?= number_format($stats['total_orders']) ?></div>
        <div class="st-kpi-label">Commandes</div>
    </div>
    <div class="st-kpi" data-aos="fade-up" data-aos-delay="200">
        <div class="st-kpi-icon" style="background:linear-gradient(135deg,#ec4899,#db2777)"><i class="fas fa-city"></i></div>
        <div class="st-kpi-val"><?= $stats['nb_villes'] ?></div>
        <div class="st-kpi-label">Villes</div>
    </div>
    <div class="st-kpi" data-aos="fade-up" data-aos-delay="250">
        <div class="st-kpi-icon" style="background:linear-gradient(135deg,#06b6d4,#0891b2)"><i class="fas fa-hiking"></i></div>
        <div class="st-kpi-val"><?= number_format($stats['total_activities']) ?></div>
        <div class="st-kpi-label">Activites</div>
    </div>
</div>

<!-- Charts row -->
<div class="st-grid-2">
    <!-- Top Villes -->
    <div class="st-card" data-aos="fade-up">
        <h3 class="st-card-title"><i class="fas fa-city" style="color:#7c3aed"></i> Top villes</h3>
        <?php
        $maxCityCount = max(array_column($topCities, 'nb_restos') ?: [1]);
        foreach (array_slice($topCities, 0, 10) as $city):
            $pct = round(($city['nb_restos'] / $maxCityCount) * 100);
        ?>
        <div class="st-hbar">
            <a href="/stats/<?= urlencode($city['ville']) ?>" class="st-hbar-label" style="color:#111827;text-decoration:none"><?= htmlspecialchars($city['ville']) ?></a>
            <div class="st-hbar-track"><div class="st-hbar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#7c3aed,#a78bfa)"></div></div>
            <div class="st-hbar-count"><?= $city['nb_restos'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Top Cuisines -->
    <div class="st-card" data-aos="fade-up" data-aos-delay="100">
        <h3 class="st-card-title"><i class="fas fa-utensils" style="color:#f59e0b"></i> Types de cuisine populaires</h3>
        <?php
        $maxCuisine = max(array_column($topCuisines, 'nb_restos') ?: [1]);
        foreach ($topCuisines as $cuis):
            $pct = round(($cuis['nb_restos'] / $maxCuisine) * 100);
        ?>
        <div class="st-hbar">
            <div class="st-hbar-label"><?= htmlspecialchars($cuis['type_cuisine']) ?></div>
            <div class="st-hbar-track"><div class="st-hbar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#f59e0b,#fbbf24)"></div></div>
            <div class="st-hbar-count"><?= $cuis['nb_restos'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="st-grid-2">
    <!-- Distribution des notes -->
    <div class="st-card" data-aos="fade-up">
        <h3 class="st-card-title"><i class="fas fa-chart-bar" style="color:#10b981"></i> Distribution des notes</h3>
        <?php
        $totalNotes = array_sum($noteDistribution) ?: 1;
        foreach ([5, 4, 3, 2, 1] as $star):
            $cnt = $noteDistribution[$star] ?? 0;
            $pct = round(($cnt / $totalNotes) * 100);
        ?>
        <div class="st-hbar">
            <div class="st-hbar-label" style="min-width:50px"><?= $star ?> ★</div>
            <div class="st-hbar-track"><div class="st-hbar-fill" style="width:<?= $pct ?>%;background:<?= $star >= 4 ? '#10b981' : ($star == 3 ? '#f59e0b' : '#ef4444') ?>"></div></div>
            <div class="st-hbar-count"><?= number_format($cnt) ?> <span style="color:#9ca3af;font-weight:400">(<?= $pct ?>%)</span></div>
        </div>
        <?php endforeach; ?>
        <div style="text-align:center;margin-top:12px;font-size:13px;color:#6b7280">
            Note moyenne : <strong style="color:#111827;font-size:16px"><?= $stats['avg_note'] ?> ★</strong> sur <?= number_format($totalNotes) ?> avis
        </div>
    </div>

    <!-- Croissance mensuelle -->
    <div class="st-card" data-aos="fade-up" data-aos-delay="100">
        <h3 class="st-card-title"><i class="fas fa-chart-line" style="color:#3b82f6"></i> Restaurants ajoutes par mois</h3>
        <div class="st-chart-wrap"><canvas id="growthChart"></canvas></div>
    </div>
</div>

<!-- Top Restaurants -->
<div class="st-card" data-aos="fade-up" style="margin-bottom:24px">
    <h3 class="st-card-title"><i class="fas fa-crown" style="color:#f59e0b"></i> Top 10 restaurants les plus populaires
        <a href="/classement-restaurants" style="margin-left:auto;font-size:12px;color:#7c3aed;text-decoration:none;font-weight:600">Voir le classement complet →</a>
    </h3>
    <?php foreach ($topRestaurants as $i => $r):
        $photo = $r['main_photo'] ?? '';
        $photoUrl = $photo ? '/' . ltrim($photo, '/') : '';
    ?>
    <a href="/restaurant/<?= $r['slug'] ?? $r['id'] ?>" class="st-top-item">
        <div class="st-top-rank"><?= $i + 1 ?></div>
        <?php if ($photoUrl): ?>
        <img class="st-top-photo" src="<?= htmlspecialchars($photoUrl) ?>" alt="" loading="lazy">
        <?php else: ?>
        <div class="st-top-photo" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af"><i class="fas fa-store"></i></div>
        <?php endif; ?>
        <div class="st-top-info">
            <div class="st-top-name"><?= htmlspecialchars($r['nom']) ?></div>
            <div class="st-top-meta"><?= htmlspecialchars($r['ville'] ?? '') ?> · <?= htmlspecialchars($r['type_cuisine'] ?? '') ?> · <?= number_format($r['nb_avis']) ?> avis</div>
        </div>
        <div class="st-top-note"><?= number_format(min(5, (float)$r['note_moyenne']), 1) ?> ★</div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Villes -->
<h2 style="font-size:20px;font-weight:800;margin-bottom:16px" data-aos="fade-up"><i class="fas fa-map-marked-alt" style="color:#7c3aed"></i> Statistiques par ville</h2>
<div class="st-cities">
    <?php foreach (array_slice($topCities, 0, 12) as $city): ?>
    <a href="/stats/<?= urlencode($city['ville']) ?>" class="st-city-card" data-aos="fade-up">
        <div class="st-city-name"><?= htmlspecialchars($city['ville']) ?></div>
        <div class="st-city-count"><?= $city['nb_restos'] ?> restaurants</div>
        <div class="st-city-note">★ <?= $city['avg_note'] ?? '-' ?> · <?= number_format($city['total_avis'] ?? 0) ?> avis</div>
    </a>
    <?php endforeach; ?>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const growthData = <?= json_encode($monthlyGrowth) ?>;
if (growthData.length > 0) {
    new Chart(document.getElementById('growthChart'), {
        type: 'bar',
        data: {
            labels: growthData.map(d => d.month_label),
            datasets: [{
                label: 'Restaurants ajoutes',
                data: growthData.map(d => d.cnt),
                backgroundColor: 'rgba(124, 58, 237, 0.2)',
                borderColor: '#7c3aed',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                x: { grid: { display: false } }
            }
        }
    });
}
</script>

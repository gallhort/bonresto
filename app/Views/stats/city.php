<style>
.stc-page { max-width: 1100px; margin: 0 auto; padding: 24px 20px 60px; }
.stc-hero { background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%); border-radius: 20px; padding: 32px 28px; color: #fff; margin-bottom: 24px; }
.stc-hero h1 { font-size: 26px; font-weight: 800; margin: 0 0 6px; }
.stc-hero p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }
.stc-breadcrumb { font-size: 13px; color: #6b7280; margin-bottom: 16px; }
.stc-breadcrumb a { color: #7c3aed; text-decoration: none; }

.stc-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px; margin-bottom: 32px; }
.stc-kpi { background: #fff; border-radius: 14px; padding: 20px 16px; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.stc-kpi-val { font-size: 28px; font-weight: 900; color: #111827; }
.stc-kpi-label { font-size: 12px; color: #6b7280; margin-top: 2px; }

.stc-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px; }
.stc-card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
.stc-card-title { font-size: 15px; font-weight: 700; color: #111827; margin: 0 0 16px; display: flex; align-items: center; gap: 8px; }

.stc-hbar { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.stc-hbar-label { font-size: 13px; font-weight: 500; min-width: 80px; }
.stc-hbar-track { flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.stc-hbar-fill { height: 100%; border-radius: 4px; }
.stc-hbar-count { font-size: 13px; font-weight: 700; min-width: 30px; text-align: right; }

.stc-top-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; text-decoration: none; color: inherit; }
.stc-top-item:last-child { border-bottom: none; }
.stc-top-rank { width: 28px; height: 28px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; color: #6b7280; flex-shrink: 0; }
.stc-top-item:nth-child(-n+3) .stc-top-rank { background: linear-gradient(135deg, #7c3aed, #a78bfa); color: #fff; }
.stc-top-photo { width: 44px; height: 44px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.stc-top-name { font-size: 14px; font-weight: 700; }
.stc-top-meta { font-size: 12px; color: #6b7280; }
.stc-top-note { font-size: 15px; font-weight: 800; color: #7c3aed; flex-shrink: 0; }

@media (max-width: 900px) { .stc-grid-2 { grid-template-columns: 1fr; } }
</style>

<div class="stc-page">

<div class="stc-breadcrumb">
    <a href="/stats">Statistiques</a> → <?= htmlspecialchars($ville) ?>
</div>

<div class="stc-hero">
    <h1><i class="fas fa-chart-pie"></i> Statistiques — <?= htmlspecialchars($ville) ?></h1>
    <p>Les chiffres des restaurants a <?= htmlspecialchars($ville) ?></p>
</div>

<?php $kpis = $cityData['kpis']; ?>
<div class="stc-kpis">
    <div class="stc-kpi">
        <div class="stc-kpi-val"><?= number_format($kpis['nb_restos'] ?? 0) ?></div>
        <div class="stc-kpi-label">Restaurants</div>
    </div>
    <div class="stc-kpi">
        <div class="stc-kpi-val"><?= number_format($kpis['total_avis'] ?? 0) ?></div>
        <div class="stc-kpi-label">Avis</div>
    </div>
    <div class="stc-kpi">
        <div class="stc-kpi-val"><?= $kpis['avg_note'] ?? '-' ?> ★</div>
        <div class="stc-kpi-label">Note moyenne</div>
    </div>
    <div class="stc-kpi">
        <div class="stc-kpi-val"><?= number_format($kpis['total_vues'] ?? 0) ?></div>
        <div class="stc-kpi-label">Vues totales</div>
    </div>
</div>

<div class="stc-grid-2">
    <!-- Top restaurants de la ville -->
    <div class="stc-card">
        <h3 class="stc-card-title"><i class="fas fa-crown" style="color:#f59e0b"></i> Top restaurants a <?= htmlspecialchars($ville) ?></h3>
        <?php foreach ($cityData['topRestos'] as $i => $r):
            $photo = $r['main_photo'] ?? '';
            $photoUrl = $photo ? '/' . ltrim($photo, '/') : '';
        ?>
        <a href="/restaurant/<?= $r['slug'] ?? $r['id'] ?>" class="stc-top-item">
            <div class="stc-top-rank"><?= $i + 1 ?></div>
            <?php if ($photoUrl): ?>
            <img class="stc-top-photo" src="<?= htmlspecialchars($photoUrl) ?>" alt="" loading="lazy">
            <?php else: ?>
            <div class="stc-top-photo" style="background:#e5e7eb;display:flex;align-items:center;justify-content:center;color:#9ca3af"><i class="fas fa-store"></i></div>
            <?php endif; ?>
            <div style="flex:1;min-width:0">
                <div class="stc-top-name"><?= htmlspecialchars($r['nom']) ?></div>
                <div class="stc-top-meta"><?= htmlspecialchars($r['type_cuisine'] ?? '') ?> · <?= number_format($r['nb_avis'] ?? 0) ?> avis</div>
            </div>
            <div class="stc-top-note"><?= number_format(min(5, (float)($r['note_moyenne'] ?? 0)), 1) ?> ★</div>
        </a>
        <?php endforeach; ?>
        <div style="text-align:center;margin-top:12px">
            <a href="/classement-restaurants?ville=<?= urlencode($ville) ?>" style="font-size:13px;color:#7c3aed;font-weight:600;text-decoration:none">Voir le classement complet →</a>
        </div>
    </div>

    <div>
        <!-- Cuisines populaires -->
        <div class="stc-card" style="margin-bottom:20px">
            <h3 class="stc-card-title"><i class="fas fa-utensils" style="color:#f59e0b"></i> Cuisines populaires</h3>
            <?php
            $maxCuis = max(array_column($cityData['cuisines'], 'cnt') ?: [1]);
            foreach ($cityData['cuisines'] as $c):
                $pct = round(($c['cnt'] / $maxCuis) * 100);
            ?>
            <div class="stc-hbar">
                <div class="stc-hbar-label"><?= htmlspecialchars($c['type_cuisine']) ?></div>
                <div class="stc-hbar-track"><div class="stc-hbar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#f59e0b,#fbbf24)"></div></div>
                <div class="stc-hbar-count"><?= $c['cnt'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Distribution des notes -->
        <div class="stc-card">
            <h3 class="stc-card-title"><i class="fas fa-chart-bar" style="color:#10b981"></i> Distribution des notes</h3>
            <?php
            $totalNotes = array_sum($cityData['noteDistribution']) ?: 1;
            foreach ([5, 4, 3, 2, 1] as $star):
                $cnt = $cityData['noteDistribution'][$star] ?? 0;
                $pct = round(($cnt / $totalNotes) * 100);
            ?>
            <div class="stc-hbar">
                <div class="stc-hbar-label" style="min-width:50px"><?= $star ?> ★</div>
                <div class="stc-hbar-track"><div class="stc-hbar-fill" style="width:<?= $pct ?>%;background:<?= $star >= 4 ? '#10b981' : ($star == 3 ? '#f59e0b' : '#ef4444') ?>"></div></div>
                <div class="stc-hbar-count"><?= $cnt ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div style="text-align:center;margin-top:16px">
    <a href="/search?ville=<?= urlencode($ville) ?>" style="display:inline-flex;align-items:center;gap:6px;padding:12px 24px;background:#7c3aed;color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:14px"><i class="fas fa-search"></i> Voir les restaurants a <?= htmlspecialchars($ville) ?></a>
</div>

</div>

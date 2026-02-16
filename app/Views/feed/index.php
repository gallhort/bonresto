<style>
    .feed-page { max-width: 640px; margin: 0 auto; padding: 28px 16px 60px; }

    /* Header */
    .feed-hero { background: linear-gradient(135deg, #00635a 0%, #004d40 50%, #00352e 100%); border-radius: 20px; padding: 32px 28px; margin-bottom: 28px; position: relative; overflow: hidden; }
    .feed-hero::before { content: ''; position: absolute; top: -40px; right: -40px; width: 160px; height: 160px; background: rgba(255,255,255,0.06); border-radius: 50%; }
    .feed-hero::after { content: ''; position: absolute; bottom: -20px; left: 30%; width: 100px; height: 100px; background: rgba(255,255,255,0.04); border-radius: 50%; }
    .feed-hero h1 { font-size: 26px; font-weight: 800; color: #fff; margin: 0 0 6px; letter-spacing: -0.5px; }
    .feed-hero p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; font-weight: 400; }

    /* Tabs */
    .feed-tabs { display: flex; gap: 6px; margin-bottom: 24px; background: #f3f4f6; border-radius: 12px; padding: 4px; }
    .feed-tab { flex: 1; text-align: center; padding: 10px 16px; font-size: 14px; font-weight: 600; text-decoration: none; color: #6b7280; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.25s ease; }
    .feed-tab:hover { color: #374151; background: rgba(255,255,255,0.5); }
    .feed-tab.active { color: #00635a; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }

    /* Suggestions carousel */
    .feed-suggestions { margin-bottom: 28px; }
    .feed-suggestions-title { font-size: 16px; font-weight: 700; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; color: #111827; }
    .feed-suggestions-title i { color: #00635a; font-size: 14px; }
    .sugg-scroll { display: flex; gap: 12px; overflow-x: auto; padding-bottom: 6px; scroll-snap-type: x mandatory; -webkit-overflow-scrolling: touch; }
    .sugg-scroll::-webkit-scrollbar { height: 0; }
    .sugg-card { flex-shrink: 0; width: 170px; border-radius: 14px; overflow: hidden; text-decoration: none; color: inherit; transition: transform 0.2s, box-shadow 0.2s; scroll-snap-align: start; position: relative; }
    .sugg-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
    .sugg-card-img { height: 120px; background: #e5e7eb; overflow: hidden; }
    .sugg-card-img img { width: 100%; height: 100%; object-fit: cover; }
    .sugg-card-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 10px; background: linear-gradient(transparent, rgba(0,0,0,0.75)); color: #fff; }
    .sugg-card-name { font-size: 13px; font-weight: 700; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-shadow: 0 1px 2px rgba(0,0,0,0.3); }
    .sugg-card-meta { font-size: 11px; opacity: 0.85; margin-top: 2px; }
    .sugg-card-meta .sugg-star { color: #fbbf24; }

    /* Section label */
    .feed-section-label { font-size: 13px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 16px; }

    /* Feed list */
    .feed-list { display: flex; flex-direction: column; gap: 16px; }

    /* ===== CARD BASE ===== */
    .feed-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 4px 16px rgba(0,0,0,0.04); transition: transform 0.2s, box-shadow 0.2s; }
    .feed-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08), 0 12px 32px rgba(0,0,0,0.06); }

    /* ===== HERO REVIEW CARD (with photos) ===== */
    .feed-card-hero-wrap { position: relative; overflow: hidden; }
    .feed-card-hero { width: 100%; height: 280px; object-fit: cover; display: block; transition: transform 0.4s ease; }
    .feed-card:hover .feed-card-hero { transform: scale(1.03); }
    .feed-card-hero-overlay { position: absolute; bottom: 0; left: 0; right: 0; padding: 20px 18px 16px; background: linear-gradient(transparent 0%, rgba(0,0,0,0.4) 30%, rgba(0,0,0,0.8) 100%); display: flex; align-items: flex-end; gap: 10px; }
    .feed-card-hero-overlay .fc-avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2.5px solid rgba(255,255,255,0.9); flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
    .feed-card-hero-overlay .fc-avatar-ph { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: rgba(255,255,255,0.9); font-size: 15px; border: 2.5px solid rgba(255,255,255,0.4); box-shadow: 0 2px 8px rgba(0,0,0,0.3); }
    .feed-card-hero-overlay .fc-name { font-size: 15px; font-weight: 800; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.6), 0 2px 8px rgba(0,0,0,0.3); letter-spacing: 0.2px; }
    .feed-card-hero-overlay .fc-time { font-size: 12px; color: rgba(255,255,255,0.85); text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
    .feed-card-hero-overlay .fc-badge { font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px; }
    .feed-card-hero-overlay .fc-badge.elite { background: rgba(251,191,36,0.25); color: #fbbf24; }
    .feed-card-hero-overlay .fc-badge.expert { background: rgba(96,165,250,0.25); color: #93bbfd; }
    .feed-card-hero-overlay .fc-badge.gourmet { background: rgba(244,114,182,0.25); color: #f9a8d4; }
    .feed-card-photo-count { position: absolute; top: 14px; right: 14px; background: rgba(0,0,0,0.55); color: #fff; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 20px; backdrop-filter: blur(4px); }

    .feed-card-body { padding: 14px 18px 16px; }
    .fc-stars { display: flex; gap: 2px; margin-bottom: 8px; }
    .fc-stars .star-on { color: #f59e0b; font-size: 15px; }
    .fc-stars .star-off { color: #e5e7eb; font-size: 15px; }
    .fc-text { font-size: 14px; color: #374151; line-height: 1.55; margin: 0 0 12px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .fc-resto-link { display: flex; align-items: center; gap: 10px; padding-top: 12px; border-top: 1px solid #f3f4f6; text-decoration: none; color: inherit; transition: color 0.2s; }
    .fc-resto-link:hover { color: #00635a; }
    .fc-resto-link:hover .fc-resto-name { color: #00635a; }
    .fc-resto-thumb { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
    .fc-resto-name { font-size: 14px; font-weight: 600; color: #111827; }
    .fc-resto-city { font-size: 12px; color: #9ca3af; }

    /* ===== COMPACT REVIEW CARD (no photos) ===== */
    .feed-card-compact { display: flex; gap: 14px; padding: 16px; }
    .fc-compact-thumb { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; flex-shrink: 0; }
    .fc-compact-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
    .fc-compact-header { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
    .fc-compact-avatar { width: 24px; height: 24px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .fc-compact-avatar-ph { width: 24px; height: 24px; border-radius: 50%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #9ca3af; flex-shrink: 0; }
    .fc-compact-name { font-size: 13px; font-weight: 600; color: #111827; }
    .fc-compact-stars { display: flex; gap: 1px; margin-bottom: 4px; }
    .fc-compact-stars i { font-size: 11px; }
    .fc-compact-stars .star-on { color: #f59e0b; }
    .fc-compact-stars .star-off { color: #e5e7eb; }
    .fc-compact-text { font-size: 13px; color: #6b7280; line-height: 1.4; margin: 0 0 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .fc-compact-resto { font-size: 12px; color: #00635a; font-weight: 600; text-decoration: none; }
    .fc-compact-resto:hover { text-decoration: underline; }
    .fc-compact-resto span { color: #9ca3af; font-weight: 400; }
    .fc-compact-time { font-size: 11px; color: #c4c9d1; margin-left: auto; flex-shrink: 0; }

    /* ===== ACTIVITY CARD (non-review) ===== */
    .feed-card-activity { display: flex; align-items: center; gap: 12px; padding: 14px 16px; }
    .fc-act-icon { width: 38px; height: 38px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; color: #fff; }
    .fc-act-body { flex: 1; min-width: 0; }
    .fc-act-text { font-size: 14px; color: #374151; margin: 0 0 2px; line-height: 1.4; }
    .fc-act-text strong { color: #111827; }
    .fc-act-time { font-size: 12px; color: #c4c9d1; }
    .fc-act-thumb { width: 48px; height: 40px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }

    /* Empty + load more */
    .feed-empty { text-align: center; padding: 60px 20px; }
    .feed-empty-icon { width: 64px; height: 64px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
    .feed-empty-icon i { font-size: 24px; color: #d1d5db; }
    .feed-empty p { color: #9ca3af; font-size: 14px; margin: 0; }

    .feed-load-more { display: block; width: 100%; padding: 14px; border: none; border-radius: 14px; background: linear-gradient(135deg, #00635a, #004d40); cursor: pointer; font-size: 14px; color: #fff; text-align: center; margin-top: 16px; font-weight: 700; transition: opacity 0.2s, transform 0.2s; letter-spacing: 0.3px; }
    .feed-load-more:hover { opacity: 0.9; transform: translateY(-1px); }

    @media (max-width: 480px) {
        .feed-page { padding: 16px 12px 40px; }
        .feed-hero { padding: 24px 20px; border-radius: 16px; }
        .feed-hero h1 { font-size: 22px; }
        .feed-card-hero { height: 220px; }
        .sugg-card { width: 150px; }
        .sugg-card-img { height: 100px; }
    }
</style>

<div class="feed-page">
    <!-- Hero header -->
    <div class="feed-hero">
        <h1>Fil d'actualite</h1>
        <p>Les dernieres decouvertes de la communaute</p>
    </div>

    <!-- Tabs -->
    <?php if (!empty($isAuthenticated)): ?>
    <div class="feed-tabs">
        <a href="/feed" class="feed-tab <?= ($currentTab ?? 'all') === 'all' ? 'active' : '' ?>">
            <i class="fas fa-globe-americas"></i> Tous
        </a>
        <a href="/feed?tab=following" class="feed-tab <?= ($currentTab ?? '') === 'following' ? 'active' : '' ?>">
            <i class="fas fa-user-friends"></i> Abonnements
        </a>
    </div>
    <?php endif; ?>

    <!-- Suggestions -->
    <?php if (!empty($suggestions)): ?>
    <div class="feed-suggestions">
        <p class="feed-suggestions-title"><i class="fas fa-sparkles"></i> Recommande pour vous</p>
        <div class="sugg-scroll">
            <?php foreach ($suggestions as $resto): ?>
            <a href="/restaurant/<?= $resto['slug'] ?? $resto['id'] ?>" class="sugg-card">
                <div class="sugg-card-img">
                    <?php if (!empty($resto['main_photo'])): ?>
                        <img src="/<?= htmlspecialchars($resto['main_photo']) ?>" alt="<?= htmlspecialchars($resto['nom']) ?>">
                    <?php endif; ?>
                </div>
                <div class="sugg-card-overlay">
                    <p class="sugg-card-name"><?= htmlspecialchars($resto['nom']) ?></p>
                    <p class="sugg-card-meta">
                        <span class="sugg-star"><i class="fas fa-star"></i></span>
                        <?= number_format((float)$resto['note_moyenne'], 1) ?>
                        &middot; <?= htmlspecialchars($resto['ville'] ?? '') ?>
                    </p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Feed -->
    <p class="feed-section-label"><i class="fas fa-bolt"></i> Activite recente</p>

    <?php if (!empty($activities)): ?>
    <div class="feed-list" id="feedList">
        <?php foreach ($activities as $act):
            $isReview = ($act['action_type'] === 'review');
            $photos = !empty($act['review_photos']) ? explode('|||', $act['review_photos']) : [];
            $hasPhotos = $isReview && !empty($photos);
            $restoLink = '/restaurant/' . ($act['restaurant_slug'] ?? $act['target_id'] ?? '#');
            $note = $act['review_note'] ?? $act['metadata']['rating'] ?? null;
            $noteVal = $note !== null ? min(5, max(0, (int)$note)) : 0;
        ?>
        <div class="feed-card">
            <?php if ($isReview && $hasPhotos): ?>
                <!-- === HERO REVIEW === -->
                <div class="feed-card-hero-wrap">
                    <a href="<?= $restoLink ?>">
                        <img class="feed-card-hero" src="/<?= htmlspecialchars($photos[0]) ?>" alt="Photo avis">
                    </a>
                    <?php if (count($photos) > 1): ?>
                        <div class="feed-card-photo-count"><i class="fas fa-images"></i> <?= count($photos) ?></div>
                    <?php endif; ?>
                    <div class="feed-card-hero-overlay">
                        <?php if (!empty($act['user_photo'])): ?>
                            <img class="fc-avatar" src="/uploads/avatars/<?= htmlspecialchars($act['user_photo']) ?>" alt="">
                        <?php else: ?>
                            <div class="fc-avatar-ph"><i class="fas fa-user"></i></div>
                        <?php endif; ?>
                        <div>
                            <div class="fc-name">
                                <?= htmlspecialchars($act['prenom'] ?? '') ?>
                                <?php if (!empty($act['user_badge'])): ?>
                                    <span class="fc-badge <?= htmlspecialchars($act['user_badge']) ?>"><?= htmlspecialchars(ucfirst($act['user_badge'])) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="fc-time"><?= htmlspecialchars($act['time_ago']) ?></div>
                        </div>
                    </div>
                </div>
                <div class="feed-card-body">
                    <?php if ($noteVal > 0): ?>
                    <div class="fc-stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= $noteVal ? 'star-on' : 'star-off' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($act['review_message'])): ?>
                        <p class="fc-text"><?= htmlspecialchars($act['review_message']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($act['restaurant_nom'])): ?>
                    <a href="<?= $restoLink ?>" class="fc-resto-link">
                        <?php if (!empty($act['restaurant_photo'])): ?>
                            <img class="fc-resto-thumb" src="/<?= htmlspecialchars($act['restaurant_photo']) ?>" alt="">
                        <?php endif; ?>
                        <div>
                            <div class="fc-resto-name"><?= htmlspecialchars($act['restaurant_nom']) ?></div>
                            <?php if (!empty($act['restaurant_ville'])): ?>
                                <div class="fc-resto-city"><?= htmlspecialchars($act['restaurant_ville']) ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>

            <?php elseif ($isReview): ?>
                <!-- === COMPACT REVIEW === -->
                <div class="feed-card-compact">
                    <?php if (!empty($act['restaurant_photo'])): ?>
                        <a href="<?= $restoLink ?>">
                            <img class="fc-compact-thumb" src="/<?= htmlspecialchars($act['restaurant_photo']) ?>" alt="">
                        </a>
                    <?php endif; ?>
                    <div class="fc-compact-body">
                        <div class="fc-compact-header">
                            <?php if (!empty($act['user_photo'])): ?>
                                <img class="fc-compact-avatar" src="/uploads/avatars/<?= htmlspecialchars($act['user_photo']) ?>" alt="">
                            <?php else: ?>
                                <div class="fc-compact-avatar-ph"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                            <span class="fc-compact-name"><?= htmlspecialchars($act['prenom'] ?? '') ?></span>
                            <span class="fc-compact-time"><?= htmlspecialchars($act['time_ago']) ?></span>
                        </div>
                        <?php if ($noteVal > 0): ?>
                        <div class="fc-compact-stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $noteVal ? 'star-on' : 'star-off' ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($act['review_message'])): ?>
                            <p class="fc-compact-text"><?= htmlspecialchars($act['review_message']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($act['restaurant_nom'])): ?>
                            <a href="<?= $restoLink ?>" class="fc-compact-resto">
                                <?= htmlspecialchars($act['restaurant_nom']) ?>
                                <?php if (!empty($act['restaurant_ville'])): ?>
                                    <span>&middot; <?= htmlspecialchars($act['restaurant_ville']) ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- === NON-REVIEW ACTIVITY === -->
                <div class="feed-card-activity">
                    <div class="fc-act-icon" style="background:<?= \App\Services\ActivityFeedService::getActivityColor($act['action_type']) ?>">
                        <i class="fas <?= \App\Services\ActivityFeedService::getActivityIcon($act['action_type']) ?>"></i>
                    </div>
                    <div class="fc-act-body">
                        <p class="fc-act-text"><?= \App\Services\ActivityFeedService::formatActivity($act) ?></p>
                        <span class="fc-act-time"><?= htmlspecialchars($act['time_ago']) ?></span>
                    </div>
                    <?php if (!empty($act['restaurant_photo'])): ?>
                        <img class="fc-act-thumb" src="/<?= htmlspecialchars($act['restaurant_photo']) ?>" alt="">
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="feed-load-more" id="loadMoreBtn" data-offset="<?= count($activities) ?>">
        <i class="fas fa-chevron-down"></i> Charger plus
    </button>
    <?php else: ?>
    <div class="feed-empty">
        <div class="feed-empty-icon"><i class="fas fa-utensils"></i></div>
        <p>Aucune activite pour le moment.<br>Laissez un avis pour apparaitre ici !</p>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('loadMoreBtn')?.addEventListener('click', async function() {
    const btn = this;
    const offset = parseInt(btn.dataset.offset);
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Chargement...';

    try {
        const tab = new URLSearchParams(window.location.search).get('tab') || 'all';
        const res = await fetch('/api/feed?offset=' + offset + '&limit=15&tab=' + tab);
        const data = await res.json();

        if (data.success && data.data.length > 0) {
            const list = document.getElementById('feedList');
            data.data.forEach(act => {
                const card = document.createElement('div');
                card.className = 'feed-card';
                const restoLink = '/restaurant/' + (act.restaurant_slug || '#');
                const note = act.review_note || 0;

                // Helpers
                const starsHtml = (size) => {
                    let s = `<div class="${size === 'sm' ? 'fc-compact-stars' : 'fc-stars'}">`;
                    for (let i = 1; i <= 5; i++) s += `<i class="fas fa-star ${i <= note ? 'star-on' : 'star-off'}"></i>`;
                    return s + '</div>';
                };
                const esc = (t) => t ? t.replace(/</g,'&lt;').replace(/>/g,'&gt;') : '';

                if (act.action_type === 'review') {
                    const photos = act.review_photos || [];
                    const hasPhoto = photos.length > 0;
                    const avatarHtml = act.user_photo
                        ? `<img class="${hasPhoto ? 'fc-avatar' : 'fc-compact-avatar'}" src="/uploads/avatars/${esc(act.user_photo)}" alt="">`
                        : `<div class="${hasPhoto ? 'fc-avatar-ph' : 'fc-compact-avatar-ph'}"><i class="fas fa-user"></i></div>`;
                    const badgeHtml = act.user_badge
                        ? ` <span class="fc-badge ${act.user_badge}">${act.user_badge.charAt(0).toUpperCase() + act.user_badge.slice(1)}</span>`
                        : '';
                    const textHtml = act.review_message ? `<p class="${hasPhoto ? 'fc-text' : 'fc-compact-text'}">${esc(act.review_message)}</p>` : '';

                    if (hasPhoto) {
                        const photoCount = photos.length > 1 ? `<div class="feed-card-photo-count"><i class="fas fa-images"></i> ${photos.length}</div>` : '';
                        let restoHtml = '';
                        if (act.restaurant_name) {
                            const rt = act.restaurant_photo ? `<img class="fc-resto-thumb" src="/${act.restaurant_photo}" alt="">` : '';
                            const rc = act.restaurant_city ? `<div class="fc-resto-city">${esc(act.restaurant_city)}</div>` : '';
                            restoHtml = `<a href="${restoLink}" class="fc-resto-link">${rt}<div><div class="fc-resto-name">${esc(act.restaurant_name)}</div>${rc}</div></a>`;
                        }
                        card.innerHTML = `
                            <div class="feed-card-hero-wrap">
                                <a href="${restoLink}"><img class="feed-card-hero" src="/${photos[0]}" alt="Photo avis"></a>
                                ${photoCount}
                                <div class="feed-card-hero-overlay">
                                    ${avatarHtml}
                                    <div><div class="fc-name">${esc(act.user_name)}${badgeHtml}</div><div class="fc-time">${act.time_ago}</div></div>
                                </div>
                            </div>
                            <div class="feed-card-body">
                                ${note > 0 ? starsHtml('lg') : ''}
                                ${textHtml}
                                ${restoHtml}
                            </div>`;
                    } else {
                        const rThumb = act.restaurant_photo ? `<a href="${restoLink}"><img class="fc-compact-thumb" src="/${act.restaurant_photo}" alt=""></a>` : '';
                        const restoName = act.restaurant_name
                            ? `<a href="${restoLink}" class="fc-compact-resto">${esc(act.restaurant_name)}${act.restaurant_city ? ` <span>&middot; ${esc(act.restaurant_city)}</span>` : ''}</a>`
                            : '';
                        card.innerHTML = `
                            <div class="feed-card-compact">
                                ${rThumb}
                                <div class="fc-compact-body">
                                    <div class="fc-compact-header">${avatarHtml}<span class="fc-compact-name">${esc(act.user_name)}</span><span class="fc-compact-time">${act.time_ago}</span></div>
                                    ${note > 0 ? starsHtml('sm') : ''}
                                    ${textHtml}
                                    ${restoName}
                                </div>
                            </div>`;
                    }
                } else {
                    const thumbHtml = act.restaurant_photo ? `<img class="fc-act-thumb" src="/${act.restaurant_photo}" alt="">` : '';
                    card.innerHTML = `
                        <div class="feed-card-activity">
                            <div class="fc-act-icon" style="background:${act.color}"><i class="fas ${act.icon}"></i></div>
                            <div class="fc-act-body"><p class="fc-act-text">${act.html}</p><span class="fc-act-time">${act.time_ago}</span></div>
                            ${thumbHtml}
                        </div>`;
                }
                list.appendChild(card);
            });
            btn.dataset.offset = offset + data.data.length;
            btn.innerHTML = '<i class="fas fa-chevron-down"></i> Charger plus';
            if (!data.has_more) btn.style.display = 'none';
        } else {
            btn.style.display = 'none';
        }
    } catch (e) {
        btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur, reessayer';
    }
});
</script>

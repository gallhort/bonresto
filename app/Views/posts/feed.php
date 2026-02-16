<?php
/**
 * F13 - Restaurant Posts Feed
 * Shows all posts (news, promos, events) published by the restaurant owner
 */
$r = $restaurant;
$currentUser = $_SESSION['user'] ?? null;
$isOwner = $currentUser && (int)($currentUser['id'] ?? 0) === (int)($r['owner_id'] ?? 0);
$csrfToken = $_SESSION['csrf_token'] ?? '';

$typeLabels = [
    'news' => ['label' => 'Actualité', 'icon' => 'fa-newspaper', 'color' => '#3b82f6'],
    'promo' => ['label' => 'Promo', 'icon' => 'fa-percent', 'color' => '#ef4444'],
    'event' => ['label' => 'Événement', 'icon' => 'fa-calendar-alt', 'color' => '#8b5cf6'],
    'photo' => ['label' => 'Photo', 'icon' => 'fa-camera', 'color' => '#f59e0b'],
    'menu_update' => ['label' => 'Menu', 'icon' => 'fa-utensils', 'color' => '#10b981'],
];
?>
<?php include ROOT_PATH . '/app/Views/partials/header.php'; ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">

<style>
.posts-page { max-width: 720px; margin: 0 auto; padding: 24px 16px 60px; }

.posts-hero { background: linear-gradient(135deg, #00635a 0%, #004d40 100%); border-radius: 16px; padding: 28px 24px; margin-bottom: 24px; color: #fff; position: relative; overflow: hidden; }
.posts-hero::before { content: ''; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%; }
.posts-hero h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
.posts-hero p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }
.posts-hero a { color: #fde68a; text-decoration: underline; font-weight: 600; }

/* Create post form (owner only) */
.post-create { background: #fff; border-radius: 14px; padding: 20px; margin-bottom: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
.post-create h3 { font-size: 16px; font-weight: 700; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
.post-create h3 i { color: #00635a; }
.pc-row { display: flex; gap: 10px; margin-bottom: 12px; }
.pc-select, .pc-input, .pc-textarea { width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; transition: border-color 0.2s; }
.pc-select:focus, .pc-input:focus, .pc-textarea:focus { border-color: #00635a; outline: none; }
.pc-textarea { min-height: 80px; resize: vertical; }
.pc-file-label { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; background: #f3f4f6; border-radius: 8px; font-size: 13px; font-weight: 600; color: #374151; cursor: pointer; transition: background 0.2s; }
.pc-file-label:hover { background: #e5e7eb; }
.pc-file-label input { display: none; }
.pc-file-name { font-size: 12px; color: #6b7280; margin-left: 8px; }
.pc-actions { display: flex; align-items: center; gap: 12px; margin-top: 14px; }
.pc-submit { padding: 10px 24px; background: #00635a; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
.pc-submit:hover { background: #004d46; }
.pc-submit:disabled { opacity: 0.6; cursor: not-allowed; }

/* Post cards */
.posts-list { display: flex; flex-direction: column; gap: 16px; }
.post-card { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 6px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s; }
.post-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
.post-card-photo { width: 100%; max-height: 400px; object-fit: cover; display: block; }
.post-card-body { padding: 18px 20px; }
.post-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.post-card-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #00635a, #00897b); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; flex-shrink: 0; }
.post-card-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.post-card-meta { flex: 1; min-width: 0; }
.post-card-author { font-size: 14px; font-weight: 600; color: #111827; }
.post-card-time { font-size: 12px; color: #9ca3af; }
.post-card-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; }
.post-card-pin { color: #f59e0b; font-size: 14px; margin-left: auto; }
.post-card-title { font-size: 17px; font-weight: 700; margin: 0 0 6px; color: #111827; }
.post-card-content { font-size: 14px; color: #4b5563; line-height: 1.6; white-space: pre-line; }
.post-card-footer { display: flex; align-items: center; gap: 16px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f3f4f6; }
.post-action-btn { display: flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #6b7280; cursor: pointer; transition: all 0.2s; border: none; background: none; }
.post-action-btn:hover { background: #f3f4f6; color: #111827; }
.post-action-btn.liked { color: #ef4444; }
.post-action-btn.liked i { color: #ef4444; }
.post-owner-actions { margin-left: auto; display: flex; gap: 6px; }
.post-owner-btn { padding: 4px 10px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; transition: all 0.2s; }
.post-owner-btn.pin { background: #fef3c7; color: #92400e; }
.post-owner-btn.pin:hover { background: #fde68a; }
.post-owner-btn.pin.active { background: #f59e0b; color: #fff; }
.post-owner-btn.delete { background: #fef2f2; color: #dc2626; }
.post-owner-btn.delete:hover { background: #fecaca; }

.posts-empty { text-align: center; padding: 48px 20px; color: #9ca3af; }
.posts-empty i { font-size: 48px; margin-bottom: 12px; display: block; color: #d1d5db; }
.posts-empty p { font-size: 15px; margin: 0; }

.post-msg { padding: 10px 16px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; display: none; }
.post-msg.success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; display: block; }
.post-msg.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; display: block; }

@media (max-width: 640px) {
    .posts-page { padding: 16px 12px 40px; }
    .pc-row { flex-direction: column; }
}
</style>

<div class="posts-page">
    <div class="posts-hero">
        <h1><i class="fas fa-bullhorn"></i> Actualités</h1>
        <p>Les dernières nouvelles de <a href="/restaurant/<?= $r['id'] ?>"><?= htmlspecialchars($r['nom']) ?></a></p>
    </div>

    <div id="postMsg" class="post-msg"></div>

    <?php if ($isOwner): ?>
    <div class="post-create">
        <h3><i class="fas fa-plus-circle"></i> Publier un post</h3>
        <form id="postForm" enctype="multipart/form-data">
            <div class="pc-row">
                <select name="type" class="pc-select" style="max-width:180px">
                    <option value="news">Actualité</option>
                    <option value="promo">Promo / Offre</option>
                    <option value="event">Événement</option>
                    <option value="photo">Photo</option>
                    <option value="menu_update">Mise à jour menu</option>
                </select>
                <input type="text" name="title" class="pc-input" placeholder="Titre du post..." required maxlength="200">
            </div>
            <textarea name="content" class="pc-textarea" placeholder="Contenu (optionnel)..." maxlength="2000"></textarea>
            <div class="pc-actions">
                <label class="pc-file-label">
                    <i class="fas fa-image"></i> Photo
                    <input type="file" name="photo" accept="image/*" onchange="document.getElementById('fileName').textContent = this.files[0]?.name || ''">
                </label>
                <span id="fileName" class="pc-file-name"></span>
                <button type="submit" class="pc-submit" id="postSubmitBtn"><i class="fas fa-paper-plane"></i> Publier</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="posts-list" id="postsList">
        <?php if (empty($posts)): ?>
            <div class="posts-empty">
                <i class="fas fa-newspaper"></i>
                <p>Aucun post pour le moment</p>
            </div>
        <?php else: ?>
            <?php foreach ($posts as $post):
                $t = $typeLabels[$post['type']] ?? $typeLabels['news'];
                $initials = strtoupper(mb_substr($post['author_prenom'] ?? '', 0, 1));
                $timeAgo = (new DateTime($post['created_at']))->format('d/m/Y à H:i');
            ?>
            <div class="post-card" id="post_<?= $post['id'] ?>">
                <?php if ($post['photo_path']): ?>
                    <img src="<?= htmlspecialchars($post['photo_path']) ?>" alt="" class="post-card-photo" loading="lazy">
                <?php endif; ?>
                <div class="post-card-body">
                    <div class="post-card-header">
                        <div class="post-card-avatar">
                            <?php if (!empty($post['author_photo'])): ?>
                                <img src="/uploads/avatars/<?= htmlspecialchars($post['author_photo']) ?>" alt="">
                            <?php else: ?>
                                <?= $initials ?>
                            <?php endif; ?>
                        </div>
                        <div class="post-card-meta">
                            <div class="post-card-author"><?= htmlspecialchars(($post['author_prenom'] ?? '') . ' ' . ($post['author_nom'] ?? '')) ?></div>
                            <div class="post-card-time"><?= $timeAgo ?></div>
                        </div>
                        <span class="post-card-badge" style="background:<?= $t['color'] ?>">
                            <i class="fas <?= $t['icon'] ?>"></i> <?= $t['label'] ?>
                        </span>
                        <?php if ((int)$post['is_pinned']): ?>
                            <span class="post-card-pin" title="Épinglé"><i class="fas fa-thumbtack"></i></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="post-card-title"><?= htmlspecialchars($post['title']) ?></h3>
                    <?php if ($post['content']): ?>
                        <div class="post-card-content"><?= nl2br(htmlspecialchars($post['content'])) ?></div>
                    <?php endif; ?>
                    <div class="post-card-footer">
                        <button class="post-action-btn <?= !empty($post['user_liked']) ? 'liked' : '' ?>" onclick="togglePostLike(<?= $post['id'] ?>, this)">
                            <i class="fas fa-heart"></i>
                            <span class="like-count"><?= (int)$post['likes_count'] ?></span>
                        </button>
                        <?php if ($isOwner): ?>
                        <div class="post-owner-actions">
                            <button class="post-owner-btn pin <?= (int)$post['is_pinned'] ? 'active' : '' ?>" onclick="togglePostPin(<?= $post['id'] ?>, this)" title="Épingler">
                                <i class="fas fa-thumbtack"></i>
                            </button>
                            <button class="post-owner-btn delete" onclick="deletePost(<?= $post['id'] ?>)" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const restaurantId = <?= (int)$r['id'] ?>;

// Submit post form
const postForm = document.getElementById('postForm');
if (postForm) {
    postForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('postSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publication...';

        const fd = new FormData(this);
        fd.append('_token', csrfToken);

        try {
            const res = await fetch(`/api/restaurant/${restaurantId}/posts`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: fd,
            });
            const data = await res.json();
            if (data.success) {
                showPostMsg('Post publié avec succès !', 'success');
                setTimeout(() => location.reload(), 800);
            } else {
                showPostMsg(data.error || 'Erreur', 'error');
            }
        } catch (err) {
            showPostMsg('Erreur réseau', 'error');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier';
    });
}

async function togglePostLike(postId, btn) {
    try {
        const res = await fetch(`/api/restaurant/posts/${postId}/like`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await res.json();
        if (data.success) {
            btn.classList.toggle('liked', data.liked);
            btn.querySelector('.like-count').textContent = data.likes_count;
        }
    } catch (e) {}
}

async function togglePostPin(postId, btn) {
    try {
        const res = await fetch(`/api/restaurant/posts/${postId}/pin`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await res.json();
        if (data.success) {
            btn.classList.toggle('active', data.is_pinned);
            showPostMsg(data.message, 'success');
        }
    } catch (e) {}
}

async function deletePost(postId) {
    if (!confirm('Supprimer ce post ?')) return;
    try {
        const res = await fetch(`/api/restaurant/posts/${postId}/delete`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById('post_' + postId);
            if (card) card.remove();
            showPostMsg('Post supprimé', 'success');
        } else {
            showPostMsg(data.error || 'Erreur', 'error');
        }
    } catch (e) {
        showPostMsg('Erreur réseau', 'error');
    }
}

function showPostMsg(text, type) {
    const el = document.getElementById('postMsg');
    el.textContent = text;
    el.className = 'post-msg ' + type;
    setTimeout(() => { el.className = 'post-msg'; }, 4000);
}
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>

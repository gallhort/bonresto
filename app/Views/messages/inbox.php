<?php
/**
 * F25 - Messages Inbox / Sent
 * Unified view for inbox and sent tabs
 */
$currentUser = $_SESSION['user'] ?? null;
$csrfToken = $_SESSION['csrf_token'] ?? '';
$activeTab = $tab ?? 'inbox';
$unread = $unreadCount ?? 0;
?>
<?php include ROOT_PATH . '/app/Views/partials/header.php'; ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">

<style>
.msg-page { max-width: 800px; margin: 0 auto; padding: 24px 16px 60px; }

.msg-hero { background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%); border-radius: 16px; padding: 28px 24px; margin-bottom: 24px; color: #fff; position: relative; overflow: hidden; }
.msg-hero::before { content: ''; position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.06); border-radius: 50%; }
.msg-hero h1 { font-size: 22px; font-weight: 800; margin: 0 0 4px; }
.msg-hero p { color: rgba(255,255,255,0.7); margin: 0; font-size: 14px; }

.msg-tabs { display: flex; gap: 4px; margin-bottom: 20px; background: #f3f4f6; border-radius: 12px; padding: 4px; }
.msg-tab { flex: 1; text-align: center; padding: 10px 16px; font-size: 14px; font-weight: 600; text-decoration: none; color: #6b7280; border-radius: 10px; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; }
.msg-tab:hover { color: #374151; background: rgba(255,255,255,0.5); }
.msg-tab.active { color: #1e40af; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.msg-tab-badge { background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 10px; min-width: 18px; }

.msg-compose-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; background: #1e40af; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 16px; transition: background 0.2s; }
.msg-compose-btn:hover { background: #1e3a8a; }

/* Message list */
.msg-list { display: flex; flex-direction: column; gap: 2px; background: #fff; border-radius: 14px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); overflow: hidden; }
.msg-item { display: flex; align-items: center; gap: 12px; padding: 14px 18px; text-decoration: none; color: inherit; transition: background 0.15s; border-bottom: 1px solid #f3f4f6; }
.msg-item:last-child { border-bottom: none; }
.msg-item:hover { background: #f9fafb; }
.msg-item.unread { background: #eff6ff; font-weight: 600; }
.msg-item.unread:hover { background: #dbeafe; }
.msg-avatar { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 15px; flex-shrink: 0; }
.msg-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.msg-body { flex: 1; min-width: 0; }
.msg-subject { font-size: 14px; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.msg-preview { font-size: 13px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; font-weight: 400; }
.msg-time { font-size: 12px; color: #9ca3af; white-space: nowrap; flex-shrink: 0; }
.msg-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; flex-shrink: 0; }
.msg-delete-btn { padding: 4px 8px; border: none; background: none; color: #d1d5db; cursor: pointer; border-radius: 6px; transition: all 0.2s; font-size: 14px; }
.msg-delete-btn:hover { background: #fef2f2; color: #dc2626; }

.msg-empty { text-align: center; padding: 48px 20px; color: #9ca3af; }
.msg-empty i { font-size: 48px; margin-bottom: 12px; display: block; color: #d1d5db; }

/* Compose modal */
.compose-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 2000; }
.compose-overlay.show { display: block; }
.compose-modal { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; border-radius: 16px; padding: 28px; width: 90%; max-width: 480px; z-index: 2001; display: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
.compose-modal.show { display: block; }
.compose-modal h3 { font-size: 18px; font-weight: 700; margin: 0 0 16px; }
.compose-close { position: absolute; top: 14px; right: 14px; width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; font-size: 14px; }
.compose-close:hover { background: #e5e7eb; }
.compose-field { margin-bottom: 12px; }
.compose-field label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 4px; color: #374151; }
.compose-field input, .compose-field textarea { width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 14px; font-family: inherit; }
.compose-field input:focus, .compose-field textarea:focus { border-color: #1e40af; outline: none; }
.compose-field textarea { min-height: 100px; resize: vertical; }
.compose-send { padding: 10px 24px; background: #1e40af; color: #fff; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; }
.compose-send:hover { background: #1e3a8a; }
.compose-send:disabled { opacity: 0.6; }
.compose-msg { font-size: 13px; margin-top: 10px; padding: 8px 12px; border-radius: 8px; display: none; }
.compose-msg.error { display: block; background: #fef2f2; color: #dc2626; }
.compose-msg.success { display: block; background: #f0fdf4; color: #166534; }
</style>

<div class="msg-page">
    <div class="msg-hero">
        <h1><i class="fas fa-envelope"></i> Messagerie</h1>
        <p>Vos conversations privées</p>
    </div>

    <div class="msg-tabs">
        <a href="/messages" class="msg-tab <?= $activeTab === 'inbox' ? 'active' : '' ?>">
            <i class="fas fa-inbox"></i> Boîte de réception
            <?php if ($unread > 0): ?>
                <span class="msg-tab-badge"><?= $unread ?></span>
            <?php endif; ?>
        </a>
        <a href="/messages/sent" class="msg-tab <?= $activeTab === 'sent' ? 'active' : '' ?>">
            <i class="fas fa-paper-plane"></i> Envoyés
        </a>
    </div>

    <button class="msg-compose-btn" onclick="openCompose()">
        <i class="fas fa-pen"></i> Nouveau message
    </button>

    <?php if (empty($messages)): ?>
        <div class="msg-empty">
            <i class="fas fa-envelope-open-text"></i>
            <p><?= $activeTab === 'inbox' ? 'Aucun message reçu' : 'Aucun message envoyé' ?></p>
        </div>
    <?php else: ?>
        <div class="msg-list">
            <?php foreach ($messages as $m):
                $isInbox = $activeTab === 'inbox';
                $name = $isInbox
                    ? htmlspecialchars(($m['sender_prenom'] ?? '') . ' ' . ($m['sender_nom'] ?? ''))
                    : htmlspecialchars(($m['receiver_prenom'] ?? '') . ' ' . ($m['receiver_nom'] ?? ''));
                $photo = $isInbox ? ($m['sender_photo'] ?? '') : ($m['receiver_photo'] ?? '');
                $initials = strtoupper(mb_substr($isInbox ? ($m['sender_prenom'] ?? '') : ($m['receiver_prenom'] ?? ''), 0, 1));
                $otherId = $isInbox ? (int)$m['sender_id'] : (int)$m['receiver_id'];
                $isUnread = $isInbox && !(int)$m['is_read'];
                $date = (new DateTime($m['created_at']))->format('d/m/Y H:i');
                $subject = $m['subject'] ?: '(sans objet)';
                $preview = mb_substr(strip_tags($m['body']), 0, 80);
            ?>
            <a href="/messages/conversation/<?= $otherId ?>" class="msg-item <?= $isUnread ? 'unread' : '' ?>">
                <?php if ($isUnread): ?><span class="msg-unread-dot"></span><?php endif; ?>
                <div class="msg-avatar">
                    <?php if ($photo): ?>
                        <img src="/uploads/avatars/<?= htmlspecialchars($photo) ?>" alt="">
                    <?php else: ?>
                        <?= $initials ?>
                    <?php endif; ?>
                </div>
                <div class="msg-body">
                    <div class="msg-subject"><?= htmlspecialchars($subject) ?> — <strong><?= $name ?></strong></div>
                    <div class="msg-preview"><?= htmlspecialchars($preview) ?></div>
                </div>
                <span class="msg-time"><?= $date ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Compose Modal -->
<div class="compose-overlay" id="composeOverlay" onclick="closeCompose()"></div>
<div class="compose-modal" id="composeModal">
    <button class="compose-close" onclick="closeCompose()"><i class="fas fa-times"></i></button>
    <h3><i class="fas fa-pen" style="color:#1e40af"></i> Nouveau message</h3>
    <form id="composeForm" onsubmit="sendMessage(event)">
        <div class="compose-field">
            <label>ID Destinataire</label>
            <input type="number" id="composeReceiver" placeholder="ID de l'utilisateur" required min="1">
        </div>
        <div class="compose-field">
            <label>Sujet (optionnel)</label>
            <input type="text" id="composeSubject" placeholder="Sujet du message" maxlength="200">
        </div>
        <div class="compose-field">
            <label>Message</label>
            <textarea id="composeBody" placeholder="Votre message..." required maxlength="2000"></textarea>
        </div>
        <div id="composeMsg" class="compose-msg"></div>
        <button type="submit" class="compose-send" id="composeSendBtn"><i class="fas fa-paper-plane"></i> Envoyer</button>
    </form>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

function openCompose(receiverId) {
    document.getElementById('composeOverlay').classList.add('show');
    document.getElementById('composeModal').classList.add('show');
    if (receiverId) document.getElementById('composeReceiver').value = receiverId;
}

function closeCompose() {
    document.getElementById('composeOverlay').classList.remove('show');
    document.getElementById('composeModal').classList.remove('show');
}

async function sendMessage(e) {
    e.preventDefault();
    const btn = document.getElementById('composeSendBtn');
    const msgEl = document.getElementById('composeMsg');
    btn.disabled = true;
    msgEl.className = 'compose-msg';

    const body = {
        receiver_id: parseInt(document.getElementById('composeReceiver').value),
        subject: document.getElementById('composeSubject').value.trim(),
        body: document.getElementById('composeBody').value.trim(),
    };

    try {
        const res = await fetch('/api/messages/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body),
        });
        const data = await res.json();
        if (data.success) {
            msgEl.textContent = 'Message envoyé !';
            msgEl.className = 'compose-msg success';
            setTimeout(() => { closeCompose(); location.reload(); }, 1000);
        } else {
            msgEl.textContent = data.error || 'Erreur';
            msgEl.className = 'compose-msg error';
        }
    } catch (err) {
        msgEl.textContent = 'Erreur réseau';
        msgEl.className = 'compose-msg error';
    }
    btn.disabled = false;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCompose(); });
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>

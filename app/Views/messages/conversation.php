<?php
/**
 * F25 - Conversation view between two users
 */
$currentUser = $_SESSION['user'] ?? null;
$currentUserId = (int)($currentUser['id'] ?? 0);
$csrfToken = $_SESSION['csrf_token'] ?? '';
$other = $otherUser;
$otherName = htmlspecialchars(($other['prenom'] ?? '') . ' ' . ($other['nom'] ?? ''));
$otherInitials = strtoupper(mb_substr($other['prenom'] ?? '', 0, 1));
?>
<?php include ROOT_PATH . '/app/Views/partials/header.php'; ?>
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">

<style>
.conv-page { max-width: 720px; margin: 0 auto; padding: 24px 16px 60px; }

.conv-header { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb; }
.conv-back { width: 36px; height: 36px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #374151; text-decoration: none; transition: background 0.2s; }
.conv-back:hover { background: #e5e7eb; }
.conv-avatar { width: 44px; height: 44px; border-radius: 50%; background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; flex-shrink: 0; }
.conv-avatar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
.conv-name { font-size: 18px; font-weight: 700; color: #111827; }
.conv-link { font-size: 13px; color: #3b82f6; text-decoration: none; }
.conv-link:hover { text-decoration: underline; }

.conv-messages { display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; min-height: 200px; }
.conv-bubble { max-width: 75%; padding: 12px 16px; border-radius: 16px; font-size: 14px; line-height: 1.5; position: relative; word-wrap: break-word; }
.conv-bubble.sent { align-self: flex-end; background: #1e40af; color: #fff; border-bottom-right-radius: 4px; }
.conv-bubble.received { align-self: flex-start; background: #f3f4f6; color: #111827; border-bottom-left-radius: 4px; }
.conv-bubble-time { font-size: 11px; margin-top: 4px; opacity: 0.6; }
.conv-bubble.sent .conv-bubble-time { text-align: right; }
.conv-bubble-subject { font-size: 11px; font-weight: 700; opacity: 0.7; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; }

.conv-date-sep { text-align: center; padding: 8px; font-size: 12px; color: #9ca3af; font-weight: 600; }

/* Reply form */
.conv-reply { display: flex; gap: 10px; align-items: flex-end; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 14px; padding: 10px 14px; position: sticky; bottom: 16px; box-shadow: 0 -4px 16px rgba(0,0,0,0.05); }
.conv-reply textarea { flex: 1; border: none; outline: none; resize: none; font-size: 14px; font-family: inherit; min-height: 40px; max-height: 120px; line-height: 1.5; }
.conv-reply-btn { width: 40px; height: 40px; border-radius: 50%; background: #1e40af; color: #fff; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0; transition: background 0.2s; font-size: 16px; }
.conv-reply-btn:hover { background: #1e3a8a; }
.conv-reply-btn:disabled { opacity: 0.5; cursor: not-allowed; }

.conv-empty { text-align: center; padding: 40px; color: #9ca3af; font-size: 14px; }

.conv-msg-flash { padding: 8px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 12px; display: none; }
.conv-msg-flash.error { display: block; background: #fef2f2; color: #dc2626; }

@media (max-width: 640px) {
    .conv-bubble { max-width: 85%; }
}
</style>

<div class="conv-page">
    <div class="conv-header">
        <a href="/messages" class="conv-back"><i class="fas fa-arrow-left"></i></a>
        <div class="conv-avatar">
            <?php if (!empty($other['photo_profil'])): ?>
                <img src="/uploads/avatars/<?= htmlspecialchars($other['photo_profil']) ?>" alt="">
            <?php else: ?>
                <?= $otherInitials ?>
            <?php endif; ?>
        </div>
        <div>
            <div class="conv-name"><?= $otherName ?></div>
            <a href="/user/<?= (int)$other['id'] ?>" class="conv-link">Voir le profil</a>
        </div>
    </div>

    <div id="convMsgFlash" class="conv-msg-flash"></div>

    <div class="conv-messages" id="convMessages">
        <?php if (empty($messages)): ?>
            <div class="conv-empty">Aucun message. Envoyez le premier !</div>
        <?php else: ?>
            <?php
            $lastDate = '';
            foreach ($messages as $m):
                $isSent = (int)$m['sender_id'] === $currentUserId;
                $dt = new DateTime($m['created_at']);
                $dateStr = $dt->format('d/m/Y');
                $timeStr = $dt->format('H:i');
            ?>
                <?php if ($dateStr !== $lastDate): $lastDate = $dateStr; ?>
                    <div class="conv-date-sep"><?= $dateStr ?></div>
                <?php endif; ?>
                <div class="conv-bubble <?= $isSent ? 'sent' : 'received' ?>">
                    <?php if (!empty($m['subject'])): ?>
                        <div class="conv-bubble-subject"><?= htmlspecialchars($m['subject']) ?></div>
                    <?php endif; ?>
                    <?= nl2br(htmlspecialchars($m['body'])) ?>
                    <div class="conv-bubble-time"><?= $timeStr ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <form class="conv-reply" id="replyForm" onsubmit="sendReply(event)">
        <textarea id="replyBody" placeholder="Écrire un message..." rows="1" maxlength="2000" required
                  oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
        <button type="submit" class="conv-reply-btn" id="replyBtn" title="Envoyer">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const otherUserId = <?= (int)$other['id'] ?>;

// Auto-scroll to bottom
const msgContainer = document.getElementById('convMessages');
msgContainer.scrollTop = msgContainer.scrollHeight;

async function sendReply(e) {
    e.preventDefault();
    const btn = document.getElementById('replyBtn');
    const textarea = document.getElementById('replyBody');
    const body = textarea.value.trim();
    if (!body) return;

    btn.disabled = true;

    try {
        const res = await fetch('/api/messages/send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({
                receiver_id: otherUserId,
                subject: '',
                body: body,
            }),
        });
        const data = await res.json();
        if (data.success) {
            // Add bubble to conversation
            const bubble = document.createElement('div');
            bubble.className = 'conv-bubble sent';
            bubble.innerHTML = `${escapeHtml(body).replace(/\n/g, '<br>')}<div class="conv-bubble-time">${new Date().toLocaleTimeString('fr-FR', {hour:'2-digit', minute:'2-digit'})}</div>`;

            // Remove empty placeholder if present
            const empty = msgContainer.querySelector('.conv-empty');
            if (empty) empty.remove();

            msgContainer.appendChild(bubble);
            msgContainer.scrollTop = msgContainer.scrollHeight;
            textarea.value = '';
            textarea.style.height = 'auto';
        } else {
            showFlash(data.error || 'Erreur');
        }
    } catch (err) {
        showFlash('Erreur réseau');
    }
    btn.disabled = false;
}

function showFlash(text) {
    const el = document.getElementById('convMsgFlash');
    el.textContent = text;
    el.className = 'conv-msg-flash error';
    setTimeout(() => { el.className = 'conv-msg-flash'; }, 4000);
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// Send on Enter (Shift+Enter for newline)
document.getElementById('replyBody').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('replyForm').dispatchEvent(new Event('submit'));
    }
});
</script>

<?php include ROOT_PATH . '/app/Views/partials/footer.php'; ?>

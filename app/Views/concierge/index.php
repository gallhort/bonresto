<style>
    /* Full-height chat layout */
    .cncg-wrap {
        max-width: 800px;
        margin: 0 auto;
        height: calc(100vh - 80px);
        display: flex;
        flex-direction: column;
        padding: 20px 20px 0;
    }

    /* Chat header */
    .cncg-header {
        background: #fff;
        border-radius: 12px 12px 0 0;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
        flex-shrink: 0;
    }
    .cncg-avatar {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #00635a, #004d40);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 20px;
        flex-shrink: 0;
    }
    .cncg-header-info h2 {
        font-size: 16px;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }
    .cncg-header-status {
        font-size: 12px;
        color: #10b981;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .cncg-header-status::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #10b981;
    }

    /* Messages area */
    .cncg-messages {
        flex: 1;
        overflow-y: auto;
        background: #f3f4f6;
        padding: 24px 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .cncg-messages::-webkit-scrollbar { width: 6px; }
    .cncg-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }

    /* Message bubbles */
    .cncg-msg {
        display: flex;
        gap: 10px;
        max-width: 85%;
        animation: cncgFadeIn 0.3s ease;
    }
    @keyframes cncgFadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .cncg-msg.bot { align-self: flex-start; }
    .cncg-msg.user { align-self: flex-end; flex-direction: row-reverse; }

    .cncg-msg-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 14px;
        margin-top: 4px;
    }
    .cncg-msg.bot .cncg-msg-avatar {
        background: linear-gradient(135deg, #00635a, #004d40);
        color: #fff;
    }
    .cncg-msg.user .cncg-msg-avatar {
        background: #e6f2f0;
        color: #00635a;
    }

    .cncg-bubble {
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.6;
        word-break: break-word;
    }
    .cncg-msg.bot .cncg-bubble {
        background: #fff;
        color: #374151;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
    .cncg-msg.user .cncg-bubble {
        background: #00635a;
        color: #fff;
        border-bottom-right-radius: 4px;
    }
    .cncg-msg-time {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 4px;
        padding: 0 4px;
    }
    .cncg-msg.user .cncg-msg-time { text-align: right; }

    /* Typing indicator */
    .cncg-typing {
        display: none;
        align-self: flex-start;
        gap: 10px;
        max-width: 85%;
    }
    .cncg-typing.visible { display: flex; }
    .cncg-typing-dots {
        background: #fff;
        padding: 14px 20px;
        border-radius: 16px;
        border-bottom-left-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        display: flex;
        gap: 4px;
        align-items: center;
    }
    .cncg-typing-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #9ca3af;
        animation: cncgBounce 1.4s infinite;
    }
    .cncg-typing-dots span:nth-child(2) { animation-delay: 0.2s; }
    .cncg-typing-dots span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes cncgBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30% { transform: translateY(-6px); opacity: 1; }
    }

    /* Suggestion chips */
    .cncg-suggestions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        padding: 12px 20px;
        background: #f3f4f6;
    }
    .cncg-chip {
        padding: 8px 16px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 20px;
        font-size: 13px;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
        font-family: inherit;
        white-space: nowrap;
    }
    .cncg-chip:hover {
        border-color: #00635a;
        color: #00635a;
        background: #e6f2f0;
    }
    .cncg-chip i { margin-right: 4px; font-size: 12px; color: #00635a; }

    /* Input area */
    .cncg-input-area {
        background: #fff;
        border-radius: 0 0 12px 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 -1px 3px rgba(0,0,0,.05);
        flex-shrink: 0;
        margin-bottom: 20px;
    }
    .cncg-input {
        flex: 1;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 12px 18px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s;
        font-family: inherit;
        resize: none;
        max-height: 100px;
        min-height: 44px;
        line-height: 1.4;
    }
    .cncg-input:focus { border-color: #00635a; }
    .cncg-input::placeholder { color: #9ca3af; }
    .cncg-send {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: none;
        background: #00635a;
        color: #fff;
        font-size: 16px;
        cursor: pointer;
        transition: background 0.2s, transform 0.1s;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cncg-send:hover { background: #004d40; }
    .cncg-send:active { transform: scale(0.95); }
    .cncg-send:disabled {
        background: #e5e7eb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    /* Error message */
    .cncg-error {
        display: none;
        background: #fef2f2;
        color: #dc2626;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        text-align: center;
        margin: 0 20px;
    }
    .cncg-error.visible { display: block; }

    @media (max-width: 640px) {
        .cncg-wrap { padding: 10px 10px 0; height: calc(100vh - 70px); }
        .cncg-msg { max-width: 92%; }
        .cncg-input-area { padding: 12px; margin-bottom: 10px; }
    }
</style>

<div class="cncg-wrap">
    <!-- Header -->
    <div class="cncg-header">
        <div class="cncg-avatar"><i class="fas fa-robot"></i></div>
        <div class="cncg-header-info">
            <h2>Assistant LeBonResto</h2>
            <div class="cncg-header-status">En ligne</div>
        </div>
    </div>

    <!-- Messages -->
    <div class="cncg-messages" id="cncgMessages">
        <!-- Welcome message -->
        <div class="cncg-msg bot">
            <div class="cncg-msg-avatar"><i class="fas fa-robot"></i></div>
            <div>
                <div class="cncg-bubble">
                    Bonjour ! <strong>Je suis votre assistant culinaire.</strong><br>
                    Demandez-moi des recommandations de restaurants, des horaires, ou des conseils !<br><br>
                    Par exemple :<br>
                    - "Quel est le meilleur restaurant a Alger ?"<br>
                    - "Un restaurant italien ouvert maintenant"<br>
                    - "Restaurant avec terrasse a Oran"
                </div>
                <div class="cncg-msg-time"><?= date('H:i') ?></div>
            </div>
        </div>

        <!-- Typing indicator -->
        <div class="cncg-typing" id="cncgTyping">
            <div class="cncg-msg-avatar" style="background:linear-gradient(135deg,#00635a,#004d40);color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;">
                <i class="fas fa-robot"></i>
            </div>
            <div class="cncg-typing-dots">
                <span></span><span></span><span></span>
            </div>
        </div>
    </div>

    <!-- Suggestion chips -->
    <div class="cncg-suggestions" id="cncgSuggestions">
        <button class="cncg-chip" onclick="sendSuggestion(this)"><i class="fas fa-star"></i> Meilleur restaurant a Alger</button>
        <button class="cncg-chip" onclick="sendSuggestion(this)"><i class="fas fa-pizza-slice"></i> Restaurant italien</button>
        <button class="cncg-chip" onclick="sendSuggestion(this)"><i class="fas fa-clock"></i> Ouvert maintenant</button>
        <button class="cncg-chip" onclick="sendSuggestion(this)"><i class="fas fa-fire"></i> Tendances du moment</button>
        <button class="cncg-chip" onclick="sendSuggestion(this)"><i class="fas fa-utensils"></i> Restaurant familial</button>
    </div>

    <!-- Error -->
    <div class="cncg-error" id="cncgError"></div>

    <!-- Input -->
    <div class="cncg-input-area">
        <textarea
            class="cncg-input"
            id="cncgInput"
            placeholder="Posez votre question..."
            rows="1"
            maxlength="500"
        ></textarea>
        <button class="cncg-send" id="cncgSend" onclick="sendMessage()" aria-label="Envoyer">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
(function() {
    // Session ID
    if (!sessionStorage.getItem('cncg_session_id')) {
        sessionStorage.setItem('cncg_session_id', 'cncg_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9));
    }

    var input = document.getElementById('cncgInput');
    var sendBtn = document.getElementById('cncgSend');
    var messagesEl = document.getElementById('cncgMessages');
    var typingEl = document.getElementById('cncgTyping');
    var errorEl = document.getElementById('cncgError');
    var suggestionsEl = document.getElementById('cncgSuggestions');
    var isSending = false;

    // Auto-resize textarea
    input.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    // Enter to send (Shift+Enter for newline)
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Focus input on load
    input.focus();

    // Expose to global
    window.sendMessage = function() {
        var text = input.value.trim();
        if (!text || isSending) return;
        doSend(text);
    };

    window.sendSuggestion = function(btn) {
        var text = btn.textContent.trim();
        if (!text || isSending) return;
        doSend(text);
    };

    function doSend(text) {
        isSending = true;
        sendBtn.disabled = true;
        errorEl.classList.remove('visible');

        // Hide suggestions after first use
        suggestionsEl.style.display = 'none';

        // Show user message
        addMessage(text, 'user');
        input.value = '';
        input.style.height = 'auto';

        // Show typing
        typingEl.classList.add('visible');
        scrollToBottom();

        // AJAX
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var headers = { 'Content-Type': 'application/json' };
        if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.content;

        fetch('/api/concierge/ask', {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                message: text,
                session_id: sessionStorage.getItem('cncg_session_id')
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            typingEl.classList.remove('visible');
            isSending = false;
            sendBtn.disabled = false;

            if (data.success && data.response) {
                addMessage(data.response, 'bot');
            } else {
                addMessage(data.error || 'Desole, je n\'ai pas pu traiter votre demande. Reessayez.', 'bot');
            }
            input.focus();
        })
        .catch(function() {
            typingEl.classList.remove('visible');
            isSending = false;
            sendBtn.disabled = false;
            errorEl.textContent = 'Erreur de connexion. Verifiez votre connexion internet.';
            errorEl.classList.add('visible');
            input.focus();
        });
    }

    function addMessage(text, type) {
        var now = new Date();
        var time = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

        var msgDiv = document.createElement('div');
        msgDiv.className = 'cncg-msg ' + type;

        var avatarIcon = type === 'bot' ? 'fa-robot' : 'fa-user';
        var avatarStyle = type === 'bot'
            ? 'background:linear-gradient(135deg,#00635a,#004d40);color:#fff;'
            : 'background:#e6f2f0;color:#00635a;';

        // For bot messages, convert newlines and basic markdown-like formatting
        var displayText = text;
        if (type === 'bot') {
            displayText = escapeHtml(text)
                .replace(/\n/g, '<br>')
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>');
        } else {
            displayText = escapeHtml(text).replace(/\n/g, '<br>');
        }

        msgDiv.innerHTML = ''
            + '<div class="cncg-msg-avatar" style="' + avatarStyle + '"><i class="fas ' + avatarIcon + '"></i></div>'
            + '<div>'
            + '<div class="cncg-bubble">' + displayText + '</div>'
            + '<div class="cncg-msg-time">' + time + '</div>'
            + '</div>';

        // Insert before typing indicator
        messagesEl.insertBefore(msgDiv, typingEl);
        scrollToBottom();
    }

    function scrollToBottom() {
        setTimeout(function() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }, 50);
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
})();
</script>

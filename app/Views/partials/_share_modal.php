<!-- Share Modal Partial — include on any page -->
<div class="share-modal" id="shareModal">
    <div class="share-modal__overlay" onclick="closeShareModal()"></div>
    <div class="share-modal__dialog">
        <div class="share-modal__header">
            <h3 class="share-modal__title">Partager</h3>
            <button class="share-modal__close" onclick="closeShareModal()" aria-label="Fermer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="share-modal__body">
            <!-- Share buttons grid -->
            <div class="share-modal__grid">
                <button class="share-modal__btn share-modal__btn--facebook" id="shareModalFacebook" onclick="shareVia('facebook')">
                    <span class="share-modal__btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </span>
                    <span class="share-modal__btn-label">Facebook</span>
                </button>

                <button class="share-modal__btn share-modal__btn--twitter" id="shareModalTwitter" onclick="shareVia('twitter')">
                    <span class="share-modal__btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                        </svg>
                    </span>
                    <span class="share-modal__btn-label">X</span>
                </button>

                <button class="share-modal__btn share-modal__btn--whatsapp" id="shareModalWhatsapp" onclick="shareVia('whatsapp')">
                    <span class="share-modal__btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </span>
                    <span class="share-modal__btn-label">WhatsApp</span>
                </button>

                <button class="share-modal__btn share-modal__btn--telegram" id="shareModalTelegram" onclick="shareVia('telegram')">
                    <span class="share-modal__btn-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.492-1.302.48-.428-.012-1.252-.242-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                        </svg>
                    </span>
                    <span class="share-modal__btn-label">Telegram</span>
                </button>
            </div>

            <!-- Copy link -->
            <div class="share-modal__copy">
                <input type="text" class="share-modal__copy-input" id="shareModalUrl" readonly>
                <button class="share-modal__copy-btn" id="shareModalCopyBtn" onclick="copyShareUrl()">
                    <svg class="share-modal__copy-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"></path>
                    </svg>
                    <span id="shareModalCopyLabel">Copier</span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ---- Share Modal (scoped with .share-modal prefix) ---- */
.share-modal {
    --sm-primary: #00635a;
    --sm-primary-light: #e6f2f0;
    --sm-accent: #f59e0b;
    --sm-gray-100: #f3f4f6;
    --sm-gray-200: #e5e7eb;
    --sm-gray-600: #6b7280;
    --sm-white: #fff;
    --sm-radius: 12px;
    --sm-shadow: 0 1px 3px rgba(0,0,0,.1);
    --sm-shadow-lg: 0 4px 12px rgba(0,0,0,.12);

    display: none;
    position: fixed;
    inset: 0;
    z-index: 10000;
    align-items: center;
    justify-content: center;
}

.share-modal--open {
    display: flex;
}

.share-modal__overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

.share-modal__dialog {
    position: relative;
    background: var(--sm-white);
    border-radius: var(--sm-radius);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    width: 100%;
    max-width: 420px;
    margin: 20px;
    animation: shareModalSlideIn 0.25s ease-out;
    overflow: hidden;
}

@keyframes shareModalSlideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.share-modal__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid var(--sm-gray-200);
}

.share-modal__title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.share-modal__close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    background: var(--sm-gray-100);
    border-radius: 50%;
    color: var(--sm-gray-600);
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.share-modal__close:hover {
    background: var(--sm-gray-200);
    color: #1f2937;
}

.share-modal__body {
    padding: 24px;
}

/* ---- Share button grid ---- */
.share-modal__grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 24px;
}

.share-modal__btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 8px;
    border: none;
    border-radius: 10px;
    background: var(--sm-gray-100);
    cursor: pointer;
    transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
}

.share-modal__btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.share-modal__btn:active {
    transform: translateY(0);
}

.share-modal__btn-icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--sm-white);
}

.share-modal__btn-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
}

/* Platform colors */
.share-modal__btn--facebook .share-modal__btn-icon {
    background: #1877f2;
}
.share-modal__btn--facebook:hover {
    background: #e8f0fe;
}

.share-modal__btn--twitter .share-modal__btn-icon {
    background: #000000;
}
.share-modal__btn--twitter:hover {
    background: #f0f0f0;
}

.share-modal__btn--whatsapp .share-modal__btn-icon {
    background: #25d366;
}
.share-modal__btn--whatsapp:hover {
    background: #e7f9ee;
}

.share-modal__btn--telegram .share-modal__btn-icon {
    background: #0088cc;
}
.share-modal__btn--telegram:hover {
    background: #e5f3fa;
}

/* ---- Copy link ---- */
.share-modal__copy {
    display: flex;
    gap: 8px;
    align-items: stretch;
}

.share-modal__copy-input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid var(--sm-gray-200);
    border-radius: 8px;
    font-size: 13px;
    font-family: inherit;
    color: #374151;
    background: var(--sm-gray-100);
    min-width: 0;
    outline: none;
    transition: border-color 0.2s;
}

.share-modal__copy-input:focus {
    border-color: var(--sm-primary);
}

.share-modal__copy-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 10px 16px;
    border: none;
    border-radius: 8px;
    background: var(--sm-primary);
    color: var(--sm-white);
    font-size: 13px;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s;
}

.share-modal__copy-btn:hover {
    background: #004d44;
}

.share-modal__copy-btn--copied {
    background: #16a34a !important;
}

/* ---- Responsive ---- */
@media (max-width: 480px) {
    .share-modal__dialog {
        margin: 12px;
    }
    .share-modal__body {
        padding: 20px 16px;
    }
    .share-modal__grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }
    .share-modal__btn {
        padding: 12px 4px;
    }
    .share-modal__btn-icon {
        width: 40px;
        height: 40px;
    }
    .share-modal__btn-label {
        font-size: 11px;
    }
    .share-modal__copy {
        flex-direction: column;
    }
    .share-modal__copy-btn {
        justify-content: center;
    }
}
</style>

<script>
(function() {
    'use strict';

    // State
    var _shareType = '';
    var _shareId = '';
    var _shareTitle = '';
    var _shareDescription = '';
    var _shareUrl = '';
    var _shareUrls = {};

    /**
     * Open the share modal.
     * Fetches the share card data from the API, then populates share URLs.
     */
    window.openShareModal = function(type, id, title) {
        _shareType = type;
        _shareId = id;
        _shareTitle = title || '';
        _shareDescription = '';

        var modal = document.getElementById('shareModal');
        if (!modal) return;

        // Show modal immediately
        modal.classList.add('share-modal--open');
        document.body.style.overflow = 'hidden';

        // Build the page URL as fallback (use current page URL for restaurants)
        var pageUrl = window.location.href;

        // Fetch share card from API
        fetch('/api/share/card?type=' + encodeURIComponent(type) + '&id=' + encodeURIComponent(id))
            .then(function(resp) { return resp.json(); })
            .then(function(data) {
                _shareUrl = data.url || pageUrl;
                _shareTitle = data.title || _shareTitle;
                _shareDescription = data.description || '';
                populateShareUrls(_shareUrl, _shareTitle, _shareDescription);
            })
            .catch(function() {
                _shareUrl = pageUrl;
                populateShareUrls(_shareUrl, _shareTitle, '');
            });
    };

    /**
     * Populate share button URLs and the copy input.
     * Uses rich messages per platform for better social engagement.
     */
    function populateShareUrls(url, title, description) {
        var encodedUrl = encodeURIComponent(url);

        // Rich share messages per platform
        var fbQuote = title + (description ? ' - ' + description : '') + ' | LeBonResto';
        var tweetText = '🍽️ ' + title + (description ? ' (' + description + ')' : '') + ' — Decouvrez-le sur LeBonResto !';
        var waText = '🍽️ *' + title + '*' + (description ? '\n' + description : '') + '\n\nDecouvrez-le sur LeBonResto :\n' + url;
        var tgText = '🍽️ ' + title + (description ? ' - ' + description : '') + ' | LeBonResto';

        _shareUrls = {
            facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + encodedUrl + '&quote=' + encodeURIComponent(fbQuote),
            twitter: 'https://twitter.com/intent/tweet?url=' + encodedUrl + '&text=' + encodeURIComponent(tweetText),
            whatsapp: 'https://wa.me/?text=' + encodeURIComponent(waText),
            telegram: 'https://t.me/share/url?url=' + encodedUrl + '&text=' + encodeURIComponent(tgText)
        };

        var urlInput = document.getElementById('shareModalUrl');
        if (urlInput) urlInput.value = url;

        // Reset copy button
        var copyLabel = document.getElementById('shareModalCopyLabel');
        var copyBtn = document.getElementById('shareModalCopyBtn');
        if (copyLabel) copyLabel.textContent = 'Copier';
        if (copyBtn) copyBtn.classList.remove('share-modal__copy-btn--copied');
    }

    /**
     * Share via a specific platform. Opens in a new window and logs the share.
     */
    window.shareVia = function(platform) {
        var url = _shareUrls[platform];
        if (!url) return;

        // Open share window
        var w = 600, h = 400;
        var left = (screen.width - w) / 2;
        var top = (screen.height - h) / 2;
        window.open(url, 'share_' + platform, 'width=' + w + ',height=' + h + ',left=' + left + ',top=' + top + ',toolbar=0,menubar=0');

        // Log the share
        logShare(platform);
    };

    /**
     * Copy the share URL to clipboard.
     */
    window.copyShareUrl = function() {
        var urlInput = document.getElementById('shareModalUrl');
        var copyLabel = document.getElementById('shareModalCopyLabel');
        var copyBtn = document.getElementById('shareModalCopyBtn');
        if (!urlInput) return;

        // Use clipboard API if available, fallback to select+copy
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(urlInput.value).then(function() {
                showCopied(copyBtn, copyLabel);
            }).catch(function() {
                fallbackCopy(urlInput, copyBtn, copyLabel);
            });
        } else {
            fallbackCopy(urlInput, copyBtn, copyLabel);
        }

        // Log the copy as a share action
        logShare('copy');
    };

    function fallbackCopy(input, btn, label) {
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand('copy');
            showCopied(btn, label);
        } catch (e) {
            // Silently fail
        }
    }

    function showCopied(btn, label) {
        if (label) label.textContent = 'Copié !';
        if (btn) btn.classList.add('share-modal__copy-btn--copied');

        setTimeout(function() {
            if (label) label.textContent = 'Copier';
            if (btn) btn.classList.remove('share-modal__copy-btn--copied');
        }, 2000);
    }

    /**
     * Log a share action to the server.
     */
    function logShare(platform) {
        var csrfMeta = document.querySelector('meta[name=csrf-token]');
        var csrfToken = csrfMeta ? csrfMeta.content : '';

        fetch('/api/share/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                shareable_type: _shareType,
                shareable_id: _shareId,
                platform: platform
            })
        }).catch(function() {
            // Non-critical — fail silently
        });
    }

    /**
     * Close the share modal.
     */
    window.closeShareModal = function() {
        var modal = document.getElementById('shareModal');
        if (!modal) return;
        modal.classList.remove('share-modal--open');
        document.body.style.overflow = '';
    };

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var modal = document.getElementById('shareModal');
            if (modal && modal.classList.contains('share-modal--open')) {
                closeShareModal();
            }
        }
    });
})();
</script>

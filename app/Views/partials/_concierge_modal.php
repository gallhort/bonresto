<!-- CONCIERGE MODAL v2 -->
<div class="ccm-overlay" id="ccmOverlay" onclick="closeConciergeModal()"></div>
<div class="ccm-modal" id="ccmModal" role="dialog" aria-modal="true" aria-label="Concierge IA">
    <!-- Header -->
    <div class="ccm-header">
        <div class="ccm-header-left">
            <span class="ccm-header-icon">&#x1F9ED;</span>
            <span class="ccm-header-title">Concierge LeBonResto</span>
        </div>
        <button class="ccm-reset" onclick="resetConciergeConversation()" title="Nouvelle conversation" aria-label="Nouvelle conversation">
            <i class="fas fa-redo-alt"></i>
        </button>
        <button class="ccm-close" onclick="closeConciergeModal()" aria-label="Fermer">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <!-- Conversation zone -->
    <div class="ccm-body" id="ccmBody">
        <!-- Welcome message + chips rendered by JS -->
    </div>

    <!-- Input zone (sticky bottom) -->
    <div class="ccm-input-zone">
        <form class="ccm-input-form" id="ccmForm" onsubmit="sendConciergeMessage(event)">
            <input type="text" id="ccmInput" class="ccm-input"
                   placeholder="Ex: resto familial a Oran pas cher"
                   autocomplete="off" maxlength="500"
                   aria-label="Votre message">
            <button type="submit" class="ccm-send" aria-label="Envoyer">
                <i class="fas fa-paper-plane"></i>
            </button>
        </form>
    </div>
</div>

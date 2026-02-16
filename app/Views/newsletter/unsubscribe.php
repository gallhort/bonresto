<?php
/**
 * NEWSLETTER UNSUBSCRIBE - Confirmation / Error page
 * Variables: $success (bool), $error (string|null)
 */
?>

<style>
.unsub-wrapper {
    --primary: #00635a;
    --primary-light: #e6f2f0;
    --accent: #f59e0b;
    --gray-100: #f3f4f6;
    --gray-200: #e5e7eb;
    --gray-600: #6b7280;
    --white: #fff;
    --radius: 12px;
    --shadow: 0 1px 3px rgba(0,0,0,.1);
    --shadow-lg: 0 4px 12px rgba(0,0,0,.12);

    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 40px 20px;
}

.unsub-card {
    background: var(--white);
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    padding: 48px 40px;
    text-align: center;
    max-width: 480px;
    width: 100%;
}

.unsub-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.unsub-icon--success {
    background: #dcfce7;
    color: #166534;
}

.unsub-icon--success svg {
    width: 40px;
    height: 40px;
    stroke: #166534;
    fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.unsub-icon--error {
    background: #fee2e2;
    color: #991b1b;
}

.unsub-icon--error svg {
    width: 40px;
    height: 40px;
    stroke: #991b1b;
    fill: none;
    stroke-width: 2.5;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.unsub-card h1 {
    font-size: 22px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
    line-height: 1.3;
}

.unsub-card p {
    font-size: 15px;
    color: var(--gray-600);
    line-height: 1.6;
    margin-bottom: 28px;
}

.unsub-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 32px;
    background: var(--primary);
    color: var(--white);
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
    transition: background 0.2s, transform 0.2s;
}

.unsub-link:hover {
    background: #004d44;
    transform: translateY(-1px);
}

.unsub-link svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    fill: none;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
}

@media (max-width: 520px) {
    .unsub-card {
        padding: 36px 24px;
    }
    .unsub-card h1 {
        font-size: 20px;
    }
}
</style>

<div class="unsub-wrapper">
    <div class="unsub-card">
        <?php if (!empty($success)): ?>
            <div class="unsub-icon unsub-icon--success">
                <svg viewBox="0 0 24 24">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h1>Vous avez été désabonné avec succès</h1>
            <p>Votre email ne recevra plus de newsletters. Si vous changez d'avis, vous pourrez vous réabonner depuis votre profil.</p>
        <?php else: ?>
            <div class="unsub-icon unsub-icon--error">
                <svg viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <h1>Impossible de vous désabonner</h1>
            <p><?= htmlspecialchars($error ?? 'Le lien de désabonnement est invalide ou a expiré.') ?></p>
        <?php endif; ?>

        <a href="/" class="unsub-link">
            <svg viewBox="0 0 24 24">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Retour à l'accueil
        </a>
    </div>
</div>

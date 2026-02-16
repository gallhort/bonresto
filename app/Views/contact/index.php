<?php
$old = $_SESSION['contact_old'] ?? [];
$errors = $_SESSION['contact_errors'] ?? [];
unset($_SESSION['contact_old'], $_SESSION['contact_errors']);
$currentUser = $_SESSION['user'] ?? null;
?>

<section class="contact-page">
    <div class="contact-container">
        <div class="contact-header">
            <h1>Nous contacter</h1>
            <p>Une question, une suggestion, un problème ? Notre équipe est là pour vous aider.</p>
        </div>

        <div class="contact-grid">
            <!-- Formulaire -->
            <div class="contact-form-wrapper">
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <p><?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="/contact" method="POST" class="contact-form">
                    <!-- Honeypot -->
                    <div style="display:none;">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="contact-name">Nom complet <span class="required">*</span></label>
                            <input type="text" id="contact-name" name="name" required minlength="2"
                                   value="<?= htmlspecialchars($old['name'] ?? $currentUser['prenom'] ?? '') ?>"
                                   placeholder="Votre nom">
                        </div>
                        <div class="form-group">
                            <label for="contact-email">Email <span class="required">*</span></label>
                            <input type="email" id="contact-email" name="email" required
                                   value="<?= htmlspecialchars($old['email'] ?? $currentUser['email'] ?? '') ?>"
                                   placeholder="votre@email.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contact-subject">Sujet <span class="required">*</span></label>
                        <select id="contact-subject" name="subject" required>
                            <option value="">Choisir un sujet...</option>
                            <option value="Question générale" <?= ($old['subject'] ?? '') === 'Question générale' ? 'selected' : '' ?>>Question générale</option>
                            <option value="Signaler un restaurant" <?= ($old['subject'] ?? '') === 'Signaler un restaurant' ? 'selected' : '' ?>>Signaler un restaurant</option>
                            <option value="Problème technique" <?= ($old['subject'] ?? '') === 'Problème technique' ? 'selected' : '' ?>>Problème technique</option>
                            <option value="Suggestion" <?= ($old['subject'] ?? '') === 'Suggestion' ? 'selected' : '' ?>>Suggestion d'amélioration</option>
                            <option value="Revendiquer un restaurant" <?= ($old['subject'] ?? '') === 'Revendiquer un restaurant' ? 'selected' : '' ?>>Revendiquer un restaurant</option>
                            <option value="Partenariat" <?= ($old['subject'] ?? '') === 'Partenariat' ? 'selected' : '' ?>>Partenariat / Publicité</option>
                            <option value="Suppression de données" <?= ($old['subject'] ?? '') === 'Suppression de données' ? 'selected' : '' ?>>Suppression de données (RGPD)</option>
                            <option value="Autre" <?= ($old['subject'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="contact-message">Message <span class="required">*</span></label>
                        <textarea id="contact-message" name="message" required minlength="10" maxlength="5000"
                                  rows="6" placeholder="Décrivez votre demande en détail..."><?= htmlspecialchars($old['message'] ?? '') ?></textarea>
                        <div class="char-count"><span id="charCount">0</span> / 5000</div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Envoyer le message
                    </button>
                </form>
            </div>

            <!-- Sidebar -->
            <div class="contact-sidebar">
                <div class="contact-info-card">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <h3>Email</h3>
                    <p>contact@lebonresto.dz</p>
                </div>
                <div class="contact-info-card">
                    <div class="info-icon"><i class="fas fa-clock"></i></div>
                    <h3>Temps de réponse</h3>
                    <p>Nous répondons généralement sous 24 à 48 heures.</p>
                </div>
                <div class="contact-info-card">
                    <div class="info-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Vos données</h3>
                    <p>Vos informations sont protégées. Consultez notre <a href="/confidentialite">politique de confidentialité</a>.</p>
                </div>

                <div class="contact-faq">
                    <h3><i class="fas fa-question-circle"></i> Questions fréquentes</h3>
                    <details>
                        <summary>Comment ajouter mon restaurant ?</summary>
                        <p>Rendez-vous sur la page <a href="/add-restaurant">Ajouter un restaurant</a> et remplissez le formulaire. Votre restaurant sera vérifié avant publication.</p>
                    </details>
                    <details>
                        <summary>Comment revendiquer mon restaurant ?</summary>
                        <p>Trouvez votre restaurant sur Le Bon Resto, puis cliquez sur "Revendiquer ce restaurant" sur sa fiche.</p>
                    </details>
                    <details>
                        <summary>Comment supprimer un avis ?</summary>
                        <p>Les avis ne peuvent être supprimés que s'ils enfreignent nos conditions d'utilisation. Utilisez le bouton "Signaler" sur l'avis concerné.</p>
                    </details>
                    <details>
                        <summary>Comment supprimer mon compte ?</summary>
                        <p>Rendez-vous dans vos <a href="/profil">paramètres de profil</a>, section "Supprimer mon compte".</p>
                    </details>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.contact-page {
    padding: 40px 20px 80px;
    background: #f8fafc;
    min-height: calc(100vh - 80px);
}

.contact-container {
    max-width: 1100px;
    margin: 0 auto;
}

.contact-header {
    text-align: center;
    margin-bottom: 48px;
}

.contact-header h1 {
    font-family: 'Barlow', sans-serif;
    font-size: 36px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
}

.contact-header p {
    font-size: 17px;
    color: #64748b;
    max-width: 500px;
    margin: 0 auto;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 40px;
    align-items: start;
}

/* Form */
.contact-form-wrapper {
    background: white;
    border-radius: 16px;
    padding: 36px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    border: 1px solid #e2e8f0;
}

.contact-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.contact-form .form-group {
    margin-bottom: 20px;
}

.contact-form label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
}

.contact-form .required { color: #ef4444; }

.contact-form input,
.contact-form select,
.contact-form textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 15px;
    font-family: 'Inter', -apple-system, sans-serif;
    transition: all 0.2s;
    background: #fafafa;
}

.contact-form input:focus,
.contact-form select:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #00635a;
    background: white;
    box-shadow: 0 0 0 3px rgba(0, 99, 90, 0.08);
}

.contact-form textarea {
    resize: vertical;
    min-height: 140px;
}

.char-count {
    text-align: right;
    font-size: 12px;
    color: #94a3b8;
    margin-top: 4px;
}

.btn-submit {
    width: 100%;
    padding: 14px 28px;
    background: #00635a;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.2s;
    font-family: inherit;
}

.btn-submit:hover {
    background: #004d46;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 99, 90, 0.3);
}

/* Alerts */
.alert {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
    line-height: 1.5;
}

.alert i { font-size: 18px; margin-top: 2px; flex-shrink: 0; }

.alert-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

/* Sidebar */
.contact-sidebar {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.contact-info-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px;
    text-align: center;
}

.info-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #e8f5f0;
    color: #00635a;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin: 0 auto 12px;
}

.contact-info-card h3 {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
}

.contact-info-card p {
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
}

.contact-info-card a {
    color: #00635a;
    text-decoration: underline;
}

/* FAQ */
.contact-faq {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px;
}

.contact-faq > h3 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.contact-faq > h3 i { color: #00635a; }

.contact-faq details {
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 0;
}

.contact-faq details:last-child { border-bottom: none; }

.contact-faq summary {
    font-size: 14px;
    font-weight: 600;
    color: #334155;
    cursor: pointer;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.contact-faq summary::after {
    content: '+';
    font-size: 18px;
    color: #94a3b8;
    transition: transform 0.2s;
}

.contact-faq details[open] summary::after {
    content: '−';
    color: #00635a;
}

.contact-faq details p {
    font-size: 13px;
    color: #64748b;
    line-height: 1.6;
    margin-top: 8px;
    padding-left: 4px;
}

.contact-faq details a { color: #00635a; }

/* Responsive */
@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
    }
    .contact-form .form-row {
        grid-template-columns: 1fr;
    }
    .contact-form-wrapper {
        padding: 24px;
    }
    .contact-header h1 {
        font-size: 28px;
    }
}
</style>

<script>
// Character counter
const msgInput = document.getElementById('contact-message');
const counter = document.getElementById('charCount');
if (msgInput && counter) {
    const update = () => counter.textContent = msgInput.value.length;
    msgInput.addEventListener('input', update);
    update();
}
</script>

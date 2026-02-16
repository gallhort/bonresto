<?php
$currentUser = $_SESSION['user'] ?? null;
$locale = $_SESSION['locale'] ?? 'fr';
$dir = ($locale === 'ar') ? 'rtl' : 'ltr';

// Récupérer les infos fidélité si connecté
$userLoyalty = null;
$ownedRestaurants = [];
if ($currentUser) {
    try {
        $pdo = \App\Core\Database::getInstance()->getPdo();

        $stmt = $pdo->prepare("
            SELECT u.points, u.badge, b.icon
            FROM users u
            LEFT JOIN badges b ON b.name = u.badge
            WHERE u.id = ?
        ");
        $stmt->execute([$currentUser['id']]);
        $userLoyalty = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user owns restaurants
        $ownerStmt = $pdo->prepare("SELECT id, nom FROM restaurants WHERE owner_id = ? ORDER BY nom");
        $ownerStmt->execute([$currentUser['id']]);
        $ownedRestaurants = $ownerStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    } catch (Exception $e) {
        // Silently fail
    }
}
?>
<!-- HEADER -->
<header class="thefork-header" id="mainHeader" role="banner" dir="<?= $dir ?>">
    <div class="header-container">
        <!-- Logo -->
        <a href="/" class="header-logo" aria-label="Le Bon Resto - Accueil">
            <svg class="logo-svg" width="34" height="34" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="48" height="48" rx="12" fill="#00635a"/>
                <path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                <path d="M28 12v6c0 3 2 5 4 5v-11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M32 23v13" stroke="white" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span class="logo-text">LeBon<span class="logo-text-accent">Resto</span></span>
        </a>

        <!-- Navigation centrale -->
        <nav class="header-nav desktop-only" aria-label="Navigation principale">
            <a href="/search" class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/search' || str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/search?') ? 'active' : '' ?>">
                <i class="fas fa-search"></i> <?= __('nav.explore') ?>
            </a>
            <a href="/classement-restaurants" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/classement') ? 'active' : '' ?>">
                <i class="fas fa-crown"></i> <?= __('nav.top100') ?>
            </a>
            <a href="/feed" class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/feed' ? 'active' : '' ?>">
                <i class="fas fa-stream"></i> <?= __('nav.feed') ?>
            </a>
            <a href="/add-restaurant" class="nav-link <?= ($_SERVER['REQUEST_URI'] ?? '') === '/add-restaurant' ? 'active' : '' ?>">
                <i class="fas fa-plus"></i> <?= __('nav.add') ?>
            </a>
        </nav>

        <!-- Language selector -->
        <div class="lang-selector" id="langSelector" style="position:relative">
            <button class="lang-btn" onclick="toggleLangMenu(event)" title="Langue" aria-label="Changer la langue" style="background:none;border:none;cursor:pointer;font-size:18px;color:#6b7280;padding:6px;display:flex;align-items:center;gap:4px">
                <i class="fas fa-globe"></i>
                <span style="font-size:12px;font-weight:600" id="currentLangLabel"><?= strtoupper($locale) ?></span>
            </button>
            <div class="lang-dropdown" id="langDropdown" style="display:none;position:absolute;top:calc(100% + 6px);right:0;background:white;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.15);min-width:120px;z-index:1001;overflow:hidden">
                <a href="#" onclick="setLang('fr');return false" class="lang-option" style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:13px;color:#374151;text-decoration:none;transition:background .2s<?= $locale === 'fr' ? ';background:#f0fdf4;font-weight:600;color:#00635a' : '' ?>">
                    <span>FR</span> Français
                </a>
                <a href="#" onclick="setLang('ar');return false" class="lang-option" style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:13px;color:#374151;text-decoration:none;transition:background .2s<?= $locale === 'ar' ? ';background:#f0fdf4;font-weight:600;color:#00635a' : '' ?>">
                    <span>AR</span> العربية
                </a>
                <a href="#" onclick="setLang('en');return false" class="lang-option" style="display:flex;align-items:center;gap:8px;padding:10px 14px;font-size:13px;color:#374151;text-decoration:none;transition:background .2s<?= $locale === 'en' ? ';background:#f0fdf4;font-weight:600;color:#00635a' : '' ?>">
                    <span>EN</span> English
                </a>
            </div>
        </div>

        <!-- Actions droite -->
        <div class="header-actions">
            <?php if ($currentUser): ?>
                <!-- Points fidélité -->
                <?php if ($userLoyalty): ?>
                <a href="/fidelite" class="loyalty-widget" title="<?= __('header.loyalty') ?>">
                    <span class="loyalty-icon"><?= $userLoyalty['icon'] ?? '⭐' ?></span>
                    <span class="loyalty-points"><?= number_format($userLoyalty['points'] ?? 0) ?></span>
                </a>
                <?php endif; ?>

                <!-- Messages -->
                <a href="/messages" class="msg-bell" id="msgBell" title="<?= __('header.messages') ?>" aria-label="<?= __('header.messages') ?>" style="position:relative;display:flex;align-items:center;justify-content:center;padding:6px;color:#555;font-size:20px;text-decoration:none">
                    <i class="fas fa-envelope"></i>
                    <span class="msg-badge" id="msgBadge" style="display:none;position:absolute;top:0;right:0;background:#3b82f6;color:white;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:9px;display:none;align-items:center;justify-content:center">0</span>
                </a>

                <!-- Cloche notifications -->
                <div class="notif-bell" id="notifBell">
                    <button class="notif-bell-btn" onclick="toggleNotifPanel(event)" title="<?= __('header.notifications') ?>" aria-label="<?= __('header.notifications') ?>" aria-expanded="false" aria-haspopup="true">
                        <i class="fas fa-bell" aria-hidden="true"></i>
                        <span class="notif-badge" id="notifBadge" style="display:none" aria-live="polite">0</span>
                    </button>
                    <div class="notif-panel" id="notifPanel" role="menu" aria-label="Notifications">
                        <div class="notif-panel-header">
                            <strong><?= __('header.notifications') ?></strong>
                            <button onclick="markAllRead()" class="notif-mark-read" aria-label="<?= __('header.mark_all_read') ?>"><?= __('header.mark_all_read') ?></button>
                        </div>
                        <div class="notif-list" id="notifList" aria-live="polite">
                            <p class="notif-empty"><?= __('header.no_notification') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Menu utilisateur — simplifié -->
                <div class="user-menu" id="userMenu">
                    <button class="user-trigger" onclick="toggleUserMenu(event)" aria-label="Menu utilisateur" aria-expanded="false" aria-haspopup="true">
                        <div class="user-avatar">
                            <?= strtoupper(substr($currentUser['prenom'], 0, 1)) ?>
                        </div>
                    </button>

                    <div class="user-dropdown" role="menu" aria-label="Menu utilisateur">
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars($currentUser['prenom'] . ' ' . $currentUser['nom']) ?></strong>
                            <span><?= htmlspecialchars($currentUser['email']) ?></span>
                            <?php if ($userLoyalty): ?>
                            <div class="dropdown-loyalty">
                                <span class="loyalty-badge-mini"><?= $userLoyalty['icon'] ?? '⭐' ?> <?= htmlspecialchars($userLoyalty['badge'] ?? 'Débutant') ?></span>
                                <span class="loyalty-points-mini"><?= number_format($userLoyalty['points'] ?? 0) ?> pts</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="/profil" class="dropdown-item"><i class="fas fa-user"></i> <?= __('header.profile') ?></a>
                        <a href="/mes-commandes" class="dropdown-item"><i class="fas fa-receipt"></i> <?= __('header.orders') ?></a>
                        <a href="/wishlist" class="dropdown-item"><i class="fas fa-heart"></i> <?= __('header.favorites') ?></a>
                        <a href="/mes-collections" class="dropdown-item"><i class="fas fa-layer-group"></i> <?= __('header.collections') ?></a>
                        <a href="/fidelite" class="dropdown-item"><i class="fas fa-trophy"></i> <?= __('header.loyalty') ?></a>
                        <a href="/messages" class="dropdown-item"><i class="fas fa-envelope"></i> <?= __('header.messages') ?></a>
                        <a href="/preferences" class="dropdown-item"><i class="fas fa-sliders-h"></i> <?= __('header.preferences') ?></a>
                        <?php if (!empty($ownedRestaurants) || ($currentUser['is_admin'] ?? false)): ?>
                            <div class="dropdown-divider"></div>
                            <a href="/dashboard" class="dropdown-item"><i class="fas fa-chart-line"></i> <?= __('header.dashboard') ?></a>
                        <?php endif; ?>
                        <?php if ($currentUser['is_admin'] ?? false): ?>
                            <a href="/admin" class="dropdown-item dropdown-admin"><i class="fas fa-cog"></i> <?= __('header.admin') ?></a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <button class="dropdown-item" onclick="toggleDarkMode()" style="width:100%;text-align:left;background:none;border:none;cursor:pointer;font:inherit;color:inherit;padding:10px 16px;display:flex;align-items:center;gap:10px"><i class="fas fa-moon"></i> <span id="darkModeLabel"><?= __('header.dark_mode') ?></span></button>
                        <div class="dropdown-divider"></div>
                        <a href="/logout" class="dropdown-item dropdown-logout"><i class="fas fa-sign-out-alt"></i> <?= __('header.logout') ?></a>
                    </div>
                </div>
            <?php else: ?>
                <button class="header-login-btn" onclick="openAuthModal()" aria-label="<?= __('nav.login') ?>">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    <span><?= __('nav.login') ?></span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- CONCIERGE BAR (Fixed bottom assistant) -->
<?php if (($_SERVER['REQUEST_URI'] ?? '') !== '/concierge'): ?>
<div class="concierge-bar" id="conciergeBar">
    <a href="/concierge" class="concierge-bar-inner" aria-label="<?= __('concierge.title') ?>">
        <div class="concierge-bar-icon"><i class="fas fa-robot"></i></div>
        <span class="concierge-bar-text"><?= __('concierge.placeholder') ?></span>
        <div class="concierge-bar-arrow"><i class="fas fa-arrow-right"></i></div>
    </a>
</div>
<?php endif; ?>

<!-- OVERLAY + MODAL AUTH -->
<div class="auth-overlay" id="authOverlay" onclick="closeAuthModal()" aria-hidden="true"></div>
<div class="auth-modal" id="authModal" role="dialog" aria-modal="true" aria-label="Authentification">
    <button class="auth-close" onclick="closeAuthModal()" aria-label="<?= __('common.close') ?>"><i class="fas fa-times" aria-hidden="true"></i></button>

    <div class="auth-modal-logo">
        <svg width="56" height="56" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <rect width="48" height="48" rx="12" fill="#00635a"/>
            <path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
            <path d="M28 12v6c0 3 2 5 4 5v-11" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M32 23v13" stroke="white" stroke-width="2" stroke-linecap="round"/>
            <circle cx="24" cy="24" r="21" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" fill="none"/>
        </svg>
        <div style="font-size:22px;font-weight:800;color:#1f2937;margin-top:8px">LeBon<span style="color:#00635a">Resto</span></div>
    </div>

    <!-- Step Email -->
    <div class="auth-step" id="authStepEmail">
        <h2 class="auth-title"><?= __('auth.identify') ?></h2>

        <div class="auth-form-group">
            <label>Email ou nom d'utilisateur <span class="required">*</span></label>
            <input type="text" id="authEmail" placeholder="<?= __('auth.email_placeholder') ?>" required>
        </div>

        <button class="auth-btn auth-btn-primary" onclick="checkEmail()"><?= __('auth.continue') ?></button>

        <p style="text-align:center;margin-top:16px;font-size:14px;color:#666;">
            <?= __('auth.no_account') ?> <a href="#" onclick="showStep('register')" style="color:#00635a;font-weight:600;"><?= __('auth.create_account') ?></a>
        </p>

        <div class="auth-divider"><span><?= __('auth.or') ?></span></div>

        <button class="auth-btn auth-btn-social auth-btn-facebook" onclick="alert('Bientôt disponible')">
            <i class="fab fa-facebook-f"></i> <?= __('auth.fb_login') ?>
        </button>
        <button class="auth-btn auth-btn-social auth-btn-google" onclick="alert('Bientôt disponible')">
            <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            <?= __('auth.google_login') ?>
        </button>
    </div>

    <!-- Step Login -->
    <div class="auth-step" id="authStepLogin" style="display:none;">
        <button class="auth-back" onclick="showStep('email')"><i class="fas fa-arrow-left"></i> <?= __('common.back') ?></button>
        <h2 class="auth-title"><?= __('auth.welcome_back') ?></h2>
        <p class="auth-subtitle" id="loginEmailDisplay"></p>

        <form id="loginForm" onsubmit="submitLogin(event)">
            <?= csrf_field() ?>
            <input type="hidden" name="email" id="loginEmailInput">
            <div class="auth-form-group">
                <label><?= __('auth.password') ?> <span class="required">*</span></label>
                <div class="password-field">
                    <input type="password" id="loginPassword" name="password" placeholder="<?= __('auth.password') ?>" required>
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('loginPassword')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <a href="/forgot-password" class="auth-forgot"><?= __('auth.forgot_password') ?></a>
            <div class="auth-message error" id="loginError"></div>
            <button type="submit" class="auth-btn auth-btn-primary" id="loginSubmitBtn"><?= __('auth.login_btn') ?></button>
        </form>
    </div>

    <!-- Step Register -->
    <div class="auth-step" id="authStepRegister" style="display:none;">
        <button class="auth-back" onclick="showStep('email')"><i class="fas fa-arrow-left"></i> <?= __('common.back') ?></button>
        <h2 class="auth-title"><?= __('auth.register_title') ?></h2>
        <p class="auth-subtitle" id="registerEmailDisplay"></p>

        <form id="registerForm" onsubmit="submitRegister(event)">
            <?= csrf_field() ?>
            <input type="hidden" name="email" id="registerEmailInput">
            <input type="hidden" name="ref" id="registerRef" value="<?= htmlspecialchars($_GET['ref'] ?? '') ?>">
            <div class="auth-form-row">
                <div class="auth-form-group">
                    <label><?= __('auth.firstname') ?> <span class="required">*</span></label>
                    <input type="text" id="registerPrenom" name="prenom" placeholder="<?= __('auth.firstname') ?>" required>
                </div>
                <div class="auth-form-group">
                    <label><?= __('auth.lastname') ?> <span class="required">*</span></label>
                    <input type="text" id="registerNom" name="nom" placeholder="<?= __('auth.lastname') ?>" required>
                </div>
            </div>
            <div class="auth-form-group">
                <label>Email <span class="required">*</span></label>
                <input type="email" id="registerEmailField" name="email" placeholder="Email">
            </div>
            <div class="auth-form-group">
                <label><?= __('auth.username') ?> <span class="required">*</span></label>
                <input type="text" id="registerUsername" name="username" placeholder="Ex: foodlover123" required>
            </div>
            <div class="auth-form-group">
                <label><?= __('auth.password') ?> <span class="required">*</span></label>
                <div class="password-field">
                    <input type="password" id="registerPassword" name="password" placeholder="Min. 6 caractères" required minlength="6">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('registerPassword')"><i class="fas fa-eye"></i></button>
                </div>
            </div>
            <div class="auth-form-group">
                <label><?= __('auth.confirm_password') ?> <span class="required">*</span></label>
                <input type="password" id="registerPasswordConfirm" name="password_confirm" placeholder="<?= __('auth.confirm_password') ?>" required>
            </div>
            <div class="auth-message error" id="registerError"></div>
            <button type="submit" class="auth-btn auth-btn-primary" id="registerSubmitBtn"><?= __('auth.register_btn') ?></button>
        </form>
    </div>
</div>

<style>
/* ═══════════════════════════════════════════════════════════════════ */
/* HEADER                                                             */
/* ═══════════════════════════════════════════════════════════════════ */
.thefork-header {
    background: white;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.header-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 24px;
    height: 64px;
    display: flex;
    align-items: center;
    gap: 24px;
}

/* Logo */
.header-logo {
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.logo-svg { flex-shrink: 0; }
.logo-text {
    font-size: 20px;
    font-weight: 800;
    color: #1f2937;
    letter-spacing: -0.3px;
}
.logo-text-accent { color: #00635a; }

/* Navigation centrale */
.header-nav {
    display: flex;
    align-items: center;
    gap: 4px;
    margin: 0 auto;
}
.nav-link {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
    color: #6b7280;
    border-radius: 8px;
    transition: all 0.2s;
    white-space: nowrap;
    text-decoration: none;
}
.nav-link i { font-size: 13px; }
.nav-link:hover { color: #00635a; background: #f0fdf4; }
.nav-link.active { color: #00635a; background: #f0fdf4; font-weight: 600; }

/* Actions droite */
.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

/* Bouton connexion (non connecté) */
.header-login-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #00635a;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
    white-space: nowrap;
}
.header-login-btn:hover { background: #004d46; }

/* ═══════════════════════════════════════════════════════════════════ */
/* WIDGET FIDÉLITÉ                                                    */
/* ═══════════════════════════════════════════════════════════════════ */
.loyalty-widget {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #fef3c7;
    padding: 6px 12px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 700;
    font-size: 13px;
    color: #92400e;
    transition: all 0.2s;
}
.loyalty-widget:hover { background: #fde68a; }
.loyalty-icon { font-size: 14px; }
.loyalty-points { font-weight: 700; }

/* Dropdown fidélité */
.dropdown-loyalty {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px dashed #e0e0e0;
}
.loyalty-badge-mini { font-size: 12px; font-weight: 600; color: #00635a; }
.loyalty-points-mini {
    font-size: 12px;
    font-weight: 700;
    color: #92400e;
    background: #fef3c7;
    padding: 2px 8px;
    border-radius: 10px;
}

/* ═══════════════════════════════════════════════════════════════════ */
/* USER MENU                                                          */
/* ═══════════════════════════════════════════════════════════════════ */
.user-menu { position: relative; }
.user-trigger { cursor: pointer; background: none; border: none; padding: 0; }
.user-avatar {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #00635a, #00897b);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    transition: box-shadow 0.2s;
}
.user-trigger:hover .user-avatar { box-shadow: 0 0 0 3px rgba(0,99,90,0.2); }

.user-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    min-width: 240px;
    max-height: 80vh;
    overflow-y: auto;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all 0.2s;
    z-index: 1000;
}
.user-menu.open .user-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.dropdown-header { padding: 16px; border-bottom: 1px solid #f0f0f0; }
.dropdown-header strong { display: block; font-size: 15px; }
.dropdown-header span { font-size: 13px; color: #666; }

.dropdown-divider { height: 1px; background: #f0f0f0; margin: 4px 0; }

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 16px;
    font-size: 14px;
    color: #333;
    transition: background 0.2s;
    text-decoration: none;
}
.dropdown-item:hover { background: #f9fafb; }
.dropdown-item i { width: 18px; text-align: center; color: #6b7280; font-size: 13px; }

.dropdown-admin { color: #8b5cf6; }
.dropdown-admin i { color: #8b5cf6; }
.dropdown-logout { color: #dc2626; }
.dropdown-logout i { color: #dc2626; }

/* ═══════════════════════════════════════════════════════════════════ */
/* CONCIERGE BAR                                                      */
/* ═══════════════════════════════════════════════════════════════════ */
.concierge-bar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 999;
    width: 90%;
    max-width: 480px;
}
.concierge-bar-inner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: white;
    border: 2px solid #00635a;
    border-radius: 50px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    text-decoration: none;
    color: #374151;
    transition: all 0.2s;
    cursor: pointer;
}
.concierge-bar-inner:hover {
    box-shadow: 0 6px 28px rgba(0,99,90,0.2);
    border-color: #004d40;
}
.concierge-bar-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #00635a, #00897b);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.concierge-bar-text {
    flex: 1;
    font-size: 14px;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.concierge-bar-arrow {
    width: 32px;
    height: 32px;
    background: #00635a;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
    transition: background 0.2s;
}
.concierge-bar-inner:hover .concierge-bar-arrow { background: #004d40; }

/* ═══════════════════════════════════════════════════════════════════ */
/* RESPONSIVE                                                         */
/* ═══════════════════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .desktop-only { display: none !important; }
    .header-container { padding: 0 12px; gap: 8px; }
    .logo-svg { width: 30px; height: 30px; }
    .logo-text { font-size: 17px; }
    .loyalty-widget { padding: 5px 8px; font-size: 12px; }
    .concierge-bar { bottom: 72px; width: 92%; }
    .concierge-bar-inner { padding: 10px 14px; }
    .concierge-bar-text { font-size: 13px; }
}
@media (min-width: 769px) and (max-width: 1024px) {
    .nav-link span { display: none; }
    .nav-link { padding: 8px 12px; }
}

/* ═══════════════════════════════════════════════════════════════════ */
/* AUTH MODAL */
/* ═══════════════════════════════════════════════════════════════════ */
.auth-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 2000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}
.auth-overlay.show { opacity: 1; visibility: visible; }

.auth-modal {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.95);
    background: white;
    padding: 40px;
    border-radius: 16px;
    width: 90%;
    max-width: 440px;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 2001;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}
.auth-modal.show { opacity: 1; visibility: visible; transform: translate(-50%, -50%) scale(1); }

.auth-close {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
}
.auth-close:hover { background: #e0e0e0; }

.auth-modal-logo { text-align: center; font-size: 48px; margin-bottom: 24px; }

.auth-title { font-size: 24px; font-weight: 700; text-align: center; margin-bottom: 8px; }
.auth-subtitle { text-align: center; color: #666; margin-bottom: 24px; font-size: 14px; }

.auth-back {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 14px;
    margin-bottom: 20px;
    cursor: pointer;
}
.auth-back:hover { color: #333; }

.auth-form-group { margin-bottom: 16px; }
.auth-form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
.auth-form-group label .required { color: #dc2626; }
.auth-form-group input {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 15px;
    transition: border 0.2s;
}
.auth-form-group input:focus { border-color: #00635a; outline: none; }

.auth-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.password-field { position: relative; }
.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #999;
    cursor: pointer;
}

.auth-forgot {
    display: block;
    text-align: right;
    font-size: 13px;
    color: #00635a;
    margin-bottom: 16px;
}

.auth-btn {
    width: 100%;
    padding: 14px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    margin-bottom: 10px;
}
.auth-btn-primary { background: #00635a; color: white; }
.auth-btn-primary:hover { background: #004d46; }

.auth-divider {
    display: flex;
    align-items: center;
    margin: 20px 0;
    color: #999;
    font-size: 13px;
}
.auth-divider::before, .auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e0e0e0;
}
.auth-divider span { padding: 0 12px; }

.auth-btn-social {
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.auth-btn-facebook { border: 1px solid #1877f2; color: #1877f2; }
.auth-btn-google { border: 1px solid #ddd; color: #333; }

.auth-message { padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 16px; display: none; }
.auth-message.error { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.auth-message.show { display: block; }

/* Header icon buttons */
.header-icon-btn { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: #555; font-size: 18px; transition: all 0.2s; }
.header-icon-btn:hover { background: #f0fdf4; color: #00635a; }

/* Notifications bell */
.notif-bell { position: relative; }
.notif-bell-btn { background: none; border: none; cursor: pointer; font-size: 20px; color: #555; position: relative; padding: 6px; }
.notif-bell-btn:hover { color: #00635a; }
.notif-badge { position: absolute; top: 0; right: 0; background: #ef4444; color: white; font-size: 10px; font-weight: 700; min-width: 18px; height: 18px; border-radius: 9px; display: flex; align-items: center; justify-content: center; }
.notif-panel { position: absolute; top: calc(100% + 10px); right: 0; background: white; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); width: 320px; max-height: 400px; overflow: hidden; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s; z-index: 1001; }
.notif-bell.open .notif-panel { opacity: 1; visibility: visible; transform: translateY(0); }
.notif-panel-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-bottom: 1px solid #f0f0f0; }
.notif-panel-header strong { font-size: 15px; }
.notif-mark-read { background: none; border: none; color: #00635a; font-size: 12px; cursor: pointer; font-weight: 600; }
.notif-list { max-height: 340px; overflow-y: auto; }
.notif-empty { text-align: center; color: #9ca3af; padding: 24px; font-size: 14px; }
.notif-item { display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f5f5f5; font-size: 13px; }
.notif-item.unread { background: #f0fdf4; }
.notif-item-icon { width: 32px; height: 32px; border-radius: 50%; background: #e0f2fe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 14px; }
.notif-item-body { flex: 1; }
.notif-item-title { font-weight: 600; color: #1f2937; }
.notif-item-time { color: #9ca3af; font-size: 11px; margin-top: 2px; }
</style>

<script>
// User menu
function toggleUserMenu(e) {
    e.stopPropagation();
    const menu = document.getElementById('userMenu');
    menu?.classList.toggle('open');
    const btn = menu?.querySelector('.user-trigger');
    if (btn) btn.setAttribute('aria-expanded', menu.classList.contains('open'));
}
document.addEventListener('click', function(e) {
    const menu = document.getElementById('userMenu');
    if (menu && !menu.contains(e.target)) menu.classList.remove('open');
});


// Modal auth
function openAuthModal() {
    document.getElementById('authOverlay').classList.add('show');
    document.getElementById('authModal').classList.add('show');
    document.body.style.overflow = 'hidden';
    showStep('email');
}

function closeAuthModal() {
    document.getElementById('authOverlay').classList.remove('show');
    document.getElementById('authModal').classList.remove('show');
    document.body.style.overflow = '';
}

function showStep(step) {
    document.getElementById('authStepEmail').style.display = step === 'email' ? 'block' : 'none';
    document.getElementById('authStepLogin').style.display = step === 'login' ? 'block' : 'none';
    document.getElementById('authStepRegister').style.display = step === 'register' ? 'block' : 'none';
    document.querySelectorAll('.auth-message').forEach(el => el.classList.remove('show'));
}

async function checkEmail() {
    const identifier = document.getElementById('authEmail').value.trim();
    if (!identifier) { alert('Veuillez entrer votre email ou pseudo'); return; }

    try {
        const response = await fetch('/api/check-email?email=' + encodeURIComponent(identifier));
        const data = await response.json();

        if (data.exists) {
            document.getElementById('loginEmailDisplay').textContent = identifier;
            document.getElementById('loginEmailInput').value = identifier;
            showStep('login');
        } else {
            document.getElementById('registerEmailDisplay').textContent = identifier;
            document.getElementById('registerEmailInput').value = identifier;
            document.getElementById('registerEmailField').value = identifier.includes('@') ? identifier : '';
            showStep('register');
        }
    } catch (e) {
        document.getElementById('registerEmailField').value = identifier.includes('@') ? identifier : '';
        showStep('register');
    }
}

async function submitLogin(e) {
    e.preventDefault();
    const btn = document.getElementById('loginSubmitBtn');
    const errorDiv = document.getElementById('loginError');
    btn.disabled = true; btn.textContent = 'Connexion...';
    errorDiv.classList.remove('show');

    try {
        const response = await fetch('/login', { method: 'POST', body: new FormData(document.getElementById('loginForm')) });
        const data = await response.json();
        if (data.success) {
            window.location.href = data.redirect || '/';
        } else {
            errorDiv.textContent = data.message || 'Erreur';
            errorDiv.classList.add('show');
            btn.disabled = false; btn.textContent = 'Se connecter';
        }
    } catch (e) {
        errorDiv.textContent = 'Erreur de connexion';
        errorDiv.classList.add('show');
        btn.disabled = false; btn.textContent = 'Se connecter';
    }
}

async function submitRegister(e) {
    e.preventDefault();
    const btn = document.getElementById('registerSubmitBtn');
    const errorDiv = document.getElementById('registerError');

    const pwd = document.getElementById('registerPassword').value;
    const pwdConfirm = document.getElementById('registerPasswordConfirm').value;
    if (pwd !== pwdConfirm) { errorDiv.textContent = 'Les mots de passe ne correspondent pas'; errorDiv.classList.add('show'); return; }

    btn.disabled = true; btn.textContent = 'Création...';
    errorDiv.classList.remove('show');

    try {
        const response = await fetch('/register', { method: 'POST', body: new FormData(document.getElementById('registerForm')) });
        const data = await response.json();
        if (data.success) {
            alert(data.message || 'Inscription réussie !');
            window.location.href = data.redirect || '/';
        } else {
            errorDiv.textContent = data.message || 'Erreur';
            errorDiv.classList.add('show');
            btn.disabled = false; btn.textContent = 'Créer mon compte';
        }
    } catch (e) {
        errorDiv.textContent = 'Erreur';
        errorDiv.classList.add('show');
        btn.disabled = false; btn.textContent = 'Créer mon compte';
    }
}

function togglePasswordVisibility(id) {
    const input = document.getElementById(id);
    const icon = input.parentElement.querySelector('.password-toggle i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAuthModal(); });

// Notifications
function toggleNotifPanel(e) {
    e.stopPropagation();
    const bell = document.getElementById('notifBell');
    bell.classList.toggle('open');
    const btn = bell.querySelector('.notif-bell-btn');
    if (btn) btn.setAttribute('aria-expanded', bell.classList.contains('open'));
    if (bell.classList.contains('open')) loadNotifications();
}
document.addEventListener('click', function(e) {
    const bell = document.getElementById('notifBell');
    if (bell && !bell.contains(e.target)) bell.classList.remove('open');
});

async function loadNotifications() {
    try {
        const res = await fetch('/api/notifications');
        const data = await res.json();
        if (!data.success) return;
        const list = document.getElementById('notifList');
        const badge = document.getElementById('notifBadge');
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
        if (data.notifications.length === 0) {
            list.innerHTML = '<p class="notif-empty">Aucune notification</p>';
            return;
        }
        const icons = {new_review: '⭐', claim_approved: '✅', claim_rejected: '❌', badge_earned: '🏆', rating_drop: '📉', review_approved: '✅', review_rejected: '❌', owner_response: '💬', qa_answer: '💡', new_question: '❓', reservation_request: '📅', reservation_response: '📋', order_placed: '📦', order_confirmed: '✅', order_preparing: '🍳', order_ready: '🍽️', order_delivering: '🚚', order_delivered: '✅', order_cancelled: '❌', order_refused: '❌', new_message: '✉️'};
        list.innerHTML = data.notifications.map(n => {
            const d = n.data || {};
            const isOrder = n.type && n.type.startsWith('order_');
            const isMessage = n.type === 'new_message';
            const link = isOrder ? '/mes-commandes' : isMessage ? '/messages' : (d.restaurant_id ? `/restaurant/${d.restaurant_id}` : '#');
            return `
            <a href="${link}" class="notif-item ${n.read_at ? '' : 'unread'}" style="text-decoration:none;color:inherit;">
                <div class="notif-item-icon">${icons[n.type] || '🔔'}</div>
                <div class="notif-item-body">
                    <div class="notif-item-title">${n.title}</div>
                    ${n.message ? `<div style="font-size:12px;color:#6b7280;margin-top:2px;">${n.message}</div>` : ''}
                    <div class="notif-item-time">${new Date(n.created_at).toLocaleDateString('fr-FR')}</div>
                </div>
            </a>`;
        }).join('');
    } catch(e) {}
}

async function markAllRead() {
    await fetch('/api/notifications/read', {method:'POST'});
    document.getElementById('notifBadge').style.display = 'none';
    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
}

// Charger les badges au démarrage si connecté
<?php if ($currentUser): ?>
(async function() {
    try {
        const res = await fetch('/api/notifications');
        const data = await res.json();
        if (data.success && data.unread_count > 0) {
            const badge = document.getElementById('notifBadge');
            badge.textContent = data.unread_count;
            badge.style.display = 'flex';
        }
    } catch(e) {}
    // Unread messages count
    try {
        const res = await fetch('/api/messages/unread-count');
        const data = await res.json();
        if (data.success && data.count > 0) {
            const badge = document.getElementById('msgBadge');
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            }
        }
    } catch(e) {}
})();
<?php endif; ?>

// Language selector
function toggleLangMenu(e) {
    e.stopPropagation();
    var dd = document.getElementById('langDropdown');
    dd.style.display = dd.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    var dd = document.getElementById('langDropdown');
    var sel = document.getElementById('langSelector');
    if (dd && sel && !sel.contains(e.target)) dd.style.display = 'none';
});
function setLang(locale) {
    fetch('/api/locale', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''},
        body: JSON.stringify({locale: locale})
    }).then(function() {
        document.getElementById('currentLangLabel').textContent = locale.toUpperCase();
        document.getElementById('langDropdown').style.display = 'none';
        window.location.reload();
    }).catch(function() {
        document.getElementById('langDropdown').style.display = 'none';
    });
}
</script>

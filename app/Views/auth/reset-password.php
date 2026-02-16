<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Réinitialisation mot de passe' ?> - LeBonResto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .icon {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            font-size: 28px;
            color: #1e293b;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #475569;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        input {
            width: 100%;
            padding: 14px;
            padding-right: 45px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 18px;
        }
        
        .toggle-password:hover {
            color: #475569;
        }
        
        .password-strength {
            margin-top: 8px;
            font-size: 12px;
        }
        
        .strength-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }
        
        .strength-weak { background: #ef4444; width: 33%; }
        .strength-medium { background: #f59e0b; width: 66%; }
        .strength-strong { background: #10b981; width: 100%; }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        
        .error-page {
            text-align: center;
            padding: 40px 20px;
        }
        
        .error-page .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .error-page h2 {
            color: #ef4444;
            margin-bottom: 10px;
        }
        
        .error-page p {
            color: #64748b;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($error) && $error): ?>
            <!-- Token invalide ou expiré -->
            <div class="error-page">
                <div class="icon">⚠️</div>
                <h2>Lien invalide</h2>
                <p><?= htmlspecialchars($error) ?></p>
                <a href="/forgot-password" class="btn">Demander un nouveau lien</a>
                <div class="back-link">
                    <a href="/login">
                        <i class="fas fa-arrow-left"></i> Retour à la connexion
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Formulaire de reset -->
            <div class="header">
                <div class="icon">🔑</div>
                <h1>Nouveau mot de passe</h1>
                <?php if (isset($prenom)): ?>
                    <p class="subtitle">Bonjour <?= htmlspecialchars($prenom) ?>, choisissez un nouveau mot de passe sécurisé.</p>
                <?php else: ?>
                    <p class="subtitle">Choisissez un nouveau mot de passe sécurisé pour votre compte.</p>
                <?php endif; ?>
            </div>
            
            <div id="message"></div>
            
            <form id="resetPasswordForm">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                
                <div class="form-group">
                    <label for="password">Nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="Minimum 8 caractères"
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password')">
                            <i class="fas fa-eye" id="eye-password"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strength-fill"></div>
                        </div>
                        <span id="strength-text"></span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Confirmer le mot de passe</label>
                    <div class="input-wrapper">
                        <input 
                            type="password" 
                            id="password_confirm" 
                            name="password_confirm" 
                            required 
                            placeholder="Retapez votre mot de passe"
                            autocomplete="new-password"
                        >
                        <button type="button" class="toggle-password" onclick="togglePassword('password_confirm')">
                            <i class="fas fa-eye" id="eye-password_confirm"></i>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn" id="submitBtn">
                    <i class="fas fa-check"></i> Réinitialiser le mot de passe
                </button>
            </form>
            
            <div class="back-link">
                <a href="/login">
                    <i class="fas fa-arrow-left"></i> Retour à la connexion
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById('eye-' + fieldId);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Indicateur de force du mot de passe
        const passwordInput = document.getElementById('password');
        const strengthFill = document.getElementById('strength-fill');
        const strengthText = document.getElementById('strength-text');
        
        if (passwordInput) {
            passwordInput.addEventListener('input', () => {
                const password = passwordInput.value;
                let strength = 0;
                
                if (password.length >= 8) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                strengthFill.className = 'strength-fill';
                
                if (strength === 0 || strength === 1) {
                    strengthFill.classList.add('strength-weak');
                    strengthText.textContent = 'Faible';
                    strengthText.style.color = '#ef4444';
                } else if (strength === 2 || strength === 3) {
                    strengthFill.classList.add('strength-medium');
                    strengthText.textContent = 'Moyen';
                    strengthText.style.color = '#f59e0b';
                } else {
                    strengthFill.classList.add('strength-strong');
                    strengthText.textContent = 'Fort';
                    strengthText.style.color = '#10b981';
                }
            });
        }
        
        // Soumission du formulaire
        const form = document.getElementById('resetPasswordForm');
        
        if (form) {
            const messageDiv = document.getElementById('message');
            const submitBtn = document.getElementById('submitBtn');
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const password = document.getElementById('password').value;
                const passwordConfirm = document.getElementById('password_confirm').value;
                
                if (password !== passwordConfirm) {
                    messageDiv.innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> Les mots de passe ne correspondent pas
                        </div>
                    `;
                    return;
                }
                
                if (password.length < 8) {
                    messageDiv.innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> Le mot de passe doit contenir au moins 8 caractères
                        </div>
                    `;
                    return;
                }
                
                const formData = new FormData(form);
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Réinitialisation...';
                
                try {
                    const response = await fetch('/reset-password', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        messageDiv.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> ${data.message}
                            </div>
                        `;
                        
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 2000);
                    } else {
                        messageDiv.innerHTML = `
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> ${data.message}
                            </div>
                        `;
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-check"></i> Réinitialiser le mot de passe';
                    }
                } catch (error) {
                    messageDiv.innerHTML = `
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> Une erreur est survenue
                        </div>
                    `;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Réinitialiser le mot de passe';
                }
            });
        }
    </script>
</body>
</html>

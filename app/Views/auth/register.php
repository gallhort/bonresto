<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Inscription' ?> - LeBonResto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 480px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, #34e0a1 0%, #00aa6c 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .register-header .logo {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .register-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .register-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .register-form {
            padding: 30px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group label i {
            margin-right: 6px;
            color: #00aa6c;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #34e0a1;
            box-shadow: 0 0 0 4px rgba(52, 224, 161, 0.1);
        }
        
        .form-group input::placeholder {
            color: #aaa;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            font-size: 16px;
        }
        
        .password-toggle:hover {
            color: #333;
        }
        
        .password-strength {
            margin-top: 8px;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        
        .strength-weak { width: 33%; background: #e74c3c; }
        .strength-medium { width: 66%; background: #f39c12; }
        .strength-strong { width: 100%; background: #27ae60; }
        
        .terms-check {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .terms-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: #00aa6c;
        }
        
        .terms-check label {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }
        
        .terms-check a {
            color: #00aa6c;
            text-decoration: none;
        }
        
        .terms-check a:hover {
            text-decoration: underline;
        }
        
        .btn-register {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #34e0a1 0%, #00aa6c 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 170, 108, 0.4);
        }
        
        .btn-register:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-register .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        .btn-register.loading .spinner {
            display: block;
        }
        
        .btn-register.loading .btn-text {
            display: none;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #999;
            font-size: 13px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            padding: 0 15px;
        }
        
        .login-link {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            font-size: 14px;
            color: #666;
        }
        
        .login-link a {
            color: #00aa6c;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #ffe6e6;
            color: #c0392b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .error-message.show {
            display: block;
        }
        
        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .success-message.show {
            display: block;
        }
        
        .back-home {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.9);
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            color: #333;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .back-home:hover {
            transform: translateX(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        @media (max-width: 520px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-group.full-width {
                grid-column: span 1;
            }
            
            .register-header {
                padding: 25px 20px;
            }
            
            .register-form {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <a href="/" class="back-home">
        <i class="fas fa-arrow-left"></i> Accueil
    </a>
    
    <div class="register-container">
        <div class="register-header">
            <div class="logo"><svg width="48" height="48" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#00635a"/><path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M28 12v6c0 3 2 5 4 5v-11" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 23v13" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></div>
            <h1>Créer un compte</h1>
            <p>Rejoignez la communauté LeBonResto</p>
        </div>
        
        <form class="register-form" id="registerForm">
            <?= csrf_field() ?>
            
            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom"><i class="fas fa-user"></i> Prénom</label>
                    <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
                </div>
                
                <div class="form-group">
                    <label for="nom"><i class="fas fa-user"></i> Nom</label>
                    <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="username"><i class="fas fa-at"></i> Nom d'utilisateur</label>
                <input type="text" id="username" name="username" placeholder="Ex: foodlover123" required>
            </div>
            
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" placeholder="votre@email.com" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required minlength="8">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <div class="password-strength">
                        <div class="password-strength-bar" id="strengthBar"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm"><i class="fas fa-lock"></i> Confirmer</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirm', this)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="genre"><i class="fas fa-venus-mars"></i> Genre</label>
                <select id="genre" name="genre">
                    <option value="">-- Sélectionner --</option>
                    <option value="homme">Homme</option>
                    <option value="femme">Femme</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            
            <div class="terms-check">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">
                    J'accepte les <a href="/terms" target="_blank">conditions d'utilisation</a> 
                    et la <a href="/privacy" target="_blank">politique de confidentialité</a>
                </label>
            </div>
            
            <button type="submit" class="btn-register" id="submitBtn">
                <span class="btn-text"><i class="fas fa-user-plus"></i> Créer mon compte</span>
                <span class="spinner"></span>
            </button>
        </form>
        
        <div class="login-link">
            Déjà inscrit ? <a href="/login">Se connecter</a>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
        
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            
            strengthBar.className = 'password-strength-bar';
            
            if (password.length === 0) {
                strengthBar.style.width = '0';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                strengthBar.classList.add('strength-weak');
            } else if (strength <= 4) {
                strengthBar.classList.add('strength-medium');
            } else {
                strengthBar.classList.add('strength-strong');
            }
        });
        
        // Form submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            
            // Validation
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirm').value;
            
            if (password !== confirmPassword) {
                errorDiv.textContent = 'Les mots de passe ne correspondent pas';
                errorDiv.classList.add('show');
                return;
            }
            
            if (password.length < 6) {
                errorDiv.textContent = 'Le mot de passe doit contenir au moins 6 caractères';
                errorDiv.classList.add('show');
                return;
            }
            
            // Submit
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            errorDiv.classList.remove('show');
            successDiv.classList.remove('show');
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.textContent = data.message || 'Inscription réussie !';
                    successDiv.classList.add('show');
                    
                    setTimeout(() => {
                        window.location.href = data.redirect || '/login';
                    }, 1500);
                } else {
                    errorDiv.textContent = data.message || 'Erreur lors de l\'inscription';
                    errorDiv.classList.add('show');
                    submitBtn.classList.remove('loading');
                    submitBtn.disabled = false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                errorDiv.textContent = 'Erreur de connexion au serveur';
                errorDiv.classList.add('show');
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - LeBonResto</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Trip Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f2f2f2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
      
        
        /* Main Container */
        .auth-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        
        .auth-box {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            width: 100%;
            max-width: 420px;
            padding: 32px;
        }
        
        .auth-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a1a1a;
            text-align: center;
            margin-bottom: 24px;
        }
        
        /* Social Buttons */
        .social-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 40px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
            width: 100%;
        }
        
        .social-btn i {
            font-size: 18px;
        }
        
        .social-btn.google {
            background: white;
            border: 1px solid #dadce0;
            color: #3c4043;
        }
        
        .social-btn.google:hover {
            background: #f8f9fa;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .social-btn.facebook {
            background: #1877f2;
            color: white;
        }
        
        .social-btn.facebook:hover {
            background: #166fe5;
        }
        
        .social-btn.apple {
            background: #000;
            color: white;
        }
        
        .social-btn.apple:hover {
            background: #333;
        }
        
        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            padding: 0 16px;
            color: #717171;
            font-size: 14px;
        }
        
        /* Form */
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        
        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .form-group input {
            padding: 14px 16px;
            border: 1px solid #b0b0b0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #00aa6c;
            box-shadow: 0 0 0 2px rgba(0,170,108,0.2);
        }
        
        .form-group input::placeholder {
            color: #b0b0b0;
        }
        
        .password-field {
            position: relative;
        }
        
        .password-field input {
            padding-right: 48px;
        }
        
        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #717171;
            cursor: pointer;
            font-size: 16px;
        }
        
        .password-toggle:hover {
            color: #1a1a1a;
        }
        
        .forgot-link {
            text-align: right;
        }
        
        .forgot-link a {
            font-size: 14px;
            color: #1a1a1a;
            text-decoration: none;
            font-weight: 500;
        }
        
        .forgot-link a:hover {
            text-decoration: underline;
        }
        
        /* Submit Button */
        .btn-submit {
            padding: 14px 24px;
            background: #00aa6c;
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 8px;
        }
        
        .btn-submit:hover {
            background: #008c59;
        }
        
        .btn-submit:disabled {
            background: #b0b0b0;
            cursor: not-allowed;
        }
        
        /* Error/Success Messages */
        .message {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 14px;
            display: none;
        }
        
        .message.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }
        
        .message.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }
        
        .message.show {
            display: block;
        }
        
        /* Footer Link */
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e0e0e0;
        }
        
        .auth-footer p {
            font-size: 15px;
            color: #717171;
        }
        
        .auth-footer a {
            color: #1a1a1a;
            font-weight: 600;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        /* Terms */
        .terms-text {
            font-size: 12px;
            color: #717171;
            text-align: center;
            margin-top: 16px;
            line-height: 1.5;
        }
        
        .terms-text a {
            color: #1a1a1a;
            text-decoration: underline;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            .auth-box {
                padding: 24px 20px;
                box-shadow: none;
                background: transparent;
            }
            
            .auth-container {
                background: white;
            }
        }
    </style>
</head>
<body>

    
    <!-- Main -->
    <main class="auth-container">
        <div class="auth-box">
            <h1 class="auth-title">Connexion</h1>
            
            <!-- Social Login -->
            <div class="social-buttons">
                <button class="social-btn google" type="button" onclick="alert('Google login bientôt disponible')">
                    <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continuer avec Google
                </button>
                
                <button class="social-btn facebook" type="button" onclick="alert('Facebook login bientôt disponible')">
                    <i class="fab fa-facebook-f"></i>
                    Continuer avec Facebook
                </button>
            </div>
            
            <div class="divider">
                <span>ou</span>
            </div>
            
            <!-- Form -->
            <form class="auth-form" id="loginForm">
                <?= csrf_field() ?>
                
                <div class="message error" id="errorMessage"></div>
                <div class="message success" id="successMessage"></div>
                
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="text" id="email" name="email" placeholder="E-mail ou nom d'utilisateur" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-field">
                        <input type="password" id="password" name="password" placeholder="Mot de passe" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div class="forgot-link">
                    <a href="/forgot-password">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="btn-submit" id="submitBtn">
                    Se connecter
                </button>
            </form>
            
            <p class="terms-text">
                En vous connectant, vous acceptez nos <a href="/terms">Conditions d'utilisation</a> 
                et notre <a href="/privacy">Politique de confidentialité</a>.
            </p>
            
            <div class="auth-footer">
                <p>Pas encore membre ? <a href="/register">Rejoignez-nous</a></p>
            </div>
        </div>
    </main>
    
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            
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
        
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Connexion...';
            errorDiv.classList.remove('show');
            successDiv.classList.remove('show');
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('/login', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    successDiv.textContent = 'Connexion réussie !';
                    successDiv.classList.add('show');
                    setTimeout(() => {
                        window.location.href = data.redirect || '/';
                    }, 500);
                } else {
                    errorDiv.textContent = data.message || 'Identifiants incorrects';
                    errorDiv.classList.add('show');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Se connecter';
                }
            } catch (error) {
                errorDiv.textContent = 'Erreur de connexion au serveur';
                errorDiv.classList.add('show');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Se connecter';
            }
        });
    </script>
</body>
</html>
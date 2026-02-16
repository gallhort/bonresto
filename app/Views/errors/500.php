<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur serveur - LeBonResto</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00635a;
            --primary-dark: #004d46;
            --gray-50: #f8fafc;
            --gray-500: #64748b;
            --gray-900: #0f172a;
            --font-serif: 'DM Serif Display', Georgia, serif;
            --font-sans: 'Inter', -apple-system, sans-serif;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: var(--font-sans);
            background: var(--gray-50);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-page { text-align: center; padding: 40px 20px; max-width: 560px; }
        .error-icon { font-size: 80px; margin-bottom: 24px; }
        .error-code {
            font-family: var(--font-serif);
            font-size: 96px;
            color: #dc2626;
            line-height: 1;
            margin-bottom: 12px;
        }
        .error-title {
            font-family: var(--font-serif);
            font-size: 28px;
            color: var(--gray-900);
            margin-bottom: 12px;
        }
        .error-message {
            font-size: 16px;
            color: var(--gray-500);
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            font-family: var(--font-sans);
            transition: all 0.2s;
            cursor: pointer;
            border: none;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-outline { background: white; color: var(--gray-900); border: 2px solid #e2e8f0; }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-icon">⚠️</div>
        <div class="error-code">500</div>
        <h1 class="error-title">Oups, quelque chose s'est mal passé</h1>
        <p class="error-message">
            Notre serveur a rencontré une erreur inattendue. Nous travaillons dessus.
            Veuillez réessayer dans quelques instants.
        </p>
        <div class="error-actions">
            <button class="btn btn-primary" onclick="location.reload()">
                <i class="fas fa-redo"></i> Réessayer
            </button>
            <a href="/" class="btn btn-outline">
                <i class="fas fa-home"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>

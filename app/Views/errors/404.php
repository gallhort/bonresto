<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page non trouvée - LeBonResto</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #00635a;
            --primary-dark: #004d46;
            --primary-light: #e8f5f0;
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
        .error-page {
            text-align: center;
            padding: 40px 20px;
            max-width: 560px;
        }
        .error-icon {
            font-size: 80px;
            margin-bottom: 24px;
        }
        .error-code {
            font-family: var(--font-serif);
            font-size: 96px;
            color: var(--primary);
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
        .error-search {
            display: flex;
            align-items: center;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50px;
            padding: 6px 6px 6px 20px;
            max-width: 440px;
            margin: 0 auto 24px;
            transition: border-color 0.2s;
        }
        .error-search:focus-within { border-color: var(--primary); }
        .error-search input {
            flex: 1;
            border: none;
            outline: none;
            font-size: 15px;
            font-family: var(--font-sans);
            padding: 10px 0;
        }
        .error-search button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            font-family: var(--font-sans);
            transition: background 0.2s;
        }
        .error-search button:hover { background: var(--primary-dark); }
        .error-links {
            display: flex;
            gap: 24px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .error-links a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: gap 0.2s;
        }
        .error-links a:hover { gap: 12px; }
    </style>
</head>
<body>
    <div class="error-page">
        <div class="error-icon"><svg width="64" height="64" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#00635a"/><path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M28 12v6c0 3 2 5 4 5v-11" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 23v13" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></div>
        <div class="error-code">404</div>
        <h1 class="error-title">Cette page n'existe pas</h1>
        <p class="error-message">
            Le restaurant ou la page que vous cherchez a peut-être été déplacé ou n'existe plus.
            Essayez de rechercher ce que vous cherchez.
        </p>
        <form class="error-search" action="/search" method="GET">
            <input type="text" name="q" placeholder="Rechercher un restaurant...">
            <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
        </form>
        <div class="error-links">
            <a href="/"><i class="fas fa-home"></i> Accueil</a>
            <a href="/search"><i class="fas fa-utensils"></i> Restaurants</a>
            <a href="/contact"><i class="fas fa-envelope"></i> Contact</a>
        </div>
    </div>
</body>
</html>

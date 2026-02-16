<?php
$uri = $_SERVER['REQUEST_URI'] ?? '';
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo"><svg width="24" height="24" viewBox="0 0 48 48" fill="none"><rect width="48" height="48" rx="12" fill="#00635a"/><path d="M16 12c0 0 0 8 0 12 0 2.5-2 4-2 4l2 8h2l2-8s-2-1.5-2-4c0-4 0-12 0-12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"/><path d="M28 12v6c0 3 2 5 4 5v-11" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M32 23v13" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg> LeBonResto</div>
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
    </div>

    <ul class="sidebar-menu">

        <!-- Dashboard -->
        <li class="menu-item">
            <a href="/admin/dashboard"
               class="menu-link <?= ($uri === '/admin' || $uri === '/admin/dashboard') ? 'active' : '' ?>">
                <i class="menu-icon fas fa-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <!-- Restaurants -->
        <li class="menu-item">
            <a href="/admin/restaurants"
               class="menu-link <?= $uri === '/admin/restaurants' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-utensils"></i>
                <span class="menu-text">Restaurants</span>
            </a>
        </li>

        <!-- Avis -->
        <li class="menu-item">
            <a href="/admin/reviews"
               class="menu-link <?= $uri === '/admin/reviews' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-star"></i>
                <span class="menu-text">Avis</span>
            </a>
        </li>

        <!-- AI stats -->
        <li class="menu-item">
            <a href="/admin/reviews/ai-stats"
               class="menu-link <?= $uri === '/admin/reviews/ai-stats' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-brain"></i>
                <span class="menu-text">AI stats</span>
            </a>
        </li>

        <!-- Utilisateurs -->
        <li class="menu-item">
            <a href="/admin/users"
               class="menu-link <?= $uri === '/admin/users' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-users"></i>
                <span class="menu-text">Utilisateurs</span>
            </a>
        </li>

        <!-- Suggestions -->
        <li class="menu-item">
            <a href="/admin/suggestions"
               class="menu-link <?= str_starts_with($uri, '/admin/suggestions') ? 'active' : '' ?>">
                <i class="menu-icon fas fa-lightbulb"></i>
                <span class="menu-text">Suggestions</span>
            </a>
        </li>

        <!-- Analytics -->
        <li class="menu-item">
            <a href="/admin/analytics"
               class="menu-link <?= $uri === '/admin/analytics' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-chart-line"></i>
                <span class="menu-text">Analytics</span>
            </a>
        </li>

        <!-- Messages contact -->
        <li class="menu-item">
            <a href="/admin/contacts"
               class="menu-link <?= $uri === '/admin/contacts' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-envelope"></i>
                <span class="menu-text">Messages</span>
            </a>
        </li>

        <!-- Journal modération -->
        <li class="menu-item">
            <a href="/admin/moderation-log"
               class="menu-link <?= $uri === '/admin/moderation-log' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-clipboard-list"></i>
                <span class="menu-text">Modération</span>
            </a>
        </li>

        <!-- Paramètres -->
        <li class="menu-item">
            <a href="/admin/settings"
               class="menu-link <?= $uri === '/admin/settings' ? 'active' : '' ?>">
                <i class="menu-icon fas fa-cog"></i>
                <span class="menu-text">Paramètres</span>
            </a>
        </li>

        <!-- Déconnexion -->
        <li class="menu-item"
            style="margin-top:auto; padding-top:20px; border-top:1px solid rgba(255,255,255,0.1);">
            <a href="/logout" class="menu-link">
                <i class="menu-icon fas fa-sign-out-alt"></i>
                <span class="menu-text">Déconnexion</span>
            </a>
        </li>

    </ul>
</aside>

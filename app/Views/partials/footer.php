<!-- FOOTER STYLE THEFORK -->
<footer class="footer-thefork">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- App -->
            <div class="footer-col">
                <h4>Téléchargez notre application</h4>
                <div class="app-badges">
                    <a href="#" class="app-badge">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Download_on_the_App_Store_Badge.svg/200px-Download_on_the_App_Store_Badge.svg.png" alt="App Store">
                    </a>
                    <a href="#" class="app-badge">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/78/Google_Play_Store_badge_EN.svg/200px-Google_Play_Store_badge_EN.svg.png" alt="Google Play">
                    </a>
                </div>
            </div>
            
            <!-- Découvrir -->
            <div class="footer-col">
                <h4><?= __('nav.explore') ?></h4>
                <ul class="footer-links">
                    <li><a href="/search"><i class="fas fa-search fa-fw"></i> <?= __('nav.explore') ?></a></li>
                    <li><a href="/classement-restaurants"><i class="fas fa-crown fa-fw"></i> <?= __('nav.top100') ?></a></li>
                    <li><a href="/comparateur"><i class="fas fa-balance-scale fa-fw"></i> Comparateur</a></li>
                    <li><a href="/stats"><i class="fas fa-chart-pie fa-fw"></i> Statistiques</a></li>
                    <li><a href="/evenements"><i class="fas fa-calendar-alt fa-fw"></i> Événements</a></li>
                    <li><a href="/feed"><i class="fas fa-stream fa-fw"></i> <?= __('nav.feed') ?></a></li>
                </ul>
            </div>

            <!-- Contribuer & Infos -->
            <div class="footer-col">
                <h4>Contribuer</h4>
                <ul class="footer-links">
                    <li><a href="/add-restaurant">Ajouter un restaurant</a></li>
                    <li><a href="/proposer-restaurant">Proposer un resto</a></li>
                    <li><a href="/parrainage">Parrainage</a></li>
                    <li><a href="/premium">Premium</a></li>
                </ul>
                <h4 style="margin-top:20px">Informations</h4>
                <ul class="footer-links">
                    <li><a href="/about">À propos</a></li>
                    <li><a href="/contact">Contact</a></li>
                    <li><a href="/terms">CGU</a></li>
                    <li><a href="/privacy">Confidentialité</a></li>
                </ul>
            </div>
            
            <!-- Social -->
            <div class="footer-col footer-col-right">
                <div class="footer-social">
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                </div>
                <p class="footer-copyright">© <?= date('Y') ?> LEBONRESTO - TOUS DROITS RÉSERVÉS</p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>Les avis sont soumis à vérification. L'abus d'alcool est dangereux pour la santé. À consommer avec modération.</p>
        </div>
    </div>
</footer>

<style>
.footer-thefork {
    background: #1a1a1a;
    color: white;
    padding: 50px 0 30px;
    margin-top: 60px;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
    gap: 40px;
    padding-bottom: 40px;
    border-bottom: 1px solid #333;
}

.footer-col h4 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}

.app-badges {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.app-badge img {
    height: 40px;
    width: auto;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #ccc;
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s;
}

.footer-links a:hover {
    color: white;
}

.footer-col-right {
    text-align: right;
}

.footer-social {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    margin-bottom: 20px;
}

.footer-social a {
    width: 36px;
    height: 36px;
    border: 1px solid #444;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    transition: all 0.2s;
}

.footer-social a:hover {
    background: white;
    color: #1a1a1a;
}

.footer-copyright {
    font-size: 12px;
    color: #999;
}

.footer-bottom {
    padding-top: 20px;
    text-align: center;
}

.footer-bottom p {
    font-size: 12px;
    color: #666;
    line-height: 1.6;
}

@media (max-width: 768px) {
    .footer-grid {
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }
    
    .footer-col-right {
        text-align: left;
        grid-column: span 2;
    }
    
    .footer-social {
        justify-content: flex-start;
    }
}

@media (max-width: 480px) {
    .footer-grid {
        grid-template-columns: 1fr;
    }
    
    .footer-col-right {
        grid-column: span 1;
    }
}
</style>
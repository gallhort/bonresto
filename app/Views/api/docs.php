<style>
    .api-container { max-width: 900px; margin: 0 auto; padding: 32px 20px; }
    .api-hero { background: linear-gradient(135deg, #1e293b, #0f172a); color: #fff; padding: 48px 0; margin: -20px -20px 32px; }
    .api-hero-inner { max-width: 900px; margin: 0 auto; padding: 0 20px; }
    .api-hero h1 { font-size: 28px; margin: 0 0 8px; }
    .api-hero p { opacity: 0.7; margin: 0; font-size: 15px; }
    .api-base { background: #1e293b; display: inline-block; padding: 8px 16px; border-radius: 8px; font-family: monospace; font-size: 14px; color: #67e8f9; margin-top: 12px; }

    .api-section { margin-bottom: 32px; }
    .api-section h2 { font-size: 20px; margin: 0 0 16px; padding-bottom: 8px; border-bottom: 2px solid #e5e7eb; }
    .api-endpoint { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 12px; overflow: hidden; }
    .api-endpoint-header { display: flex; align-items: center; gap: 10px; padding: 14px 18px; cursor: pointer; }
    .api-method { padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 700; font-family: monospace; color: #fff; }
    .api-method.get { background: #10b981; }
    .api-method.post { background: #3b82f6; }
    .api-method.delete { background: #ef4444; }
    .api-path { font-family: monospace; font-size: 14px; color: #374151; }
    .api-desc { margin-left: auto; font-size: 13px; color: #6b7280; }
    .api-endpoint-body { display: none; padding: 0 18px 18px; border-top: 1px solid #f3f4f6; }
    .api-endpoint.open .api-endpoint-body { display: block; }
    .api-param-table { width: 100%; font-size: 13px; border-collapse: collapse; margin-top: 10px; }
    .api-param-table th { text-align: left; padding: 6px 10px; background: #f9fafb; color: #6b7280; font-size: 12px; text-transform: uppercase; }
    .api-param-table td { padding: 6px 10px; border-bottom: 1px solid #f3f4f6; }
    .api-type { font-family: monospace; font-size: 12px; color: #6366f1; }
    .api-required { color: #ef4444; font-size: 11px; font-weight: 600; }
    .api-example { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; font-family: monospace; font-size: 13px; white-space: pre; overflow-x: auto; margin-top: 10px; }
    .api-note { background: #fef3c7; border-left: 3px solid #f59e0b; padding: 10px 14px; border-radius: 0 6px 6px 0; font-size: 13px; color: #92400e; margin-top: 10px; }
</style>

<div class="api-hero">
    <div class="api-hero-inner">
        <h1><i class="fas fa-code"></i> API Publique LeBonResto</h1>
        <p>Accedez aux donnees des restaurants, avis, et plus encore</p>
        <div class="api-base">https://lebonresto.dz/api</div>
    </div>
</div>

<div class="api-container">

    <div class="api-note" style="margin-bottom:24px">
        <strong>Authentification :</strong> La plupart des endpoints GET sont publics. Les endpoints POST necessitent une session authentifiee (cookie de session). L'API retourne du JSON (Content-Type: application/json).
    </div>

    <!-- RESTAURANTS -->
    <div class="api-section">
        <h2><i class="fas fa-utensils"></i> Restaurants</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method get">GET</span>
                <span class="api-path">/api/restaurants</span>
                <span class="api-desc">Liste des restaurants</span>
            </div>
            <div class="api-endpoint-body">
                <table class="api-param-table">
                    <tr><th>Param</th><th>Type</th><th>Description</th></tr>
                    <tr><td>q</td><td class="api-type">string</td><td>Recherche textuelle</td></tr>
                    <tr><td>ville</td><td class="api-type">string</td><td>Filtrer par ville</td></tr>
                    <tr><td>type</td><td class="api-type">string</td><td>Type de cuisine</td></tr>
                    <tr><td>rating</td><td class="api-type">int</td><td>Note minimum (1-5)</td></tr>
                    <tr><td>lat, lng</td><td class="api-type">float</td><td>Coordonnees GPS pour tri par distance</td></tr>
                    <tr><td>page</td><td class="api-type">int</td><td>Page (defaut: 1)</td></tr>
                </table>
                <div class="api-example">GET /api/restaurants?ville=Oran&type=Pizza&rating=4</div>
            </div>
        </div>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method get">GET</span>
                <span class="api-path">/api/restaurants/{id}</span>
                <span class="api-desc">Detail d'un restaurant</span>
            </div>
            <div class="api-endpoint-body">
                <p style="font-size:13px;color:#6b7280">Retourne toutes les informations d'un restaurant : nom, adresse, horaires, photos, note, avis.</p>
                <div class="api-example">GET /api/restaurants/42</div>
            </div>
        </div>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method get">GET</span>
                <span class="api-path">/api/restaurants/filter</span>
                <span class="api-desc">Filtrage AJAX (carte)</span>
            </div>
            <div class="api-endpoint-body">
                <p style="font-size:13px;color:#6b7280">Memes parametres que /api/restaurants, optimise pour la carte interactive.</p>
            </div>
        </div>
    </div>

    <!-- REVIEWS -->
    <div class="api-section">
        <h2><i class="fas fa-star"></i> Avis</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method get">GET</span>
                <span class="api-path">/api/reviews/{restaurant_id}</span>
                <span class="api-desc">Avis d'un restaurant</span>
            </div>
            <div class="api-endpoint-body">
                <table class="api-param-table">
                    <tr><th>Param</th><th>Type</th><th>Description</th></tr>
                    <tr><td>offset</td><td class="api-type">int</td><td>Decalage (defaut: 0)</td></tr>
                    <tr><td>limit</td><td class="api-type">int</td><td>Nombre max (defaut: 5)</td></tr>
                    <tr><td>sort</td><td class="api-type">string</td><td>recent | helpful | rating_high | rating_low</td></tr>
                    <tr><td>rating</td><td class="api-type">int</td><td>Filtrer par note</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- CHECKINS -->
    <div class="api-section">
        <h2><i class="fas fa-map-marker-alt"></i> Check-ins</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method post">POST</span>
                <span class="api-path">/api/restaurants/{id}/checkin</span>
                <span class="api-desc">Effectuer un check-in</span>
            </div>
            <div class="api-endpoint-body">
                <p class="api-required">Authentification requise</p>
                <table class="api-param-table">
                    <tr><th>Body</th><th>Type</th><th>Description</th></tr>
                    <tr><td>lat <span class="api-required">requis</span></td><td class="api-type">float</td><td>Latitude utilisateur</td></tr>
                    <tr><td>lng <span class="api-required">requis</span></td><td class="api-type">float</td><td>Longitude utilisateur</td></tr>
                </table>
                <div class="api-example">POST /api/restaurants/42/checkin
{ "lat": 35.6971, "lng": -0.6308 }</div>
                <div class="api-note">Distance maximale : 200m. Cooldown : 4h entre check-ins au meme endroit.</div>
            </div>
        </div>
    </div>

    <!-- COLLECTIONS -->
    <div class="api-section">
        <h2><i class="fas fa-layer-group"></i> Collections</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method post">POST</span>
                <span class="api-path">/api/collections</span>
                <span class="api-desc">Creer une collection</span>
            </div>
            <div class="api-endpoint-body">
                <table class="api-param-table">
                    <tr><th>Body</th><th>Type</th><th>Description</th></tr>
                    <tr><td>title <span class="api-required">requis</span></td><td class="api-type">string</td><td>Titre (3-150 car.)</td></tr>
                    <tr><td>description</td><td class="api-type">string</td><td>Description optionnelle</td></tr>
                    <tr><td>is_public</td><td class="api-type">int</td><td>1=publique (defaut), 0=privee</td></tr>
                </table>
            </div>
        </div>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method post">POST</span>
                <span class="api-path">/api/collections/{id}/add</span>
                <span class="api-desc">Ajouter un restaurant</span>
            </div>
            <div class="api-endpoint-body">
                <table class="api-param-table">
                    <tr><th>Body</th><th>Type</th><th>Description</th></tr>
                    <tr><td>restaurant_id <span class="api-required">requis</span></td><td class="api-type">int</td><td>ID du restaurant</td></tr>
                    <tr><td>note</td><td class="api-type">string</td><td>Note personnelle</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- RESERVATIONS -->
    <div class="api-section">
        <h2><i class="fas fa-calendar-check"></i> Reservations</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method post">POST</span>
                <span class="api-path">/api/restaurants/{id}/reservation</span>
                <span class="api-desc">Demander une reservation</span>
            </div>
            <div class="api-endpoint-body">
                <div class="api-note">Disponible uniquement pour les restaurants dont le proprietaire a active les reservations.</div>
                <table class="api-param-table">
                    <tr><th>Body</th><th>Type</th><th>Description</th></tr>
                    <tr><td>date <span class="api-required">requis</span></td><td class="api-type">string</td><td>YYYY-MM-DD</td></tr>
                    <tr><td>heure <span class="api-required">requis</span></td><td class="api-type">string</td><td>HH:MM</td></tr>
                    <tr><td>nb_personnes <span class="api-required">requis</span></td><td class="api-type">int</td><td>1-20</td></tr>
                    <tr><td>telephone</td><td class="api-type">string</td><td>Numero de contact</td></tr>
                    <tr><td>message</td><td class="api-type">string</td><td>Message (500 car. max)</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- SUGGESTIONS -->
    <div class="api-section">
        <h2><i class="fas fa-magic"></i> Suggestions</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method get">GET</span>
                <span class="api-path">/api/suggestions</span>
                <span class="api-desc">Restaurants recommandes</span>
            </div>
            <div class="api-endpoint-body">
                <p style="font-size:13px;color:#6b7280">Retourne des suggestions personnalisees basees sur l'historique de l'utilisateur (cuisines preferees, villes frequentees). Si non connecte, retourne les mieux notes.</p>
                <table class="api-param-table">
                    <tr><th>Param</th><th>Type</th><th>Description</th></tr>
                    <tr><td>limit</td><td class="api-type">int</td><td>Nombre max (defaut: 6, max: 10)</td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- FEED -->
    <div class="api-section">
        <h2><i class="fas fa-stream"></i> Fil d'actualite</h2>

        <div class="api-endpoint" onclick="this.classList.toggle('open')">
            <div class="api-endpoint-header">
                <span class="api-method get">GET</span>
                <span class="api-path">/api/feed</span>
                <span class="api-desc">Activites de la communaute</span>
            </div>
            <div class="api-endpoint-body">
                <table class="api-param-table">
                    <tr><th>Param</th><th>Type</th><th>Description</th></tr>
                    <tr><td>offset</td><td class="api-type">int</td><td>Decalage (defaut: 0)</td></tr>
                    <tr><td>limit</td><td class="api-type">int</td><td>Nombre max (defaut: 15, max: 30)</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

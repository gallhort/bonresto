/*jshint esversion: 6 */

/**
 * AutocompleteCity - Système d'autocomplétion moderne et performant
 * Gère la recherche de villes avec dropdown dynamique
 */
class AutocompleteCity {
    constructor(inputId, options = {}) {
        // Configuration
        this.input = document.getElementById(inputId);
        this.gpsInput = document.getElementById(options.gpsInputId || 'currentgps');
        this.searchUrl = options.searchUrl || '/zz/searchCity.php';
        this.minChars = options.minChars || 2;
        this.debounceDelay = options.debounceDelay || 300;
        
        // État
        this.selectedCity = null;
        this.currentResults = [];
        this.selectedIndex = -1;
        this.isLoading = false;
        this.debounceTimer = null;
        
        // Éléments DOM
        this.dropdown = null;
        
        // Initialisation
        this.init();
    }
    
    init() {
        if (!this.input) {
            console.error('Input introuvable');
            return;
        }
        
        // Créer le wrapper si nécessaire
        this.wrapInput();
        
        // Événements input
        this.input.addEventListener('input', () => this.handleInput());
        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.input.addEventListener('focus', () => this.handleFocus());
        
        // Événements globaux
        document.addEventListener('click', (e) => this.handleClickOutside(e));
        window.addEventListener('resize', () => this.closeDropdown());
        window.addEventListener('scroll', () => this.updateDropdownPosition(), true);
        
        // ARIA attributes
        this.input.setAttribute('role', 'combobox');
        this.input.setAttribute('aria-autocomplete', 'list');
        this.input.setAttribute('aria-expanded', 'false');
        this.input.setAttribute('autocomplete', 'off');
    }
    
    wrapInput() {
        // Vérifier si déjà wrappé
        if (this.input.parentElement.classList.contains('autocomplete-wrapper')) {
            return;
        }
        
        const wrapper = document.createElement('div');
        wrapper.className = 'autocomplete-wrapper';
        this.input.parentNode.insertBefore(wrapper, this.input);
        wrapper.appendChild(this.input);
    }
    
    handleInput() {
        clearTimeout(this.debounceTimer);
        
        const query = this.input.value.trim();
        
        // Reset si vide
        if (query.length === 0) {
            this.selectedCity = null;
            if (this.gpsInput) this.gpsInput.value = '';
            this.closeDropdown();
            return;
        }
        
        // Attendre minimum de caractères
        if (query.length < this.minChars) {
            this.closeDropdown();
            return;
        }
        
        // Debounce pour éviter trop de requêtes
        this.debounceTimer = setTimeout(() => {
            this.search(query);
        }, this.debounceDelay);
    }
    
    async search(query) {
        this.isLoading = true;
        this.showLoading();
        
        console.log("🔍 Recherche : " + query);
        
        try {
            const response = await fetch(`${this.searchUrl}?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            this.isLoading = false;
            this.currentResults = data || [];
            this.selectedIndex = -1;
            
            this.displayResults(query);
            
        } catch (error) {
            console.error("❌ Erreur fetch:", error);
            this.isLoading = false;
            this.showError();
        }
    }
    
    displayResults(query) {
        // Créer ou réutiliser le dropdown
        if (!this.dropdown) {
            this.createDropdown();
        }
        
        const list = this.dropdown.querySelector('.autocomplete-list');
        list.innerHTML = '';
        
        // Aucun résultat
        if (this.currentResults.length === 0) {
            const item = document.createElement('div');
            item.className = 'autocomplete-item no-result';
            item.innerHTML = '🔍 Aucun résultat trouvé';
            list.appendChild(item);
            this.openDropdown();
            return;
        }
        
        // Afficher les résultats
        this.currentResults.forEach((city, index) => {
            const item = this.createResultItem(city, query, index);
            list.appendChild(item);
        });
        
        this.openDropdown();
    }
    
    createResultItem(city, query, index) {
        const item = document.createElement('div');
        item.className = 'autocomplete-item';
        item.dataset.index = index;
        item.dataset.gps = city.gps;
        
        // Texte complet
        const fullText = `${city.commune_name_ascii}, ${city.daira_name_ascii}, ${city.wilaya_name_ascii}`;
        
        // Highlight du texte recherché
        const highlightedText = this.highlightMatch(fullText, query);
        
        item.innerHTML = `
            <span class="autocomplete-icon">📍</span>
            <span class="autocomplete-text">${highlightedText}</span>
        `;
        
        // Événements
        item.addEventListener('click', () => this.selectItem(index));
        item.addEventListener('mouseenter', () => this.setSelectedIndex(index));
        
        return item;
    }
    
    highlightMatch(text, query) {
        const regex = new RegExp(`(${this.escapeRegex(query)})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }
    
    escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
    
    createDropdown() {
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'autocomplete-dropdown';
        this.dropdown.id = 'autocomplete-dropdown-' + Date.now();
        
        const list = document.createElement('div');
        list.className = 'autocomplete-list';
        list.setAttribute('role', 'listbox');
        
        this.dropdown.appendChild(list);
        document.body.appendChild(this.dropdown);
        
        // ARIA
        this.input.setAttribute('aria-controls', this.dropdown.id);
    }
    
    openDropdown() {
        if (!this.dropdown) return;
        
        this.updateDropdownPosition();
        this.dropdown.classList.add('open');
        this.input.setAttribute('aria-expanded', 'true');
    }
    
    closeDropdown() {
        if (!this.dropdown) return;
        
        this.dropdown.classList.remove('open');
        this.input.setAttribute('aria-expanded', 'false');
        this.selectedIndex = -1;
        
        // Détruire après animation
        setTimeout(() => {
            if (this.dropdown && !this.dropdown.classList.contains('open')) {
                this.dropdown.remove();
                this.dropdown = null;
            }
        }, 300);
    }
    
    updateDropdownPosition() {
        if (!this.dropdown) return;
        
        const rect = this.input.getBoundingClientRect();
        const dropdownHeight = 300; // hauteur max
        const spaceBelow = window.innerHeight - rect.bottom;
        const spaceAbove = rect.top;
        
        // Positionner en dessous par défaut
        let top = rect.bottom + window.scrollY;
        let openUpward = false;
        
        // Si pas assez de place en dessous, ouvrir vers le haut
        if (spaceBelow < dropdownHeight && spaceAbove > spaceBelow) {
            top = rect.top + window.scrollY - Math.min(dropdownHeight, spaceAbove);
            openUpward = true;
        }
        
        this.dropdown.style.top = top + 'px';
        this.dropdown.style.left = rect.left + window.scrollX + 'px';
        this.dropdown.style.width = rect.width + 'px';
        
        this.dropdown.classList.toggle('open-upward', openUpward);
    }
    
    showLoading() {
        if (!this.dropdown) {
            this.createDropdown();
        }
        
        const list = this.dropdown.querySelector('.autocomplete-list');
        list.innerHTML = `
            <div class="autocomplete-item loading">
                <span class="autocomplete-loader"></span>
                <span>Recherche en cours...</span>
            </div>
        `;
        
        this.openDropdown();
    }
    
    showError() {
        if (!this.dropdown) {
            this.createDropdown();
        }
        
        const list = this.dropdown.querySelector('.autocomplete-list');
        list.innerHTML = `
            <div class="autocomplete-item error">
                ❌ Erreur de chargement
            </div>
        `;
        
        this.openDropdown();
    }
    
    handleKeydown(e) {
        if (!this.dropdown || !this.dropdown.classList.contains('open')) {
            return;
        }
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.setSelectedIndex(
                    this.selectedIndex < this.currentResults.length - 1 
                    ? this.selectedIndex + 1 
                    : 0
                );
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.setSelectedIndex(
                    this.selectedIndex > 0 
                    ? this.selectedIndex - 1 
                    : this.currentResults.length - 1
                );
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0) {
                    this.selectItem(this.selectedIndex);
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                this.closeDropdown();
                break;
                
            case 'Tab':
                this.closeDropdown();
                break;
        }
    }
    
    setSelectedIndex(index) {
        this.selectedIndex = index;
        
        const items = this.dropdown.querySelectorAll('.autocomplete-item');
        items.forEach((item, i) => {
            item.classList.toggle('selected', i === index);
        });
        
        // Scroll si nécessaire
        if (items[index]) {
            items[index].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        }
    }
    
    selectItem(index) {
        const city = this.currentResults[index];
        if (!city) return;
        
        const fullText = `${city.commune_name_ascii}, ${city.daira_name_ascii}, ${city.wilaya_name_ascii}`;
        
        // Remplir l'input
        this.input.value = fullText;
        
        // Stocker GPS
        if (this.gpsInput) {
            this.gpsInput.value = city.gps;
        }
        
        this.selectedCity = {
            name: fullText,
            gps: city.gps,
            data: city
        };
        
        console.log("✅ Ville sélectionnée : " + fullText);
        console.log("📍 GPS : " + city.gps);
        
        // Fermer
        this.closeDropdown();
        
        // Déclencher événement personnalisé
        this.input.dispatchEvent(new CustomEvent('citySelected', { 
            detail: this.selectedCity 
        }));
    }
    
    handleFocus() {
        // Rouvrir si déjà des résultats
        if (this.currentResults.length > 0 && this.input.value.length >= this.minChars) {
            this.displayResults(this.input.value);
        }
    }
    
    handleClickOutside(e) {
        if (!this.dropdown) return;
        
        if (!this.input.contains(e.target) && !this.dropdown.contains(e.target)) {
            this.closeDropdown();
        }
    }
    
    // Méthodes publiques
    reset() {
        this.input.value = '';
        if (this.gpsInput) this.gpsInput.value = '';
        this.selectedCity = null;
        this.currentResults = [];
        this.closeDropdown();
    }
    
    getSelectedCity() {
        return this.selectedCity;
    }
    
    destroy() {
        if (this.dropdown) {
            this.dropdown.remove();
        }
        // Cleanup événements si nécessaire
    }
}

// Initialisation automatique au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser l'autocomplete
    window.cityAutocomplete = new AutocompleteCity('autoC', {
        gpsInputId: 'currentgps',
        searchUrl: '/zz/searchCity.php',
        minChars: 2,
        debounceDelay: 300
    });
    
    // Validation du formulaire
    const form = document.querySelector('form.banner_one_form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedCity = window.cityAutocomplete.getSelectedCity();
            
            if (!selectedCity || !selectedCity.gps) {
                e.preventDefault();
                alert("⚠️ Veuillez sélectionner une ville dans la liste d'autocomplétion");
                document.getElementById('autoC').focus();
                return false;
            }
            
            console.log("📤 Formulaire soumis avec GPS : " + selectedCity.gps);
        });
    }
    
    // Écouter l'événement personnalisé (optionnel)
    document.getElementById('autoC').addEventListener('citySelected', function(e) {
        console.log("🎯 Ville choisie:", e.detail);
        // Tu peux faire des actions supplémentaires ici
    });
});
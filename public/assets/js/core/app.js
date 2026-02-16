/**
 * BonResto - Core JavaScript
 */

(function() {
    'use strict';
    
    // App object
    window.BonResto = {
        
        /**
         * Initialize application
         */
        init: function() {
            this.initSmoothScroll();
            this.initMobileMenu();
        },
        
        /**
         * Smooth scroll for anchor links
         */
        initSmoothScroll: function() {
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if(href === '#') return;
                    
                    e.preventDefault();
                    const target = document.querySelector(href);
                    
                    if(target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        },
        
        /**
         * Mobile menu toggle (if needed)
         */
        initMobileMenu: function() {
            // TODO: Add mobile menu functionality if needed
        },
        
        /**
         * Show toast notification
         */
        toast: function(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.textContent = message;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
                color: white;
                padding: 16px 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                z-index: 10000;
                animation: slideIn 0.3s ease;
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    };
    
    // Initialize on DOM ready
    if(document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => BonResto.init());
    } else {
        BonResto.init();
    }
    
})();

// Animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

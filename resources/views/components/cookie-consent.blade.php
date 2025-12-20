{{-- Component: Cookie Consent Banner RGPD --}}
<div id="cookieConsent" class="cookie-consent-banner" style="display: none;">
    <div class="cookie-consent-content">
        <div class="cookie-icon">
            <i class="fas fa-cookie-bite"></i>
        </div>
        
        <div class="cookie-text">
            <h4 class="cookie-title">🍪 Gestion des Cookies</h4>
            <p class="cookie-message">
                Nous utilisons des cookies pour améliorer votre expérience sur notre site et réaliser des statistiques de visite anonymes. 
                En continuant, vous acceptez notre utilisation des cookies conformément à notre 
                <a href="{{ route('politique') }}" class="cookie-link" target="_blank">Politique de Confidentialité</a>.
            </p>
        </div>
        
        <div class="cookie-actions">
            <button onclick="acceptAllCookies()" class="btn-accept-all">
                <i class="fas fa-check"></i> Tout Accepter
            </button>
            <button onclick="acceptEssentialOnly()" class="btn-essential">
                <i class="fas fa-cookie"></i> Essentiels Uniquement
            </button>
            <button onclick="toggleCookiePreferences()" class="btn-customize">
                <i class="fas fa-cog"></i> Personnaliser
            </button>
            <button onclick="rejectAllCookies()" class="btn-reject">
                <i class="fas fa-times"></i> Tout Refuser
            </button>
        </div>
    </div>
    
    {{-- Modal de préférences --}}
    <div id="cookiePreferences" class="cookie-preferences-modal" style="display: none;">
        <div class="cookie-preferences-content">
            <div class="cookie-preferences-header">
                <h3>🔧 Personnaliser les Cookies</h3>
                <button onclick="toggleCookiePreferences()" class="btn-close-modal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="cookie-categories">
                <!-- Cookies essentiels (obligatoires) -->
                <div class="cookie-category">
                    <div class="category-header">
                        <div>
                            <h4>🔒 Cookies Essentiels</h4>
                            <p>Nécessaires au fonctionnement du site</p>
                        </div>
                        <label class="toggle-switch disabled">
                            <input type="checkbox" checked disabled>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="category-description">
                        Ces cookies sont indispensables pour naviguer sur le site et utiliser ses fonctionnalités (session, sécurité, formulaires).
                    </div>
                </div>
                
                <!-- Cookies analytiques -->
                <div class="cookie-category">
                    <div class="category-header">
                        <div>
                            <h4>📊 Cookies Analytiques</h4>
                            <p>Statistiques de visite anonymes</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="analyticsConsent">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="category-description">
                        Ces cookies nous aident à comprendre comment les visiteurs utilisent notre site (Google Analytics). Les données sont anonymisées.
                    </div>
                </div>
                
                <!-- Cookies marketing (optionnel) -->
                <div class="cookie-category">
                    <div class="category-header">
                        <div>
                            <h4>🎯 Cookies Marketing</h4>
                            <p>Publicité personnalisée</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="marketingConsent">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    <div class="category-description">
                        Ces cookies permettent d'afficher des publicités pertinentes (non utilisés actuellement).
                    </div>
                </div>
            </div>
            
            <div class="cookie-preferences-footer">
                <button onclick="savePreferences()" class="btn-save-preferences">
                    <i class="fas fa-save"></i> Enregistrer mes Préférences
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Cookie Consent Banner */
.cookie-consent-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
    color: white;
    padding: 24px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.3);
    z-index: 99999;
    animation: slideInUp 0.5s ease-out;
}

@keyframes slideInUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.cookie-consent-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.cookie-icon {
    font-size: 48px;
    color: #f59e0b;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.cookie-text {
    flex: 1;
    min-width: 300px;
}

.cookie-title {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 8px;
}

.cookie-message {
    font-size: 14px;
    line-height: 1.6;
    color: #d1d5db;
}

.cookie-link {
    color: #22c55e;
    text-decoration: underline;
    font-weight: 600;
}

.cookie-link:hover {
    color: #16a34a;
}

.cookie-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.cookie-actions button {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-accept-all {
    background: #22c55e;
    color: white;
}

.btn-accept-all:hover {
    background: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(34, 197, 94, 0.4);
}

.btn-essential {
    background: #3b82f6;
    color: white;
}

.btn-essential:hover {
    background: #2563eb;
    transform: translateY(-2px);
}

.btn-customize {
    background: #6b7280;
    color: white;
}

.btn-customize:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

.btn-reject {
    background: transparent;
    color: #d1d5db;
    border: 2px solid #4b5563;
}

.btn-reject:hover {
    background: #4b5563;
    color: white;
}

/* Cookie Preferences Modal */
.cookie-preferences-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.cookie-preferences-content {
    background: white;
    border-radius: 16px;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.cookie-preferences-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 2px solid #e5e7eb;
}

.cookie-preferences-header h3 {
    font-size: 24px;
    font-weight: bold;
    color: #1f2937;
    margin: 0;
}

.btn-close-modal {
    background: #ef4444;
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-close-modal:hover {
    background: #dc2626;
    transform: rotate(90deg);
}

.cookie-categories {
    padding: 24px;
}

.cookie-category {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.category-header h4 {
    font-size: 18px;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 4px;
}

.category-header p {
    font-size: 13px;
    color: #6b7280;
    margin: 0;
}

.category-description {
    font-size: 14px;
    color: #4b5563;
    line-height: 1.5;
}

/* Toggle Switch */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: 0.4s;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch input:checked + .toggle-slider {
    background-color: #22c55e;
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-switch.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.cookie-preferences-footer {
    padding: 24px;
    border-top: 2px solid #e5e7eb;
    text-align: center;
}

.btn-save-preferences {
    background: #22c55e;
    color: white;
    border: none;
    padding: 14px 32px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-save-preferences:hover {
    background: #16a34a;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(34, 197, 94, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
    .cookie-consent-content {
        flex-direction: column;
        text-align: center;
    }
    
    .cookie-icon {
        font-size: 36px;
    }
    
    .cookie-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .cookie-actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
// Gestion du consentement des cookies
document.addEventListener('DOMContentLoaded', function() {
    checkCookieConsent();
});

function checkCookieConsent() {
    const consent = getCookie('cookie_consent');
    
    if (!consent) {
        // Afficher le banner après 1 seconde
        setTimeout(() => {
            document.getElementById('cookieConsent').style.display = 'block';
        }, 1000);
    } else {
        // Appliquer les préférences enregistrées
        applyConsent(JSON.parse(consent));
    }
}

function acceptAllCookies() {
    const consent = {
        essential: true,
        analytics: true,
        marketing: false
    };
    
    saveConsent(consent);
    applyConsent(consent);
    hideBanner();
}

function acceptEssentialOnly() {
    const consent = {
        essential: true,
        analytics: false,
        marketing: false
    };
    
    saveConsent(consent);
    applyConsent(consent);
    hideBanner();
}

function rejectAllCookies() {
    const consent = {
        essential: true, // Toujours actifs (nécessaires)
        analytics: false,
        marketing: false
    };
    
    saveConsent(consent);
    applyConsent(consent);
    hideBanner();
}

function toggleCookiePreferences() {
    const modal = document.getElementById('cookiePreferences');
    
    if (modal.style.display === 'none' || !modal.style.display) {
        modal.style.display = 'flex';
        
        // Charger les préférences actuelles
        const consent = getCookie('cookie_consent');
        if (consent) {
            const prefs = JSON.parse(consent);
            document.getElementById('analyticsConsent').checked = prefs.analytics || false;
            document.getElementById('marketingConsent').checked = prefs.marketing || false;
        }
    } else {
        modal.style.display = 'none';
    }
}

function savePreferences() {
    const consent = {
        essential: true,
        analytics: document.getElementById('analyticsConsent').checked,
        marketing: document.getElementById('marketingConsent').checked
    };
    
    saveConsent(consent);
    applyConsent(consent);
    toggleCookiePreferences();
    hideBanner();
}

function saveConsent(consent) {
    // Sauvegarder dans un cookie (1 an)
    setCookie('cookie_consent', JSON.stringify(consent), 365);
    
    console.log('✅ Préférences cookies enregistrées:', consent);
}

function applyConsent(consent) {
    // Appliquer Google Analytics si consentement
    if (consent.analytics && typeof gtag !== 'undefined') {
        gtag('consent', 'update', {
            'analytics_storage': 'granted'
        });
    } else if (typeof gtag !== 'undefined') {
        gtag('consent', 'update', {
            'analytics_storage': 'denied'
        });
    }
    
    // Appliquer marketing si consentement
    if (consent.marketing && typeof gtag !== 'undefined') {
        gtag('consent', 'update', {
            'ad_storage': 'granted'
        });
    } else if (typeof gtag !== 'undefined') {
        gtag('consent', 'update', {
            'ad_storage': 'denied'
        });
    }
    
    console.log('✅ Consentement appliqué:', consent);
}

function hideBanner() {
    document.getElementById('cookieConsent').style.display = 'none';
}

// Helper functions pour les cookies
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';
}

function getCookie(name) {
    const nameEQ = name + "=";
    const ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

// Permettre de rouvrir le panel de préférences depuis n'importe où
window.openCookiePreferences = function() {
    document.getElementById('cookieConsent').style.display = 'block';
    toggleCookiePreferences();
}
</script>







































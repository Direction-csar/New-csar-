// Cookie Consent - Chargé différé pour performance
(function() {
    'use strict';
    
    let cookieSettings = {
        functional: true,
        statistics: false,
        marketing: false
    };

    // HTML du cookie consent
    const cookieHTML = `
    <div id="cookie-consent-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:99999; backdrop-filter:blur(3px);">
        <div id="cookie-consent-box" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; border-radius:16px; max-width:520px; width:90%; box-shadow:0 25px 60px rgba(0,0,0,0.3); overflow:hidden; animation:cookieSlideIn 0.4s ease;">
            <div style="padding:25px 30px 15px; display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:15px;">
                    <img src="/images/logos/LOGO CSAR vectoriel-01.png" alt="CSAR" style="height:45px; width:auto;">
                    <h3 style="margin:0; font-size:1.15rem; font-weight:700; color:#1f2937;">Gérer le consentement</h3>
                </div>
                <button onclick="closeCookieConsent()" style="background:none; border:none; font-size:1.5rem; color:#9ca3af; cursor:pointer; padding:5px; line-height:1;">&times;</button>
            </div>
            <div style="padding:0 30px 20px;">
                <p style="font-size:0.9rem; color:#4b5563; line-height:1.7; margin:0;">
                    Pour offrir les meilleures expériences, nous utilisons des technologies telles que les cookies pour stocker et/ou accéder aux informations des appareils.
                </p>
            </div>
            <div style="padding:0 30px;">
                <div class="cookie-option" style="border-top:1px solid #e5e7eb; padding:16px 0; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-weight:600; color:#1f2937; font-size:0.95rem;">Fonctionnel</span>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <span style="color:#22c55e; font-weight:600; font-size:0.85rem;">Toujours activé</span>
                    </div>
                </div>
                <div class="cookie-option" style="border-top:1px solid #e5e7eb; padding:16px 0; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-weight:600; color:#1f2937; font-size:0.95rem;">Statistiques</span>
                    <label class="cookie-toggle" style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
                        <input type="checkbox" id="cookie-stats" style="opacity:0; width:0; height:0;">
                        <span class="cookie-slider" style="position:absolute; top:0; left:0; right:0; bottom:0; background:#ccc; border-radius:24px; transition:0.3s;"></span>
                    </label>
                </div>
                <div class="cookie-option" style="border-top:1px solid #e5e7eb; padding:16px 0; display:flex; align-items:center; justify-content:space-between;">
                    <span style="font-weight:600; color:#1f2937; font-size:0.95rem;">Marketing</span>
                    <label class="cookie-toggle" style="position:relative; display:inline-block; width:44px; height:24px; cursor:pointer;">
                        <input type="checkbox" id="cookie-marketing" style="opacity:0; width:0; height:0;">
                        <span class="cookie-slider" style="position:absolute; top:0; left:0; right:0; bottom:0; background:#ccc; border-radius:24px; transition:0.3s;"></span>
                    </label>
                </div>
            </div>
            <div style="padding:20px 30px 25px; display:flex; gap:10px; border-top:1px solid #e5e7eb; margin-top:10px;">
                <button onclick="acceptAllCookies()" style="flex:1; background:#22c55e; color:white; border:none; padding:14px; border-radius:10px; font-weight:700; font-size:0.95rem; cursor:pointer;">Accepter</button>
                <button onclick="refuseAllCookies()" style="flex:1; background:#f3f4f6; color:#1f2937; border:1px solid #e5e7eb; padding:14px; border-radius:10px; font-weight:600; font-size:0.95rem; cursor:pointer;">Refuser</button>
                <button onclick="savePreferences()" style="flex:1; background:#f3f4f6; color:#1f2937; border:1px solid #e5e7eb; padding:14px; border-radius:10px; font-weight:600; font-size:0.95rem; cursor:pointer;">Enregistrer</button>
            </div>
        </div>
    </div>`;
    
    // Insérer le HTML
    document.body.insertAdjacentHTML('beforeend', cookieHTML);
    
    // Styles CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes cookieSlideIn {
            from { opacity:0; transform:translate(-50%,-50%) scale(0.9); }
            to { opacity:1; transform:translate(-50%,-50%) scale(1); }
        }
        .cookie-slider::before {
            content:'';
            position:absolute;
            height:18px;
            width:18px;
            left:3px;
            bottom:3px;
            background:white;
            border-radius:50%;
            transition:0.3s;
        }
        .cookie-toggle input:checked + .cookie-slider {
            background:#22c55e;
        }
        .cookie-toggle input:checked + .cookie-slider::before {
            transform:translateX(20px);
        }
    `;
    document.head.appendChild(style);
    
    // Fonctions
    function loadCookiePreferences() {
        const saved = localStorage.getItem('cookiePreferences');
        if (saved) {
            cookieSettings = JSON.parse(saved);
            applyCookieSettings();
        } else {
            setTimeout(() => {
                document.getElementById('cookie-consent-overlay').style.display = 'block';
            }, 1000);
        }
    }
    
    function closeCookieConsent() {
        document.getElementById('cookie-consent-overlay').style.display = 'none';
    }
    
    function acceptAllCookies() {
        cookieSettings = { functional: true, statistics: true, marketing: true };
        saveCookiePreferences();
        closeCookieConsent();
    }
    
    function refuseAllCookies() {
        cookieSettings = { functional: true, statistics: false, marketing: false };
        saveCookiePreferences();
        closeCookieConsent();
    }
    
    function saveCookiePreferences() {
        cookieSettings.statistics = document.getElementById('cookie-stats').checked;
        cookieSettings.marketing = document.getElementById('cookie-marketing').checked;
        localStorage.setItem('cookiePreferences', JSON.stringify(cookieSettings));
        applyCookieSettings();
        closeCookieConsent();
    }
    
    function applyCookieSettings() {
        document.getElementById('cookie-stats').checked = cookieSettings.statistics;
        document.getElementById('cookie-marketing').checked = cookieSettings.marketing;
        
        // Appliquer les paramètres de tracking
        if (cookieSettings.statistics) {
            console.log('Analytics activés');
        }
        if (cookieSettings.marketing) {
            console.log('Marketing activé');
        }
    }
    
    // Initialiser
    loadCookiePreferences();
    
    // Rendre les fonctions globales
    window.closeCookieConsent = closeCookieConsent;
    window.acceptAllCookies = acceptAllCookies;
    window.refuseAllCookies = refuseAllCookies;
    window.savePreferences = saveCookiePreferences;
})();

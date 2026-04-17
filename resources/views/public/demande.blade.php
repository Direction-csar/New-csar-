@extends('layouts.public')
@section('title', __('pages.demande'))
@section('content')
<div class="demande-container" style="max-width:720px;margin:40px auto 60px auto;background:linear-gradient(180deg,#ffffff 0%,#f8fafc 100%);border-radius:18px;box-shadow:0 10px 30px rgba(2,132,199,0.08), 0 4px 12px rgba(0,0,0,0.04);padding:38px 28px 28px 28px;">
<style>
@media (max-width: 768px) {
    .demande-container { margin: 20px 15px !important; padding: 24px 18px !important; }
    .demande-container h1 { font-size: 1.6rem !important; }
}
@media (max-width: 480px) {
    .demande-container { margin: 10px !important; padding: 18px 14px !important; border-radius: 12px !important; }
    .demande-container h1 { font-size: 1.4rem !important; }
}
</style>
    <div style="margin-bottom:20px;">
        <a href="{{ route('home', ['locale' => 'fr']) }}" style="color:#0284c7;text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:8px;">
            <i class="fas fa-arrow-left"></i> Retour à l'accueil
        </a>
    </div>
    <h1 style="font-size:2rem;font-weight:800;margin-bottom:4px;color:#0d9488;">Formulaire de demande</h1>
    <div style="font-size:1.1rem;color:#0284c7;margin-bottom:22px;">
        @if(isset($selectedType))
            @switch($selectedType)
                @case('aide_alimentaire')
                    <i class="fas fa-hand-holding-heart" style="color:#d97706;margin-right:8px;"></i> Aide alimentaire
                    @break
                @case('demande_audience')
                    <i class="fas fa-handshake" style="color:#2563eb;margin-right:8px;"></i> Demande d'audience
                    @break
                @case('information_generale')
                    <i class="fas fa-info-circle" style="color:#6366f1;margin-right:8px;"></i> Information générale
                    @break
                @case('autre')
                    <i class="fas fa-clipboard-list" style="color:#9333ea;margin-right:8px;"></i> Autre demande
                    @break
                @default
                    Effectuez votre demande auprès du CSAR
            @endswitch
        @else
            Effectuez votre demande auprès du CSAR
        @endif
    </div>
    @if(session('success'))
    <div style="background:#dcfce7;color:#166534;border-radius:8px;padding:18px 22px;margin-bottom:22px;font-weight:600;box-shadow:0 2px 8px rgba(34,197,94,0.08);">
        <i class="fas fa-check-circle" style="margin-right:8px;"></i> {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div style="background:#fef2f2;color:#7f1d1d;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-weight:500;box-shadow:0 2px 8px rgba(220,38,38,0.06);border:1px solid #fecaca;">
        @if($errors->has('geolocation'))
            <div style="margin-bottom:10px;padding:8px;background:#ffe4e6;border-radius:6px;border:1px dashed #fecdd3;">
                <i class="fas fa-map-marker-alt"></i> {{ $errors->first('geolocation') }}
            </div>
        @endif
        <ul style="margin:0;padding-left:1rem;">
            @foreach($errors->all() as $error)
                @if(!str_contains($error, 'géolocalisation'))
                    <li>{{ $error }}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ route('demande.store.direct') }}" id="demandeForm" autocomplete="off" enctype="multipart/form-data">
        @csrf
        <fieldset style="border:none;padding:0;margin-bottom:26px;">
            <legend style="font-size:1.02rem;font-weight:700;color:#0284c7;margin-bottom:12px;">Type de demande</legend>
            <label for="type_demande" style="font-weight:600;">Type de demande *</nlabel>
            <select name="type_demande" id="type_demande" required onchange="toggleAutreType(this)" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;background:#fff;">
                <option value="">Sélectionnez le type de votre demande</option>
                <option value="aide_alimentaire" {{ (old('type_demande') ?? $selectedType ?? '') == 'aide_alimentaire' ? 'selected' : '' }}>Aide alimentaire</option>
                <option value="demande_audience" {{ (old('type_demande') ?? $selectedType ?? '') == 'demande_audience' ? 'selected' : '' }}>Demande d'audience</option>
                <option value="information_generale" {{ (old('type_demande') ?? $selectedType ?? '') == 'information_generale' ? 'selected' : '' }}>Information générale</option>
                <option value="autre" {{ (old('type_demande') ?? $selectedType ?? '') == 'autre' ? 'selected' : '' }}>Autre</option>
            </select>
            <div id="autre-type-div" style="display:{{ (old('type_demande') ?? $selectedType ?? '') == 'autre' ? 'block' : 'none' }};margin-bottom:12px;">
                <label for="type_demande_autre" style="font-weight:600;">Précisez votre type de demande *</label>
                <input type="text" name="type_demande_autre" id="type_demande_autre"
                    value="{{ old('type_demande_autre') }}"
                    placeholder="Décrivez brièvement votre demande..."
                    style="width:100%;padding:8px 10px;margin:4px 0 0 0;border:1px solid #cbd5e1;border-radius:6px;"
                    {{ (old('type_demande') ?? $selectedType ?? '') == 'autre' ? 'required' : '' }}>
            </div>
        </fieldset>
        <fieldset style="border:none;padding:0;margin-bottom:26px;">
            <legend style="font-size:1.02rem;font-weight:700;color:#0284c7;margin-bottom:12px;">Informations personnelles</legend>
            <label for="nom" style="font-weight:600;">Nom *</label>
            <input type="text" name="nom" id="nom" required maxlength="255" value="{{ old('nom') }}" placeholder="Votre nom" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;">
            <label for="prenom" style="font-weight:600;">Prénom *</label>
            <input type="text" name="prenom" id="prenom" required maxlength="255" value="{{ old('prenom') }}" placeholder="Votre prénom" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;">
            <label for="email" style="font-weight:600;">Email *</label>
            <input type="email" name="email" id="email" required maxlength="255" value="{{ old('email') }}" placeholder="votre.email@exemple.com" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;">
            <label for="telephone" style="font-weight:600;">Téléphone *</label>
            <input type="text" name="telephone" id="telephone" required maxlength="30" value="{{ old('telephone') }}" placeholder="+221 77 123 45 67" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;">
        </fieldset>
        <fieldset style="border:none;padding:0;margin-bottom:26px;">
            <legend style="font-size:1.02rem;font-weight:700;color:#0284c7;margin-bottom:12px;">Détails de la demande</legend>
            <label for="objet" style="font-weight:600;">Objet de la demande *</label>
            <input type="text" name="objet" id="objet" required maxlength="255" value="{{ old('objet') }}" placeholder="Ex : Demande d’aide alimentaire" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;">
            <label for="description" style="font-weight:600;">Description détaillée *</label>
            <textarea name="description" id="description" required maxlength="2000" placeholder="Décrivez votre demande en détail..." style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;min-height:90px;">{{ old('description') }}</textarea>
        </fieldset>
        <!-- Section géolocalisation pour aide alimentaire uniquement -->
        <fieldset id="geolocation-section" style="border:none;padding:0;margin-bottom:26px;display:none;">
            <legend style="font-size:1.02rem;font-weight:700;color:#0284c7;margin-bottom:12px;">📍 Localisation (recommandée)</legend>
            
            <div id="geolocation-info" style="background:#dbeafe;border:1px solid #3b82f6;border-radius:8px;padding:16px;margin-bottom:16px;">
                <p style="margin:0;color:#1e40af;font-size:0.9rem;font-weight:600;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Localisation recommandée :</strong> Pour traiter efficacement votre demande d'aide alimentaire, nous avons besoin de votre localisation.
                </p>
                <p style="margin:8px 0 0 0;color:#1e40af;font-size:0.85rem;">
                    Vous pouvez <strong>soit utiliser le GPS</strong> (bouton ci-dessous), <strong>soit saisir votre adresse manuellement</strong> dans les champs ci-dessous.
                </p>
            </div>
            
            <button type="button" id="btn-geoloc" style="background:#0ea5e9;color:#fff;border:none;padding:12px 24px;border-radius:8px;cursor:pointer;margin-bottom:12px;font-weight:600;transition:all 0.3s ease;">
                <i class="fas fa-location-arrow"></i> Obtenir ma position automatiquement
            </button>
            
            <div id="geo-status" style="margin:8px 0;font-size:0.9rem;"></div>
            
            <!-- Bouton de réessai plus visible -->
            <button type="button" id="btn-retry-geoloc" style="background:#dc2626;color:#fff;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;margin-bottom:12px;display:none;font-weight:600;">
                <i class="fas fa-redo"></i> Réessayer la géolocalisation
            </button>
            
            <!-- Champs cachés pour les coordonnées -->
            <input type="hidden" id="latitude" name="latitude">
            <input type="hidden" id="longitude" name="longitude">
            
            <!-- Saisie manuelle de la région (toujours visible) -->
            <div style="margin-bottom:16px;">
                <label for="region" style="font-weight:600;">Région *</label>
                <select name="region" id="region" required style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;background:#fff;">
                    <option value="">Sélectionnez votre région</option>
                    <option value="Dakar">Dakar</option>
                    <option value="Thiès">Thiès</option>
                    <option value="Saint-Louis">Saint-Louis</option>
                    <option value="Diourbel">Diourbel</option>
                    <option value="Louga">Louga</option>
                    <option value="Fatick">Fatick</option>
                    <option value="Kaolack">Kaolack</option>
                    <option value="Kaffrine">Kaffrine</option>
                    <option value="Tambacounda">Tambacounda</option>
                    <option value="Kédougou">Kédougou</option>
                    <option value="Kolda">Kolda</option>
                    <option value="Ziguinchor">Ziguinchor</option>
                    <option value="Sédhiou">Sédhiou</option>
                    <option value="Matam">Matam</option>
                </select>
            </div>
            
            <!-- Saisie manuelle si géolocalisation échoue -->
            <div id="manual-location" style="display:none;">
                <label for="address" style="font-weight:600;">Adresse complète *</label>
                <textarea name="address" id="address" placeholder="Indiquez votre adresse complète (quartier, ville, région)" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;min-height:60px;"></textarea>
                
                <div>
                    <label for="commune" style="font-weight:600;">Commune/Ville</label>
                    <input type="text" name="commune" id="commune" placeholder="Nom de votre commune" style="width:100%;padding:8px 10px;margin:4px 0 12px 0;border:1px solid #cbd5e1;border-radius:6px;">
                </div>
            </div>
        </fieldset>

        <fieldset style="border:none;padding:0;margin-bottom:26px;">
            <legend style="font-size:1.02rem;font-weight:700;color:#0284c7;margin-bottom:12px;">Pièces jointes (optionnel)</legend>
            <div id="attachments-container">
                <div class="attachment-item" style="margin-bottom:12px;">
                    <input type="file" name="pj[]" accept=".pdf" style="width:100%;padding:8px 10px;margin:4px 0 8px 0;border:1px solid #cbd5e1;border-radius:6px;">
                </div>
            </div>
            <button type="button" id="add-attachment" style="background:#e5e7eb;color:#374151;border:1px solid #d1d5db;padding:8px 16px;border-radius:6px;font-size:0.9rem;cursor:pointer;margin-bottom:8px;">
                <i class="fas fa-plus"></i> Ajouter un document
            </button>
            <div style="font-size:0.85rem;color:#64748b;">
                <strong>Format accepté uniquement :</strong> PDF<br>
                <strong>Taille max par fichier :</strong> 20 Mo<br>
                <strong>Maximum :</strong> 5 fichiers
            </div>
        </fieldset>
        <fieldset style="border:none;padding:0;margin-bottom:26px;">
            <legend style="font-size:1.02rem;font-weight:700;color:#0284c7;margin-bottom:12px;">Consentement</legend>
            <label style="display:flex;align-items:center;font-weight:500;">
                <input type="checkbox" name="consentement" required style="margin-right:8px;"> J’accepte que mes données soient utilisées pour le traitement de ma demande par le CSAR.
            </label>
        </fieldset>
        
        <button type="submit" id="btn-submit" style="background:linear-gradient(90deg,#0d9488 0%,#0284c7 100%);color:#fff;font-weight:700;padding:12px 40px;border:none;border-radius:8px;font-size:1.1rem;cursor:pointer;transition:all 0.3s ease;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
            <i class="fas fa-paper-plane"></i> Envoyer ma demande
        </button>
    </form>
</div>
<script>
console.log('🚀 SCRIPT CHARGÉ - Début du JavaScript');

// Fonction de test pour diagnostiquer le problème
function testFormValidation() {
    console.log('🧪 DÉBUT DU TEST DE DIAGNOSTIC');
    
    const form = document.getElementById('demandeForm');
    console.log('Formulaire trouvé:', form);
    
    if (!form) {
        alert('❌ ERREUR: Formulaire introuvable !');
        return;
    }
    
    const consentement = document.querySelector('input[name="consentement"]');
    console.log('Consentement:', consentement);
    console.log('Consentement coché:', consentement ? consentement.checked : 'N/A');
    
    const latitude = document.getElementById('latitude');
    const longitude = document.getElementById('longitude');
    console.log('Latitude field:', latitude);
    console.log('Longitude field:', longitude);
    console.log('Latitude value:', latitude ? latitude.value : 'N/A');
    console.log('Longitude value:', longitude ? longitude.value : 'N/A');
    
    const address = document.getElementById('address');
    const region = document.getElementById('region');
    console.log('Address field:', address);
    console.log('Region field:', region);
    console.log('Address value:', address ? address.value : 'N/A');
    console.log('Region value:', region ? region.value : 'N/A');
    
    alert('✅ Test terminé - Vérifiez la console pour les détails (F12)');
}

// Rafraîchir le token CSRF automatiquement avant soumission
function refreshCsrfToken() {
    return fetch('/fr', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newToken = doc.querySelector('meta[name="csrf-token"]');
        if (newToken) {
            const tokenValue = newToken.getAttribute('content');
            // Mettre à jour le token dans le formulaire
            const csrfInput = document.querySelector('input[name="_token"]');
            if (csrfInput) {
                csrfInput.value = tokenValue;
                console.log('✅ Token CSRF rafraîchi avec succès');
                return true;
            }
        }
        console.warn('⚠️ Impossible de rafraîchir le token CSRF');
        return false;
    })
    .catch(error => {
        console.error('❌ Erreur lors du rafraîchissement du token:', error);
        return false;
    });
}

// Fonction de soumission directe (contournement du JavaScript)
function directSubmit() {
    console.log('⚡ SOUMISSION DIRECTE - Contournement des validations JS');
    
    const form = document.getElementById('demandeForm');
    if (!form) {
        alert('❌ Formulaire introuvable !');
        return;
    }
    
    // Vérifier le token CSRF
    const csrfToken = document.querySelector('input[name="_token"]');
    console.log('Token CSRF trouvé:', csrfToken ? 'OUI' : 'NON');
    console.log('Valeur du token:', csrfToken ? csrfToken.value : 'N/A');
    
    // Vérification minimale du consentement
    const consentement = document.querySelector('input[name="consentement"]');
    if (!consentement || !consentement.checked) {
        alert('⚠️ Veuillez cocher la case de consentement avant d\'envoyer votre demande.');
        return;
    }
    
    console.log('🔄 Rafraîchissement du token CSRF avant envoi...');
    
    // Rafraîchir le token CSRF avant de soumettre
    refreshCsrfToken().then(success => {
        // Soumettre le formulaire normalement
        console.log('🚀 ENVOI DU FORMULAIRE...');
        
        // Désactiver le bouton pour éviter les double-soumissions
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Envoi en cours...';
        }
        
        // Soumettre le formulaire
        form.submit();
    });
}

// Fonction pour rafraîchir le token CSRF automatiquement
function refreshCSRFToken() {
    console.log('🔄 Rafraîchissement automatique du token CSRF...');
    
    fetch('/csrf-token', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erreur réseau');
        }
        return response.json();
    })
    .then(data => {
            const currentToken = document.querySelector('input[name="_token"]');
        if (currentToken && data.token) {
            currentToken.value = data.token;
            console.log('✅ Token CSRF mis à jour automatiquement');
        }
    })
    .catch(error => {
        console.error('❌ Erreur lors du rafraîchissement automatique:', error);
        // En cas d'erreur, recharger la page pour obtenir un nouveau token
        console.log('🔄 Rechargement de la page pour obtenir un nouveau token...');
        window.location.reload();
    });
}

// Rafraîchir le token CSRF toutes les 5 minutes
setInterval(refreshCSRFToken, 5 * 60 * 1000); // 5 minutes

// Enregistrer le temps de chargement de la page
window.pageLoadTime = Date.now();

// Restaurer les données sauvegardées au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 Page chargée, token CSRF initialisé');
    
    // Vérifier s'il y a des données sauvegardées
    const savedData = localStorage.getItem('csar_form_data');
    if (savedData) {
        try {
            const data = JSON.parse(savedData);
            console.log('🔄 Restauration des données sauvegardées...');
            
            // Restaurer les données dans le formulaire
            Object.keys(data).forEach(key => {
                const element = document.querySelector(`[name="${key}"]`);
                if (element) {
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = data[key] === 'on' || data[key] === element.value;
                    } else {
                        element.value = data[key];
                    }
                }
            });
            
            // Supprimer les données sauvegardées
            localStorage.removeItem('csar_form_data');
            
            // Afficher un message à l'utilisateur
            const message = document.createElement('div');
            message.style.cssText = `
                background: #d1fae5; 
                color: #065f46; 
                padding: 12px 16px; 
                border-radius: 8px; 
                margin-bottom: 20px; 
                border: 1px solid #a7f3d0;
                font-weight: 500;
            `;
            message.innerHTML = '✅ Vos données ont été restaurées. Vous pouvez maintenant soumettre le formulaire.';
            
            const form = document.getElementById('demandeForm');
            if (form) {
                form.insertBefore(message, form.firstChild);
                
                // Supprimer le message après 5 secondes
                setTimeout(() => {
                    message.remove();
                }, 5000);
            }
            
        } catch (error) {
            console.error('❌ Erreur lors de la restauration des données:', error);
            localStorage.removeItem('csar_form_data');
        }
    }
});

// Champ Autre dynamique pour type_demande
function toggleAutreType(select) {
    const div = document.getElementById('autre-type-div');
    const input = document.getElementById('type_demande_autre');
    if (select.value === 'autre') {
        div.style.display = 'block';
        input.required = true;
    } else {
        div.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}

// Gestion de l'affichage conditionnel de la géolocalisation
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM CHARGÉ - JavaScript actif');
    const typeSelect = document.getElementById('type_demande');
    const typeHidden = document.querySelector('input[name="type_demande"][type="hidden"]');
    const geolocationSection = document.getElementById('geolocation-section');
    const regionField = document.getElementById('region');
    const addressField = document.getElementById('address');
    const form = document.getElementById('demandeForm');
    
    // Fonction pour obtenir le type de demande actuel
    function getCurrentType() {
        if (typeHidden) {
            return typeHidden.value;
        } else if (typeSelect) {
            return typeSelect.value;
        }
        return '';
    }
    
    // Fonction pour afficher/masquer la section géolocalisation
    function toggleGeolocationSection() {
        const currentType = getCurrentType();
        if (currentType === 'aide_alimentaire') {
            geolocationSection.style.display = 'block';
            regionField.setAttribute('required', 'required');
            addressField.setAttribute('required', 'required');
        } else {
            geolocationSection.style.display = 'none';
            regionField.removeAttribute('required');
            addressField.removeAttribute('required');
        }
    }
    
    // Vérifier au chargement de la page
    toggleGeolocationSection();
    
    // Écouter les changements de type de demande (seulement si le select existe)
    if (typeSelect) {
        typeSelect.addEventListener('change', toggleGeolocationSection);
    }
    
    // Validation avant soumission du formulaire - VERSION SIMPLIFIÉE
    if (form) {
        console.log('✅ Formulaire trouvé, ajout du listener de soumission');
        
        form.addEventListener('submit', function(e) {
            console.log('🔥 ÉVÉNEMENT SUBMIT DÉCLENCHÉ !');
            console.log('Event object:', e);
            
            const currentType = getCurrentType();
            console.log('Type de demande:', currentType);
            
            // Vérification du consentement
            const consentementField = document.querySelector('input[name="consentement"]');
            if (!consentementField || !consentementField.checked) {
                console.log('❌ Consentement non coché');
                e.preventDefault();
                alert('⚠️ CONSENTEMENT REQUIS\n\nVous devez accepter le traitement de vos données pour pouvoir soumettre votre demande.');
                return false;
            }
            
            // Pour l'aide alimentaire, vérification de géolocalisation simplifiée
            if (currentType === 'aide_alimentaire') {
                const latitudeField = document.getElementById('latitude');
                const longitudeField = document.getElementById('longitude');
                const addressField = document.getElementById('address');
                const regionField = document.getElementById('region');
                
                const hasGPS = latitudeField && longitudeField && latitudeField.value && longitudeField.value;
                const hasManualAddress = addressField && regionField && addressField.value.trim() && regionField.value.trim();
                
                console.log('Vérification géolocalisation:', { hasGPS, hasManualAddress });
                
                if (!hasGPS && !hasManualAddress) {
                    console.log('❌ Géolocalisation manquante');
                    e.preventDefault();
                    alert('⚠️ GÉOLOCALISATION OBLIGATOIRE\n\nPour les demandes d\'aide alimentaire, vous devez soit :\n- Activer votre GPS (bouton bleu)\n- Ou remplir manuellement votre adresse et région\n\nCela nous aide à traiter votre demande efficacement.');
                    return false;
                }
            }
            
            console.log('✅ Toutes les validations passées');
            
            // Afficher un indicateur de chargement
            const submitBtn = document.getElementById('btn-submit');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
                submitBtn.disabled = true;
                console.log('✅ Bouton mis à jour avec indicateur de chargement');
            }
            
            console.log('🚀 SOUMISSION DU FORMULAIRE AUTORISÉE !');
            // Le formulaire va se soumettre naturellement ici
        });
        
        console.log('✅ Event listener ajouté avec succès');
    } else {
        console.error('❌ FORMULAIRE INTROUVABLE !');
        console.log('Tentative de recherche du formulaire par différents moyens...');
        
        const formByName = document.querySelector('form[id="demandeForm"]');
        const formByTag = document.querySelector('form');
        
        console.log('Formulaire par ID:', formByName);
        console.log('Premier formulaire trouvé:', formByTag);
    }
    
    // Gestion des pièces jointes multiples
    const addButton = document.getElementById('add-attachment');
    const container = document.getElementById('attachments-container');
    let attachmentCount = 1;
    const maxAttachments = 5;

    if (addButton && container) {
        addButton.addEventListener('click', function() {
            if (attachmentCount >= maxAttachments) {
                alert('Vous ne pouvez ajouter que 5 fichiers maximum.');
                return;
            }

            attachmentCount++;
            
            const newAttachment = document.createElement('div');
            newAttachment.className = 'attachment-item';
            newAttachment.style.cssText = 'margin-bottom:12px;display:flex;align-items:center;gap:8px;';
            
            newAttachment.innerHTML = `
                <input type="file" name="pj[]" accept=".pdf" style="flex:1;padding:8px 10px;margin:4px 0 8px 0;border:1px solid #cbd5e1;border-radius:6px;">
                <button type="button" class="remove-attachment" style="background:#dc2626;color:#fff;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;font-size:0.8rem;">
                    <i class="fas fa-trash"></i>
                </button>
            `;
            
            container.appendChild(newAttachment);
            
            // Ajouter l'événement de suppression
            newAttachment.querySelector('.remove-attachment').addEventListener('click', function() {
                newAttachment.remove();
                attachmentCount--;
                updateAddButton();
            });
            
            updateAddButton();
        });
        
        function updateAddButton() {
            if (attachmentCount >= maxAttachments) {
                addButton.style.display = 'none';
            } else {
                addButton.style.display = 'inline-block';
            }
        }
        
        // Validation de taille de fichier
        container.addEventListener('change', function(e) {
            if (e.target.type === 'file' && e.target.files[0]) {
                const file = e.target.files[0];
                const maxSize = 20 * 1024 * 1024; // 20 Mo en bytes
                
                if (file.size > maxSize) {
                    alert('Le fichier est trop volumineux. La taille maximum est de 20 Mo.');
                    e.target.value = '';
                    return;
                }
                
                if (!file.name.toLowerCase().endsWith('.pdf')) {
                    alert('Seuls les fichiers PDF sont acceptés.');
                    e.target.value = '';
                    return;
                }
            }
        });
    }
});

// Nouvelle fonction de géolocalisation améliorée
function activerGeoloc() {
    const btn = document.getElementById('btn-geoloc');
    const retryBtn = document.getElementById('btn-retry-geoloc');
    const status = document.getElementById('geo-status');
    const manualLocation = document.getElementById('manual-location');
    
    if (!btn || !status) return;
    
    // Afficher la saisie manuelle dans tous les cas
    if (manualLocation) {
        manualLocation.style.display = 'block';
    }
    
    // Vérifier si on est en HTTPS (requis pour la géolocalisation)
    if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
        status.innerHTML = `
            <div style="background:#dbeafe;color:#1e40af;padding:12px;border-radius:6px;border:1px solid #3b82f6;">
                <i class="fas fa-info-circle"></i> 
                <strong>La géolocalisation GPS nécessite HTTPS</strong><br>
                <small>Pas de problème ! Veuillez simplement remplir les champs d'adresse ci-dessous.</small>
            </div>
        `;
        btn.innerHTML = '<i class="fas fa-map-marked-alt"></i> GPS non disponible (HTTP)';
        btn.disabled = true;
        btn.style.background = '#6b7280';
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation en cours...';
    status.innerHTML = '<span style="color:#0369a1;"><i class="fas fa-spinner fa-spin"></i> Recherche de votre position...</span>';
    
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Succès de la géolocalisation
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                
                status.innerHTML = `
                    <div style="background:#dcfce7;color:#166534;padding:12px;border-radius:6px;border:1px solid #16a34a;">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Position GPS obtenue !</strong><br>
                        <small>Coordonnées : ${lat.toFixed(6)}, ${lng.toFixed(6)}</small><br>
                        <small style="color:#059669;margin-top:4px;display:block;">
                            <i class="fas fa-spinner fa-pulse"></i> Recherche de votre adresse...
                        </small>
                    </div>
                `;
                
                btn.innerHTML = '<i class="fas fa-check"></i> Position GPS obtenue';
                btn.style.background = '#16a34a';
                btn.disabled = true;
                
                // Cacher le bouton de réessai
                if (retryBtn) retryBtn.style.display = 'none';
                
                // Essayer de récupérer l'adresse via reverse geocoding
                reverseGeocode(lat, lng);
                
            },
            function(error) {
                // Échec de la géolocalisation
                let errorMessage = 'Erreur de géolocalisation : ';
                let suggestion = '';
                
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage += 'Accès refusé par l\'utilisateur.';
                        suggestion = 'Veuillez autoriser l\'accès à votre position dans les paramètres de votre navigateur.';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage += 'Position non disponible.';
                        suggestion = 'Vérifiez que votre GPS est activé et que vous êtes dans une zone avec une bonne réception.';
                        break;
                    case error.TIMEOUT:
                        errorMessage += 'Délai d\'attente dépassé.';
                        suggestion = 'La recherche de position a pris trop de temps. Réessayez ou saisissez votre adresse manuellement.';
                        break;
                    default:
                        errorMessage += 'Erreur inconnue.';
                        suggestion = 'Veuillez saisir votre adresse manuellement ci-dessous.';
                        break;
                }
                
                status.innerHTML = `
                    <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:6px;border:1px solid #dc2626;">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>${errorMessage}</strong><br>
                        <small>${suggestion}</small>
                    </div>
                `;
                
                btn.innerHTML = '<i class="fas fa-location-arrow"></i> Obtenir ma position automatiquement';
                btn.disabled = false;
                btn.style.background = '#0ea5e9';
                
                // Afficher le bouton de réessai
                if (retryBtn) {
                    retryBtn.style.display = 'inline-block';
                    retryBtn.onclick = activerGeoloc;
                }
                
                // Afficher les champs de saisie manuelle
                if (manualLocation) {
                manualLocation.style.display = 'block';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 15000, // Augmenté à 15 secondes
                maximumAge: 300000
            }
        );
    } else {
        status.innerHTML = `
            <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:6px;border:1px solid #dc2626;">
                <i class="fas fa-times-circle"></i> 
                <strong>Géolocalisation non supportée</strong><br>
                <small>Votre navigateur ne supporte pas la géolocalisation. Veuillez remplir manuellement votre adresse.</small>
            </div>
        `;
        
        btn.innerHTML = '<i class="fas fa-times"></i> GPS non disponible';
        btn.disabled = true;
        btn.style.background = '#6b7280';
        
        // Afficher les champs de saisie manuelle
        if (manualLocation) {
        manualLocation.style.display = 'block';
        }
    }
}

// Fonction de géocodage inversé (optionnel, pour récupérer l'adresse)
function reverseGeocode(lat, lng) {
    const status = document.getElementById('geo-status');
    
    // Ajouter un message de chargement
    if (status) {
        status.innerHTML = `
            <div style="background:#dbeafe;color:#0369a1;padding:10px;border-radius:6px;border:1px solid #0ea5e9;">
                <i class="fas fa-spinner fa-spin"></i> 
                <strong>Recherche de l'adresse...</strong>
            </div>
        `;
    }
    
    // Utilisation de l'API Nominatim d'OpenStreetMap (gratuite)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=16&addressdetails=1&accept-language=fr`)
        .then(response => response.json())
        .then(data => {
            console.log('Reverse geocoding data:', data);
            
            if (data && data.address) {
                const addr = data.address;
                
                // Construire une adresse lisible
                let addressParts = [];
                if (addr.road) addressParts.push(addr.road);
                if (addr.suburb) addressParts.push(addr.suburb);
                if (addr.neighbourhood) addressParts.push(addr.neighbourhood);
                if (addr.city) addressParts.push(addr.city);
                if (addr.town) addressParts.push(addr.town);
                if (addr.village) addressParts.push(addr.village);
                
                const formattedAddress = addressParts.join(', ') || data.display_name;
                
                // Extraire la région (State/Region pour le Sénégal)
                const region = addr.state || addr.region || addr.city || addr.town || 'Dakar';
                
                // Extraire la commune
                const commune = addr.city || addr.town || addr.village || addr.suburb || '';
                
                // Pré-remplir les champs
                const addressField = document.getElementById('address');
                const regionField = document.getElementById('region');
                const communeField = document.getElementById('commune');
                
                if (formattedAddress && addressField) {
                    addressField.value = formattedAddress;
                }
                
                if (region && regionField) {
                    regionField.value = region;
                }
                
                if (commune && communeField) {
                    communeField.value = commune;
                }
                
                // Afficher le succès avec les informations
                if (status) {
                    status.innerHTML = `
                        <div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:6px;border:1px solid #10b981;">
                            <i class="fas fa-check-circle"></i> 
                            <strong>Adresse trouvée !</strong><br>
                            <small style="display:block;margin-top:4px;">
                                <i class="fas fa-map-marker-alt"></i> ${formattedAddress}
                            </small>
                            <small style="display:block;margin-top:2px;">
                                <i class="fas fa-map"></i> Région: ${region}
                            </small>
                        </div>
                    `;
                }
                
                // Afficher les champs manuels pour permettre la correction
                const manualLocation = document.getElementById('manual-location');
                if (manualLocation) {
                    manualLocation.style.display = 'block';
                }
                
            } else {
                // Pas d'adresse trouvée
                if (status) {
                    status.innerHTML = `
                        <div style="background:#fef3c7;color:#92400e;padding:12px;border-radius:6px;border:1px solid #f59e0b;">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Position enregistrée</strong><br>
                            <small>Adresse non trouvée automatiquement. Veuillez saisir votre adresse manuellement ci-dessous.</small>
                        </div>
                    `;
                }
                
                // Forcer l'affichage des champs manuels
                const manualLocation = document.getElementById('manual-location');
                if (manualLocation) {
                    manualLocation.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.log('Reverse geocoding failed:', error);
            
            // Afficher un message informatif
            if (status) {
                status.innerHTML = `
                    <div style="background:#fef3c7;color:#92400e;padding:12px;border-radius:6px;border:1px solid #f59e0b;">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Position enregistrée</strong><br>
                        <small>Impossible de récupérer l'adresse automatiquement. Veuillez la saisir manuellement ci-dessous.</small>
                    </div>
                `;
            }
            
            // Afficher les champs manuels
            const manualLocation = document.getElementById('manual-location');
            if (manualLocation) {
                manualLocation.style.display = 'block';
            }
        });
}

// Ajouter l'événement au bouton de géolocalisation
document.addEventListener('DOMContentLoaded', function() {
    const btnGeoloc = document.getElementById('btn-geoloc');
    if (btnGeoloc) {
        btnGeoloc.addEventListener('click', activerGeoloc);
    }
    
    // Événement pour le bouton de réessai
    const btnRetryGeoloc = document.getElementById('btn-retry-geoloc');
    if (btnRetryGeoloc) {
        btnRetryGeoloc.addEventListener('click', activerGeoloc);
    }
    
    // Afficher automatiquement la saisie manuelle si on détecte qu'on est en HTTP
    const isHTTP = location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1';
    const manualLocation = document.getElementById('manual-location');
    
    if (isHTTP && manualLocation) {
        manualLocation.style.display = 'block';
    }
    
    // DÉSACTIVÉ: Gestion de la soumission AJAX du formulaire
    // Le formulaire utilise maintenant une soumission normale
    console.log('✅ Formulaire configuré pour soumission normale (pas d\'AJAX)');
});

// Fonction pour soumettre le formulaire via AJAX
function submitFormAjax() {
    console.log('🚀 Début de submitFormAjax');
    
    const form = document.getElementById('demandeForm');
    const submitBtn = document.getElementById('btn-submit');
    
    if (!form) {
        console.error('❌ Formulaire non trouvé');
        showErrorPopup('Erreur: Formulaire non trouvé');
        return;
    }
    
    if (!submitBtn) {
        console.error('❌ Bouton de soumission non trouvé');
        showErrorPopup('Erreur: Bouton de soumission non trouvé');
        return;
    }
    
    const formData = new FormData(form);
    console.log('📝 Données du formulaire:', formData);
    
    // Désactiver le bouton et afficher le loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
    console.log('⏳ Bouton désactivé, envoi en cours...');
    
    // Envoyer la requête AJAX
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('📡 Réponse reçue:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('📦 Données reçues:', data);
        if (data.success) {
            console.log('✅ Succès - Affichage de la popup');
            showConfirmationPopup(data);
        } else {
            console.log('❌ Échec - Affichage de l\'erreur');
            showErrorPopup(data.message || 'Erreur lors de l\'envoi de votre demande.');
        }
    })
    .catch(error => {
        console.error('💥 Erreur complète:', error);
        showErrorPopup('Erreur de connexion: ' + error.message);
    })
    .finally(() => {
        console.log('🔄 Réactivation du bouton');
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Envoyer ma demande';
    });
}

// Fonction pour afficher la popup de confirmation
function showConfirmationPopup(data) {
    const popup = document.createElement('div');
    popup.id = 'confirmation-popup';
    popup.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;
    
    const popupContent = document.createElement('div');
    popupContent.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease;
    `;
    
    let smsInfo = '';
    if (data.is_aide_request) {
        smsInfo = `
            <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:16px;margin:20px 0;">
                <h3 style="color:#92400e;margin-bottom:12px;font-size:1.1rem;">
                    <i class="fas fa-mobile-alt"></i> Suivi par SMS
                </h3>
                <p style="color:#92400e;margin:0;font-size:0.95rem;">
                    Un message de confirmation a été envoyé sur votre numéro de téléphone.
                </p>
                <div style="margin-top:8px;color:#059669;font-size:0.9rem;">
                    <i class="fas fa-check"></i> SMS envoyé avec succès
                </div>
            </div>
        `;
    }
    
    popupContent.innerHTML = `
        <div style="font-size: 4rem; color: #10b981; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i>
        </div>
        <h2 style="color: #1f2937; margin-bottom: 20px; font-size: 1.8rem;">Demande envoyée avec succès !</h2>
        <p style="color: #4b5563; margin-bottom: 20px; font-size: 1.1rem;">${data.message}</p>
        ${smsInfo}
        ${data.tracking_code ? `
            <div style="background:#f0f9ff;border:1px solid #0ea5e9;border-radius:8px;padding:16px;margin:20px 0;">
                <h3 style="color:#0369a1;margin-bottom:8px;font-size:1rem;">Code de suivi</h3>
                <div style="background:#1f2937;color:#fff;padding:12px;border-radius:6px;font-family:monospace;font-weight:bold;letter-spacing:1px;">
                    ${data.tracking_code}
                </div>
            </div>
        ` : ''}
        <div style="display:flex;gap:12px;justify-content:center;margin-top:30px;">
            <button onclick="closePopup()" style="background:#6b7280;color:#fff;border:none;padding:12px 24px;border-radius:8px;cursor:pointer;font-weight:600;">
                Fermer
            </button>
            <button onclick="goToTracking('${data.tracking_code}')" style="background:#10b981;color:#fff;border:none;padding:12px 24px;border-radius:8px;cursor:pointer;font-weight:600;">
                Suivre ma demande
            </button>
        </div>
    `;
    
    popup.appendChild(popupContent);
    document.body.appendChild(popup);
    
    // Ajouter les styles d'animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    `;
    document.head.appendChild(style);
}

// Fonction pour afficher la popup d'erreur
function showErrorPopup(message) {
    const popup = document.createElement('div');
    popup.id = 'error-popup';
    popup.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
        animation: fadeIn 0.3s ease;
    `;
    
    const popupContent = document.createElement('div');
    popupContent.style.cssText = `
        background: white;
        border-radius: 16px;
        padding: 40px;
        max-width: 400px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideIn 0.3s ease;
    `;
    
    popupContent.innerHTML = `
        <div style="font-size: 4rem; color: #dc2626; margin-bottom: 20px;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 style="color: #1f2937; margin-bottom: 20px; font-size: 1.8rem;">Erreur</h2>
        <p style="color: #4b5563; margin-bottom: 30px; font-size: 1.1rem;">${message}</p>
        <button onclick="closePopup()" style="background:#dc2626;color:#fff;border:none;padding:12px 24px;border-radius:8px;cursor:pointer;font-weight:600;">
            Fermer
        </button>
    `;
    
    popup.appendChild(popupContent);
    document.body.appendChild(popup);
}

// Fonction pour fermer la popup
function closePopup() {
    const popup = document.getElementById('confirmation-popup') || document.getElementById('error-popup');
    if (popup) {
        popup.remove();
    }
}

// Fonction pour aller au suivi
function goToTracking(trackingCode) {
    window.location.href = `/suivre-ma-demande?code=${trackingCode}`;
}

</script>

<style>
@media (max-width: 768px) {
    .demande-container {
        margin: 20px auto 40px auto !important;
        padding: 24px 16px 20px 16px !important;
        max-width: 95% !important;
        border-radius: 12px !important;
    }
    
    .demande-container h1 {
        font-size: 1.75rem !important;
    }
    
    .demande-container input,
    .demande-container select,
    .demande-container textarea {
        font-size: 16px !important; /* Évite le zoom sur iOS */
    }
    
    .demande-container button {
        width: 100% !important;
        padding: 14px 20px !important;
    }
}

@media (max-width: 480px) {
    .demande-container {
        margin: 10px auto 30px auto !important;
        padding: 20px 12px 16px 12px !important;
        max-width: 98% !important;
    }
    
    .demande-container h1 {
        font-size: 1.5rem !important;
        text-align: center;
    }
    
    .demande-container legend {
        font-size: 0.95rem !important;
    }
}
</style>
@endsection

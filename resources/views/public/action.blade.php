@extends('layouts.public')

@section('title', __('pages.action'))

@section('content')
<!-- Hero Section -->
<section class="hero fade-in" style="background: radial-gradient(1200px 600px at 0% 0%, #34d39922 0, transparent 60%), radial-gradient(1200px 600px at 100% 0%, #10b98122 0, transparent 60%), linear-gradient(135deg, #22c55e 0%, #16a34a 100%); min-height: 44vh; display: flex; align-items: center; justify-content: center; padding: 48px 0; position: relative; overflow: hidden;">
    <!-- Motifs décoratifs -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.1;">
        <div style="position: absolute; top: 10%; left: 10%; width: 100px; height: 100px; background: #fff; border-radius: 50%;"></div>
        <div style="position: absolute; top: 20%; right: 15%; width: 60px; height: 60px; background: #fff; border-radius: 50%;"></div>
        <div style="position: absolute; bottom: 30%; left: 20%; width: 80px; height: 80px; background: #fff; border-radius: 50%;"></div>
        <div style="position: absolute; bottom: 20%; right: 10%; width: 120px; height: 120px; background: #fff; border-radius: 50%;"></div>
    </div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
        <!-- Progress bar -->
        <div style="height:6px; width:100%; background:rgba(255,255,255,.25); border-radius:999px; overflow:hidden; margin:0 0 18px 0;">
            <div id="progressBar" style="height:100%; width:0%; background:#fff; border-radius:999px; transition:width .6s ease;"></div>
        </div>
        <h1 class="main-title" style="font-size: 3rem; font-weight: 800; color: #fff; margin-bottom: 14px; letter-spacing: -1px; line-height: 1.2;">
            {{ __('messages.action.title') }}
        </h1>
        <p class="main-subtitle" style="font-size: 1.1rem; color: #e5e7eb; max-width: 780px; margin: 0 auto 28px; line-height: 1.6;">
            {{ __('messages.action.subtitle') }}
        </p>
        
        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
            <div class="hero-stat zoom-hover" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); padding: 10px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.28);">
                <div style="font-size: 1.35rem; font-weight: 700; color: #fff; margin-bottom: 2px;">4</div>
                <div style="color: #e6f7ef; font-size: 0.85rem;">{{ __('messages.action.types_count') }}</div>
            </div>
            <div class="hero-stat zoom-hover" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); padding: 10px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.28);">
                <div style="font-size: 1.35rem; font-weight: 700; color: #fff; margin-bottom: 2px;">24h</div>
                <div style="color: #e6f7ef; font-size: 0.85rem;">{{ __('messages.action.response_time') }}</div>
            </div>
            <div class="hero-stat zoom-hover" style="background: rgba(255,255,255,0.18); backdrop-filter: blur(8px); padding: 10px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.28);">
                <div style="font-size: 1.35rem; font-weight: 700; color: #fff; margin-bottom: 2px;">100%</div>
                <div style="color: #e6f7ef; font-size: 0.85rem;">{{ __('messages.action.free') }}</div>
            </div>
        </div>
    </div>
</section>

<!-- Action Types Section (NE PAS MODIFIER le contenu) -->
<section class="section fade-in" style="background: #fff; padding: 80px 0; position: relative;">
    <!-- Barre d'étapes -->
    <div class="steps" style="position: absolute; left: 50%; transform: translateX(-50%); top: -22px; display:flex; gap:14px; align-items:center;">
        <div class="step step-active" style="width:10px; height:10px; border-radius:999px; background:#22c55e; box-shadow:0 0 0 6px rgba(34,197,94,0.18);"></div>
        <div class="step" style="width:10px; height:10px; border-radius:999px; background:#e5e7eb;"></div>
    </div>
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">Types d'actions disponibles</h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto;">Sélectionnez le type d'action qui correspond à votre besoin</p>
        </div>
        
        <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div class="action-card zoom-hover" data-type="aide" style="background: #fff; border-radius: 20px; padding: 40px 30px; text-align: center; border: 2px solid #f3f4f6; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"></div>
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(239,68,68,0.3);">
                    <i class="fas fa-box"></i>
                </div>
                <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 15px;">📦 {{ __('messages.action.food_aid') }}</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6;">{{ __('messages.action.food_aid_desc') }}</p>
                <ul style="margin: 0 0 30px; padding: 0; list-style: none; text-align: left;">
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Aide alimentaire d'urgence
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Matériel humanitaire
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Support logistique
                    </li>
                </ul>
                <button class="btn btn-primary select-action" style="width: 100%; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; padding: 15px 30px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(239,68,68,0.3);">
                    <i class="fas fa-arrow-right"></i> {{ __('messages.action.choose') }}
                </button>
            </div>
            
            <div class="action-card zoom-hover" data-type="partenariat" style="background: #fff; border-radius: 20px; padding: 40px 30px; text-align: center; border: 2px solid #f3f4f6; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);"></div>
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(139,92,246,0.3);">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 15px;">🤝 {{ __('messages.action.partnership') }}</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6;">{{ __('messages.action.partnership_desc') }}</p>
                <ul style="margin: 0 0 30px; padding: 0; list-style: none; text-align: left;">
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Collaboration institutionnelle
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Projets communs
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Échange d'expertise
                    </li>
                </ul>
                <button class="btn btn-primary select-action" style="width: 100%; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: #fff; padding: 15px 30px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(139,92,246,0.3);">
                    <i class="fas fa-arrow-right"></i> {{ __('messages.action.choose') }}
                </button>
            </div>
            
            <div class="action-card zoom-hover" data-type="audience" style="background: #fff; border-radius: 20px; padding: 40px 30px; text-align: center; border: 2px solid #f3f4f6; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);"></div>
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(6,182,212,0.3);">
                    <i class="fas fa-user-tie"></i>
                </div>
                <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 15px;">🙋‍♂️ {{ __('messages.action.audience') }}</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6;">{{ __('messages.action.audience_desc') }}</p>
                <ul style="margin: 0 0 30px; padding: 0; list-style: none; text-align: left;">
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Rencontre avec la DG
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Présentation de projets
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Discussions stratégiques
                    </li>
                </ul>
                <button class="btn btn-primary select-action" style="width: 100%; background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); color: #fff; padding: 15px 30px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(6,182,212,0.3);">
                    <i class="fas fa-arrow-right"></i> {{ __('messages.action.choose') }}
                </button>
            </div>
            
            <div class="action-card zoom-hover" data-type="autre" style="background: #fff; border-radius: 20px; padding: 40px 30px; text-align: center; border: 2px solid #f3f4f6; transition: all 0.3s ease; cursor: pointer; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);"></div>
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(249,115,22,0.3);">
                    <i class="fas fa-file-alt"></i>
                </div>
                <h3 style="font-size: 1.4rem; font-weight: 700; color: #1f2937; margin-bottom: 15px;">📝 {{ __('messages.action.other') }}</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6;">{{ __('messages.action.other_desc') }}</p>
                <ul style="margin: 0 0 30px; padding: 0; list-style: none; text-align: left;">
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Demandes de stage
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Appui logistique
                    </li>
                    <li style="padding: 8px 0; color: #6b7280; display: flex; align-items: center;">
                        <i class="fas fa-check" style="color: #22c55e; margin-right: 10px; font-size: 14px;"></i>
                        Autres services
                    </li>
                </ul>
                <button class="btn btn-primary select-action" style="width: 100%; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #fff; padding: 15px 30px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 1rem; border: none; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(249,115,22,0.3);">
                    <i class="fas fa-arrow-right"></i> {{ __('messages.action.choose') }}
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Request Form Section -->
<section class="section fade-in" style="background: #f8fafc; display: none; padding: 80px 0; position:relative;" id="requestFormSection">
    <!-- Barre d'étapes -->
    <div class="steps" style="position: absolute; left: 50%; transform: translateX(-50%); top: -22px; display:flex; gap:14px; align-items:center;">
        <div class="step" style="width:10px; height:10px; border-radius:999px; background:#e5e7eb;"></div>
        <div class="step step-active" style="width:10px; height:10px; border-radius:999px; background:#22c55e; box-shadow:0 0 0 6px rgba(34,197,94,0.18);"></div>
    </div>
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">Formulaire de demande</h2>
            <p class="section-subtitle" id="formSubtitle" style="font-size: 1.2rem; color: #6b7280;">Remplissez le formulaire pour votre demande</p>
        </div>
        
        <div style="max-width: 900px; margin: 0 auto;">
            <div class="card" style="background: #fff; border-radius: 20px; padding: 50px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;">
                <form action="{{ route('request.submit') }}" method="POST" id="requestForm">
                    @csrf
                    <input type="hidden" name="type" id="requestType">
                    
                    <!-- Personal Information -->
                    <div style="margin-bottom: 40px;">
                        <h3 style="margin-bottom: 25px; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; font-size: 1.3rem; font-weight: 600; display: flex; align-items: center;">
                            <i class="fas fa-user" style="color: #22c55e; margin-right: 12px; font-size: 20px;"></i> Informations personnelles
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px;">
                            <div>
                                <label for="full_name" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Nom complet *</label>
                                <input type="text" id="full_name" name="full_name" required 
                                       style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #f9fafb;"
                                       placeholder="Votre nom complet">
                            </div>
                            
                            <div>
                                <label for="phone" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Téléphone *</label>
                                <input type="tel" id="phone" name="phone" required 
                                       placeholder="+221 77 123 45 67"
                                       style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #f9fafb;">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
                            <div>
                                <label for="email" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Email *</label>
                                <input type="email" id="email" name="email" required 
                                       style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #f9fafb;"
                                       placeholder="votre.email@exemple.com">
                            </div>
                            
                            <div>
                                <label for="region" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Région *</label>
                                <select id="region" name="region" required 
                                        style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px; transition: all 0.3s ease; background: #f9fafb;">
                                    <option value="">Sélectionnez une région</option>
                                    <option value="Dakar">Dakar</option>
                                    <option value="Thiès">Thiès</option>
                                    <option value="Diourbel">Diourbel</option>
                                    <option value="Fatick">Fatick</option>
                                    <option value="Kaolack">Kaolack</option>
                                    <option value="Kolda">Kolda</option>
                                    <option value="Louga">Louga</option>
                                    <option value="Matam">Matam</option>
                                    <option value="Saint-Louis">Saint-Louis</option>
                                    <option value="Tambacounda">Tambacounda</option>
                                    <option value="Ziguinchor">Ziguinchor</option>
                                    <option value="Kédougou">Kédougou</option>
                                    <option value="Sédhiou">Sédhiou</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div style="margin-bottom: 40px;">
                        <label for="address" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Adresse complète *</label>
                        <textarea id="address" name="address" required rows="3"
                                  placeholder="Votre adresse complète..."
                                  style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px; resize: vertical; transition: all 0.3s ease; background: #f9fafb;"></textarea>
                    </div>
                    
                    <!-- Request Details -->
                    <div style="margin-bottom: 40px;">
                        <h3 style="margin-bottom: 25px; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; font-size: 1.3rem; font-weight: 600; display: flex; align-items: center;">
                            <i class="fas fa-file-alt" style="color: #22c55e; margin-right: 12px; font-size: 20px;"></i> Détails de la demande
                        </h3>
                        
                        <label for="description" style="display: block; margin-bottom: 10px; font-weight: 600; color: #374151;">Description détaillée *</label>
                        <textarea id="description" name="description" required rows="6"
                                  placeholder="Décrivez votre demande en détail..."
                                  style="width: 100%; padding: 15px; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 16px; resize: vertical; transition: all 0.3s ease; background: #f9fafb;"></textarea>
                    </div>
                    
                    <!-- Geolocation -->
                    <div style="margin-bottom: 40px;" id="geolocationSection">
                        <h3 style="margin-bottom: 25px; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; font-size: 1.3rem; font-weight: 600; display: flex; align-items: center;">
                            <i class="fas fa-map-marker-alt" style="color: #22c55e; margin-right: 12px; font-size: 20px;"></i> Géolocalisation <span style="font-size: 14px; color: #ef4444; margin-left: 10px;">(obligatoire)</span>
                        </h3>
                        
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        
                        <div style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); padding: 30px; border-radius: 15px; text-align: center; border: 2px solid #fecaca; position: relative; overflow: hidden;">
                            <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"></div>
                            <i class="fas fa-location-arrow" style="font-size: 32px; color: #ef4444; margin-bottom: 15px;"></i>
                            <p id="geolocation-status" style="margin: 0 0 20px; color: #991b1b; font-weight: 500; font-size: 1.1rem;">
                                La géolocalisation est obligatoire pour les demandes d'aide. Cliquez pour activer.
                            </p>
                            <button type="button" id="getLocation" class="btn btn-secondary" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: #fff; padding: 15px 30px; border-radius: 12px; border: none; font-weight: 600; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(239,68,68,0.3);">
                                <i class="fas fa-map-marker-alt"></i>
                                Activer la géolocalisation
                            </button>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div style="text-align: center;">
                        <button type="submit" id="submitBtn" class="btn btn-primary" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; padding: 18px 50px; border-radius: 15px; border: none; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 10px 30px rgba(34,197,94,0.3); display:inline-flex; align-items:center; gap:10px;">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer ma demande
                        </button>
                        <p id="formSuccess" style="display:none; margin-top:14px; color:#166534; font-weight:600;">Votre demande a bien été envoyée.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section fade-in" style="background: linear-gradient(135deg,#f8fafc 0%, #eef2ff 100%); padding: 70px 0; position: relative;">
    <div style="position:absolute; inset:0; opacity:.08; pointer-events:none; background: radial-gradient(400px 200px at 20% 30%, #22c55e 0, transparent 60%), radial-gradient(400px 200px at 85% 60%, #3b82f6 0, transparent 60%);"></div>
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.3rem; font-weight: 700; color: #111827; margin-bottom: 10px;">Fonctionnalités incluses</h2>
            <p class="section-subtitle" style="font-size: 1.05rem; color: #6b7280; max-width: 680px; margin: 0 auto;">Votre demande bénéficie de ces services automatiques</p>
        </div>
        
        <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 22px;">
            <div class="feature-card zoom-hover" data-feature="geo" title="Activer la géolocalisation" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(6px); border-radius: 16px; padding: 28px 24px; text-align: center; border: 1px solid #e5e7eb; transition: all 0.3s ease; position: relative; overflow: hidden; cursor:pointer;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);"></div>
                <div class="feature-icon" style="width: 64px; height: 64px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; color: #fff; box-shadow: 0 8px 25px rgba(34,197,94,0.3);">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 8px;">Géolocalisation obligatoire</h3>
                <p style="color: #6b7280; line-height: 1.6; font-size:.95rem;">Géolocalisation obligatoire pour les demandes d'aide alimentaire uniquement</p>
            </div>
            
            <div class="feature-card zoom-hover" data-feature="track" title="Suivre ma demande" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(6px); border-radius: 16px; padding: 28px 24px; text-align: center; border: 1px solid #e5e7eb; transition: all 0.3s ease; position: relative; overflow: hidden; cursor:pointer;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);"></div>
                <div class="feature-icon" style="width: 64px; height: 64px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; color: #fff; box-shadow: 0 8px 25px rgba(59,130,246,0.3);">
                    <i class="fas fa-barcode"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 8px;">Code de suivi unique</h3>
                <p style="color: #6b7280; line-height: 1.6; font-size:.95rem;">Un code de suivi unique vous est attribué pour suivre votre demande</p>
            </div>
            
            <div class="feature-card zoom-hover" data-feature="sms" title="SMS de confirmation" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(6px); border-radius: 16px; padding: 28px 24px; text-align: center; border: 1px solid #e5e7eb; transition: all 0.3s ease; position: relative; overflow: hidden; cursor:pointer;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);"></div>
                <div class="feature-icon" style="width: 64px; height: 64px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; color: #fff; box-shadow: 0 8px 25px rgba(245,158,11,0.3);">
                    <i class="fas fa-sms"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 8px;">SMS de confirmation</h3>
                <p style="color: #6b7280; line-height: 1.6; font-size:.95rem;">Vous recevez un SMS de confirmation via l'API Orange</p>
            </div>
            
            <div class="feature-card zoom-hover" style="background: rgba(255,255,255,0.9); backdrop-filter: blur(6px); border-radius: 16px; padding: 28px 24px; text-align: center; border: 1px solid #e5e7eb; transition: all 0.3s ease; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"></div>
                <div class="feature-icon" style="width: 64px; height: 64px; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 18px; font-size: 26px; color: #fff; box-shadow: 0 8px 25px rgba(239,68,68,0.3);">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; color: #111827; margin-bottom: 8px;">Téléchargement PDF</h3>
                <p style="color: #6b7280; line-height: 1.6; font-size:.95rem;">Votre demande peut être téléchargée en format PDF</p>
            </div>
        </div>
    </div>
</section>
<!-- Floating Help Button -->
<a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" title="Besoin d'aide ?" style="position:fixed; right:22px; bottom:22px; z-index:50; background:linear-gradient(135deg,#22c55e,#16a34a); color:#fff; padding:14px 18px; border-radius:999px; box-shadow:0 12px 30px rgba(34,197,94,.35); text-decoration:none; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-question-circle"></i>
    Aide
</a>
@endsection

@section('styles')
<style>
/* Additional styles for action page */
.action-card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.action-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    border-color: #22c55e;
}

.action-card.selected {
    border: 2px solid #22c55e;
    background: #f0fdf4;
    transform: translateY(-5px);
}

.hero-stat:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.3) !important;
}

.feature-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* Steps */
.steps .step { opacity:.9 }
.steps .step-active { outline: 0 }

/* Form styles */
input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: #22c55e !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(34,197,94,0.1);
}

/* Geolocation status */
.geolocation-status {
    background: #f3f4f6;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
}

.geolocation-status.success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    color: #166534;
    border: 2px solid #bbf7d0;
}

.geolocation-status.error {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    color: #dc2626;
    border: 2px solid #fecaca;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .cards-grid {
        grid-template-columns: 1fr;
    }
    
    .form-section .grid {
        grid-template-columns: 1fr;
    }
    
    .hero-stat {
        padding: 15px 20px !important;
    }
    
    .hero-stat div:first-child {
        font-size: 1.5rem !important;
    }
    
    .main-title {
        font-size: 2.5rem !important;
    }
    
    .main-subtitle {
        font-size: 1.1rem !important;
    }
}

/* Animation for form appearance */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#requestFormSection {
    animation: slideInUp 0.6s ease-out;
}
</style>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const actionCards = document.querySelectorAll('.action-card');
    const requestFormSection = document.getElementById('requestFormSection');
    const progressBar = document.getElementById('progressBar');
    const requestTypeInput = document.getElementById('requestType');
    const formSubtitle = document.getElementById('formSubtitle');
    const getLocationBtn = document.getElementById('getLocation');
    const geolocationSection = document.getElementById('geolocationSection');
    const geolocationStatus = document.getElementById('geolocation-status');
    const latitudeInput = document.getElementById('latitude');
    const longitudeInput = document.getElementById('longitude');
    
    // Action card selection
    actionCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove selected class from all cards
            actionCards.forEach(c => c.classList.remove('selected'));
            
            // Add selected class to clicked card
            this.classList.add('selected');
            
            // Get the action type
            const actionType = this.dataset.type;
            requestTypeInput.value = actionType;
            
            // Update form subtitle
            const actionTitles = {
                'aide': 'Demande d\'aide alimentaire ou matérielle',
                'partenariat': 'Demande de partenariat institutionnel',
                'audience': 'Demande d\'audience',
                'autre': 'Autres demandes (stage, appui logistique...)'
            };
            
            formSubtitle.textContent = actionTitles[actionType];
            
            // Show/hide geolocation based on request type
            if (actionType === 'aide') {
                geolocationSection.style.display = 'block';
                geolocationSection.querySelector('h3 span').textContent = '(obligatoire)';
                geolocationStatus.textContent = 'La géolocalisation est obligatoire pour les demandes d\'aide. Cliquez pour activer.';
                latitudeInput.required = true;
                longitudeInput.required = true;
            } else {
                geolocationSection.style.display = 'none';
                latitudeInput.required = false;
                longitudeInput.required = false;
            }
            
            // Show the form section
            requestFormSection.style.display = 'block';
            
            // Scroll to form
            requestFormSection.scrollIntoView({ behavior: 'smooth' });
            if (progressBar) progressBar.style.width = '65%';
        });
    });
    
    // Geolocation
    function getLocation() {
        geolocationStatus.textContent = 'Récupération de votre position...';
        geolocationStatus.className = '';
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    latitudeInput.value = lat;
                    longitudeInput.value = lng;
                    
                    geolocationStatus.textContent = `Position récupérée : ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
                    geolocationStatus.className = 'success';
                },
                function(error) {
                    geolocationStatus.textContent = 'Impossible de récupérer votre position. Vous pouvez continuer sans géolocalisation.';
                    geolocationStatus.className = 'error';
                }
            );
        } else {
            geolocationStatus.textContent = 'La géolocalisation n\'est pas supportée par votre navigateur.';
            geolocationStatus.className = 'error';
        }
    }
    
    // Get location button
    getLocationBtn.addEventListener('click', getLocation);
    
    // Feature quick actions
    document.querySelectorAll('.feature-card[data-feature]').forEach(card => {
        card.addEventListener('click', function(){
            const feature = this.getAttribute('data-feature');
            if (feature === 'geo') {
                // Ouvre la section géolocalisation et déclenche la localisation
                if (geolocationSection) {
                    requestFormSection.style.display = 'block';
                    geolocationSection.scrollIntoView({behavior:'smooth'});
                    getLocation();
                }
            }
            if (feature === 'track') {
                window.location.href = '/suivre-ma-demande';
            }
            if (feature === 'pdf') {
                // Si un code est généré côté serveur, on pourrait rediriger vers la page de suivi
                // Ici, on affiche une info et on scrolle vers le formulaire si non rempli
                const form = document.getElementById('requestForm');
                if (!form) return;
                if (!document.getElementById('requestType').value) {
                    alert("Veuillez d'abord remplir et soumettre la demande pour générer le PDF.");
                    window.scrollTo({top: form.getBoundingClientRect().top + window.scrollY - 80, behavior:'smooth'});
                } else {
                    alert('Le PDF sera disponible après soumission.');
                }
            }
            if (feature === 'sms') {
                alert("Un SMS de confirmation est envoyé automatiquement après l'envoi de votre demande.");
            }
        });
    });

    // Form submission
    document.getElementById('requestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = document.getElementById('submitBtn');
        const successMsg = document.getElementById('formSuccess');
        submitBtn.disabled = true;
        const original = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';

        // Envoi AJAX
        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        }).then(async (res) => {
            if (res.ok) {
                successMsg.style.display = 'block';
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Envoyé';
                if (progressBar) progressBar.style.width = '100%';
                setTimeout(() => { window.location.href = '/suivre-ma-demande'; }, 800);
            } else {
                const data = await res.text();
                alert('Erreur lors de l\'envoi. Vérifiez les champs obligatoires.');
                submitBtn.disabled = false;
                submitBtn.innerHTML = original;
                if (progressBar) progressBar.style.width = '40%';
            }
        }).catch(() => {
            alert('Connexion indisponible. Réessayez.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
            if (progressBar) progressBar.style.width = '40%';
        });
    });
});
</script>
@endpush 
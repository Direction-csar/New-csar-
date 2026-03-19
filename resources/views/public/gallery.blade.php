@extends('layouts.public')

@section('title', 'Nos Missions en Images - CSAR')

@section('content')
<!-- Hero Section Modernisé -->
<section class="hero fade-in" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95) 0%, rgba(34, 197, 94, 0.9) 100%), url('{{ asset('img/1.jpg') }}'); background-size: cover; background-position: center; background-attachment: fixed; min-height: 60vh; display: flex; align-items: center; justify-content: center; padding: 80px 0; position: relative; overflow: hidden;">
    
    <!-- Grid pattern animé -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.15) 1px, transparent 0); background-size: 60px 60px; animation: gridMove 20s linear infinite; opacity: 0.2;"></div>
    
    <!-- Particules flottantes -->
    <div style="position: absolute; top: 15%; left: 10%; width: 8px; height: 8px; background: rgba(34,197,94,0.8); border-radius: 50%; animation: float 6s ease-in-out infinite; box-shadow: 0 0 20px rgba(34,197,94,0.6);"></div>
    <div style="position: absolute; top: 25%; right: 15%; width: 6px; height: 6px; background: rgba(59,130,246,0.7); border-radius: 50%; animation: float 8s ease-in-out infinite reverse; box-shadow: 0 0 15px rgba(59,130,246,0.6);"></div>
    <div style="position: absolute; bottom: 20%; left: 20%; width: 10px; height: 10px; background: rgba(245,158,11,0.6); border-radius: 50%; animation: float 7s ease-in-out infinite; box-shadow: 0 0 25px rgba(245,158,11,0.6);"></div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; text-align: center; position: relative; z-index: 2;">
        <!-- Badge moderne -->
        <div style="display: inline-block; background: rgba(255,255,255,0.1); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.2); border-radius: 50px; padding: 12px 25px; margin-bottom: 30px;">
            <span style="color: #22c55e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px;">📸 Galerie CSAR</span>
        </div>
        
        <h1 class="main-title" style="font-size: 4rem; font-weight: 900; color: #fff; margin-bottom: 25px; letter-spacing: -2px; line-height: 1.1; text-shadow: 0 6px 12px rgba(0,0,0,0.4);">
            Nos Missions en Images
        </h1>
        <p class="main-subtitle" style="font-size: 1.4rem; color: rgba(255,255,255,0.9); max-width: 700px; margin: 0 auto; line-height: 1.7; text-shadow: 0 3px 6px rgba(0,0,0,0.3);">
            Découvrez nos actions humanitaires et interventions de résilience à travers le Sénégal
        </p>
    </div>
</section>

<!-- Diaporama Section -->
<section class="slideshow-section" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 50%, #f1f5f9 100%); padding: 80px 0; position: relative; overflow: hidden;">
    <!-- Décoration arrière-plan -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background-image: radial-gradient(circle at 20% 80%, rgba(34,197,94,0.03) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(59,130,246,0.03) 0%, transparent 50%);"></div>
    
    <div class="container" style="max-width: 1400px; margin: 0 auto; padding: 0 20px; position: relative; z-index: 2;">
        <!-- En-tête -->
        <div style="text-align: center; margin-bottom: 60px;">
            <div style="display: inline-block; background: rgba(34,197,94,0.1); color: #22c55e; padding: 8px 20px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 20px;">
                📸 Galerie Photos
            </div>
            <h2 style="font-size: 3rem; font-weight: 800; color: #1f2937; margin-bottom: 20px; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                Nos Actions sur le Terrain
            </h2>
        </div>

        <!-- Diaporama principal -->
        <div class="slideshow-container" style="position: relative; max-width: 1200px; margin: 0 auto; border-radius: 25px; overflow: hidden; box-shadow: 0 25px 80px rgba(0,0,0,0.15); background: #fff;">
            
            <!-- Images du diaporama -->
            <div class="slideshow-images" style="position: relative; height: 600px; overflow: hidden;">
                
                <!-- Slide 1 - Image N1 -->
                <div class="slide active" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 1; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('images/arriere plan/N1.jpg') }}" alt="Actions Humanitaires" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(34,197,94,0.3) 100%);"></div>
                    <div class="slide-text-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); color: #fff; padding: 50px; z-index: 2;">
                        <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 0 4px 8px rgba(0,0,0,0.5);">Actions Humanitaires</h3>
                        <p style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 20px; opacity: 0.9;">Distribution d'aide alimentaire et assistance aux populations vulnérables</p>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="background: rgba(34,197,94,0.3); color: #22c55e; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">Mission 2024</span>
                            <span style="font-size: 0.9rem; opacity: 0.8;">📍 Région de Dakar</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 2 - Image N2 -->
                <div class="slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('images/arriere plan/N2.jpg') }}" alt="Interventions de Résilience" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(59,130,246,0.3) 100%);"></div>
                    <div class="slide-text-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); color: #fff; padding: 50px; z-index: 2;">
                        <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 0 4px 8px rgba(0,0,0,0.5);">Interventions de Résilience</h3>
                        <p style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 20px; opacity: 0.9;">Renforcement des capacités locales et programmes de développement durable</p>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="background: rgba(59,130,246,0.3); color: #3b82f6; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">Mission 2024</span>
                            <span style="font-size: 0.9rem; opacity: 0.8;">📍 Région de Thiès</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 3 - Image N3 -->
                <div class="slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('images/arriere plan/N3.jpg') }}" alt="Mobilisations Locales" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(245,158,11,0.3) 100%);"></div>
                    <div class="slide-text-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); color: #fff; padding: 50px; z-index: 2;">
                        <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 0 4px 8px rgba(0,0,0,0.5);">Mobilisations Locales</h3>
                        <p style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 20px; opacity: 0.9;">Engagement communautaire et sensibilisation des populations</p>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="background: rgba(245,158,11,0.3); color: #f59e0b; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">Mission 2024</span>
                            <span style="font-size: 0.9rem; opacity: 0.8;">📍 Région de Saint-Louis</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 4 - Image N5 -->
                <div class="slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('images/arriere plan/N5.jpg') }}" alt="Programmes Nutritionnels" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(139,92,246,0.3) 100%);"></div>
                    <div class="slide-text-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); color: #fff; padding: 50px; z-index: 2;">
                        <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 0 4px 8px rgba(0,0,0,0.5);">Programmes Nutritionnels</h3>
                        <p style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 20px; opacity: 0.9;">Lutte contre la malnutrition et promotion de la sécurité alimentaire</p>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="background: rgba(139,92,246,0.3); color: #8b5cf6; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">Mission 2024</span>
                            <span style="font-size: 0.9rem; opacity: 0.8;">📍 Région de Kaolack</span>
                        </div>
                    </div>
                </div>

                <!-- Slide 5 - Image N8 -->
                <div class="slide" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);">
                    <img src="{{ asset('images/arriere plan/N8.jpg') }}" alt="Soutien aux Communautés" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center;">
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(16,185,129,0.3) 100%);"></div>
                    <div class="slide-text-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.9)); color: #fff; padding: 50px; z-index: 2;">
                        <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; text-shadow: 0 4px 8px rgba(0,0,0,0.5);">Soutien aux Communautés</h3>
                        <p style="font-size: 1.2rem; line-height: 1.6; margin-bottom: 20px; opacity: 0.9;">Accompagnement des populations et renforcement de la résilience locale</p>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span style="background: rgba(16,185,129,0.3); color: #10b981; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 600;">Mission 2024</span>
                            <span style="font-size: 0.9rem; opacity: 0.8;">📍 Région de Kolda</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contrôles de navigation -->
            <button class="prev-btn" onclick="changeSlide(-1)" style="position: absolute; left: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.3s ease; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-left" style="font-size: 20px; color: #1f2937; margin-right: 2px;"></i>
            </button>
            
            <button class="next-btn" onclick="changeSlide(1)" style="position: absolute; right: 30px; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.9); border: none; width: 60px; height: 60px; border-radius: 50%; cursor: pointer; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: all 0.3s ease; z-index: 10; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chevron-right" style="font-size: 20px; color: #1f2937; margin-left: 2px;"></i>
            </button>

            <!-- Indicateurs -->
            <div class="slide-indicators" style="position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 12px; z-index: 10;">
                <button class="indicator active" onclick="goToSlide(0)" style="width: 12px; height: 12px; border-radius: 50%; border: none; background: #22c55e; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);"></button>
                <button class="indicator" onclick="goToSlide(1)" style="width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s ease;"></button>
                <button class="indicator" onclick="goToSlide(2)" style="width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s ease;"></button>
                <button class="indicator" onclick="goToSlide(3)" style="width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s ease;"></button>
                <button class="indicator" onclick="goToSlide(4)" style="width: 12px; height: 12px; border-radius: 50%; border: none; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s ease;"></button>
            </div>
        </div>

        <!-- Contrôles de lecture -->
        <div style="text-align: center; margin-top: 40px;">
            <button id="playPauseBtn" onclick="toggleAutoplay()" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; border: none; padding: 15px 30px; border-radius: 15px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3); display: inline-flex; align-items: center; gap: 10px;">
                <i class="fas fa-pause" id="playPauseIcon"></i>
                <span id="playPauseText">Pause</span>
            </button>
        </div>
    </div>
</section>

<!-- Key Figures Section -->
<section class="section fade-in" style="background: #fff; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        {{-- Section chiffres clés supprimée --}}
    </div>
</section>

<!-- Mission Types Section -->
<section class="section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">Types d'Interventions</h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Nos différentes missions sur le terrain
            </p>
        </div>
        
        <div class="cards-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
            <div class="card zoom-hover" style="background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease;">
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 15px; text-align: center;">Actions Humanitaires</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6; text-align: center;">Distribution d'aide alimentaire d'urgence et matériel humanitaire</p>
                <ul style="margin: 0; padding-left: 20px; color: #6b7280; line-height: 1.8;">
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #22c55e; margin-right: 10px;"></i>Distribution de denrées alimentaires</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #22c55e; margin-right: 10px;"></i>Fourniture de matériel médical</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #22c55e; margin-right: 10px;"></i>Support logistique d'urgence</li>
                </ul>
            </div>
            
            <div class="card zoom-hover" style="background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease;">
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 15px; text-align: center;">Interventions de Résilience</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6; text-align: center;">Renforcement des capacités locales et prévention des crises</p>
                <ul style="margin: 0; padding-left: 20px; color: #6b7280; line-height: 1.8;">
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #3b82f6; margin-right: 10px;"></i>Formation des communautés</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #3b82f6; margin-right: 10px;"></i>Développement de systèmes d'alerte</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #3b82f6; margin-right: 10px;"></i>Renforcement des infrastructures</li>
                </ul>
            </div>
            
            <div class="card zoom-hover" style="background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease;">
                <div class="card-icon" style="width: 80px; height: 80px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 25px; font-size: 32px; color: #fff; box-shadow: 0 8px 25px rgba(245, 158, 11, 0.3);">
                    <i class="fas fa-truck"></i>
                </div>
                <h3 style="font-size: 1.5rem; font-weight: 700; color: #1f2937; margin-bottom: 15px; text-align: center;">Livraisons et Mobilisations</h3>
                <p style="color: #6b7280; margin-bottom: 25px; line-height: 1.6; text-align: center;">Transport et distribution stratégique des ressources</p>
                <ul style="margin: 0; padding-left: 20px; color: #6b7280; line-height: 1.8;">
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #f59e0b; margin-right: 10px;"></i>Transport de marchandises</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #f59e0b; margin-right: 10px;"></i>Coordination logistique</li>
                    <li style="margin-bottom: 8px;"><i class="fas fa-check" style="color: #f59e0b; margin-right: 10px;"></i>Mobilisation des équipes</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Galerie Photos de Missions -->
<section class="section fade-in" style="background: #fff; padding: 100px 0;">
    <div class="container" style="max-width: 1400px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 60px;">
            <div style="display: inline-block; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); padding: 10px 24px; border-radius: 50px; margin-bottom: 20px;">
                <span style="color: #22c55e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.2px;">
                    <i class="fas fa-images" style="margin-right: 8px;"></i>
                    Nos Missions en Images
                </span>
            </div>
            <h2 style="font-size: 3rem; font-weight: 800; color: #1f2937; margin-bottom: 20px;">
                Galerie de Missions
            </h2>
            <p style="color: #6b7280; font-size: 1.2rem; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                Découvrez nos actions sur le terrain à travers ces images de nos différentes interventions
            </p>
        </div>

        <!-- Grille d'images -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
            @php
                $missionImages = [
                    ['src' => 'images/bloc/csar1.jpg', 'title' => 'Distribution alimentaire', 'location' => 'Dakar'],
                    ['src' => 'images/bloc/csar2.jpg', 'title' => 'Aide humanitaire', 'location' => 'Thiès'],
                    ['src' => 'images/bloc/csar3.jpg', 'title' => 'Mission de terrain', 'location' => 'Saint-Louis'],
                    ['src' => 'images/bloc/csar4.jpg', 'title' => 'Soutien aux communautés', 'location' => 'Kaolack'],
                    ['src' => 'images/bloc/csar5..jpg', 'title' => 'Programme nutritionnel', 'location' => 'Kolda'],
                    ['src' => 'images/bloc/csar6.jpg', 'title' => 'Intervention d\'urgence', 'location' => 'Ziguinchor'],
                    ['src' => 'images/bloc/csar7.jpg', 'title' => 'Renforcement de capacités', 'location' => 'Tambacounda'],
                    ['src' => 'images/bloc/csar8.jpg', 'title' => 'Mobilisation locale', 'location' => 'Louga'],
                    ['src' => 'images/bloc/csar9.jpg', 'title' => 'Assistance alimentaire', 'location' => 'Fatick'],
                    ['src' => 'images/bloc/csar10.jpg', 'title' => 'Sécurité alimentaire', 'location' => 'Diourbel'],
                    ['src' => 'images/bloc/csar11.jpg', 'title' => 'Action humanitaire', 'location' => 'Matam'],
                    ['src' => 'images/bloc/csar12.jpg', 'title' => 'Distribution de vivres', 'location' => 'Kédougou'],
                ];
            @endphp

            @foreach($missionImages as $index => $image)
            <div class="mission-photo-card" style="position: relative; border-radius: 15px; overflow: hidden; cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.4s ease; background: #f3f4f6;" 
                 onclick="openLightbox('{{ asset($image['src']) }}', '{{ $image['title'] }}', '{{ $image['location'] }}')">
                <img src="{{ asset($image['src']) }}" alt="{{ $image['title'] }}" 
                     style="width: 100%; height: 350px; object-fit: cover; transition: transform 0.4s ease; display: block;"
                     onerror="this.style.display='none'; this.parentElement.innerHTML='<div style=\'height:350px;display:flex;align-items:center;justify-content:center;color:#9ca3af;\'><i class=\'fas fa-image\' style=\'font-size:3rem;\'></i></div>';">
                <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 25px 20px; color: white; transform: translateY(100%); transition: transform 0.4s ease;" class="photo-overlay">
                    <h4 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 8px 0;">{{ $image['title'] }}</h4>
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">
                        <i class="fas fa-map-marker-alt"></i> {{ $image['location'] }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<style>
.mission-photo-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(34, 197, 94, 0.2);
}

.mission-photo-card:hover img {
    transform: scale(1.1);
}

.mission-photo-card:hover .photo-overlay {
    transform: translateY(0);
}
</style>

<!-- Témoignages de Mission Section -->
<section class="section fade-in" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); padding: 100px 0; position: relative; overflow: hidden;">
    <!-- Décoration arrière-plan -->
    <div style="position: absolute; top: -100px; right: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(34, 197, 94, 0.08), transparent); border-radius: 50%; filter: blur(80px);"></div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; position: relative; z-index: 1;">
        <!-- En-tête -->
        <div style="text-align: center; margin-bottom: 60px;">
            <div style="display: inline-block; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); padding: 10px 24px; border-radius: 50px; margin-bottom: 20px;">
                <span style="color: #22c55e; font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1.2px;">
                    <i class="fas fa-comments" style="margin-right: 8px;"></i>
                    Témoignages de Nos Équipes
                </span>
            </div>
            <h2 style="font-size: 3rem; font-weight: 800; color: #1f2937; margin-bottom: 20px; background: linear-gradient(135deg, #1f2937 0%, #22c55e 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                Retours de Mission
            </h2>
            <p style="color: #6b7280; font-size: 1.2rem; max-width: 700px; margin: 0 auto; line-height: 1.7;">
                Découvrez les témoignages de reconnaissance de nos membres envoyés en mission sur le terrain
            </p>
        </div>

        <!-- Grille de témoignages -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px; margin-bottom: 50px;">
            @php
                $missionTestimonials = \App\Models\Testimonial::approved()
                    ->mission()
                    ->latest()
                    ->take(6)
                    ->get();
            @endphp

            @forelse($missionTestimonials as $index => $testimonial)
            <div class="testimonial-card" style="background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 2px solid #f3f4f6; transition: all 0.4s; position: relative; overflow: hidden;" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                <!-- Accent coloré -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #22c55e, #10b981);"></div>
                
                <!-- Icône de citation -->
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                    <i class="fas fa-quote-left" style="font-size: 1.2rem; color: #22c55e;"></i>
                </div>

                <!-- Message -->
                <p style="color: #4b5563; font-size: 1rem; line-height: 1.7; margin-bottom: 25px; font-style: italic;">
                    "{{ Str::limit($testimonial->message, 150) }}"
                </p>

                <!-- Info mission -->
                @if($testimonial->mission_location || $testimonial->mission_date)
                <div style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
                    @if($testimonial->mission_location)
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(34, 197, 94, 0.1); color: #059669; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $testimonial->mission_location }}
                    </span>
                    @endif
                    
                    @if($testimonial->mission_date)
                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; background: rgba(59, 130, 246, 0.1); color: #2563eb; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                        <i class="fas fa-calendar"></i>
                        {{ $testimonial->mission_date->format('M Y') }}
                    </span>
                    @endif
                </div>
                @endif

                <!-- Auteur -->
                <div style="display: flex; align-items: center; gap: 15px; padding-top: 20px; border-top: 1px solid #f3f4f6;">
                    <div style="width: 45px; height: 45px; background: linear-gradient(135deg, #22c55e, #10b981); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem;">
                        {{ substr($testimonial->name, 0, 1) }}
                    </div>
                    <div>
                        <h4 style="font-size: 1rem; font-weight: 700; color: #1f2937; margin: 0;">
                            {{ $testimonial->name }}
                        </h4>
                        @if($testimonial->organization)
                        <p style="font-size: 0.85rem; color: #6b7280; margin: 0;">
                            {{ $testimonial->organization }}
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Note si disponible -->
                @if($testimonial->rating)
                <div style="margin-top: 15px; display: flex; gap: 4px;">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star" style="color: {{ $i <= $testimonial->rating ? '#f59e0b' : '#e5e7eb' }}; font-size: 0.9rem;"></i>
                    @endfor
                </div>
                @endif
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px;">
                <i class="fas fa-comments" style="font-size: 4rem; color: #d1d5db; margin-bottom: 20px;"></i>
                <p style="color: #6b7280; font-size: 1.1rem;">
                    Aucun témoignage de mission pour le moment.
                </p>
            </div>
            @endforelse
        </div>

        <!-- Bouton pour partager son témoignage -->
        <div style="text-align: center;">
            <a href="{{ route('testimonials.create', ['locale' => app()->getLocale()]) }}" class="btn btn-success zoom-hover" style="display: inline-flex; align-items: center; gap: 12px; padding: 16px 35px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 1.1rem; box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3); transition: all 0.3s;">
                <i class="fas fa-pen"></i>
                Partager votre témoignage de mission
            </a>
        </div>
    </div>
</section>

<style>
.testimonial-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(34, 197, 94, 0.15);
    border-color: #22c55e;
}
</style>

<!-- Contact Section -->
<section class="section fade-in" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div style="text-align: center; max-width: 700px; margin: 0 auto;">
            <h3 style="font-size: 2rem; font-weight: 700; color: #fff; margin-bottom: 20px;">En savoir plus sur nos missions</h3>
            <p style="font-size: 1.2rem; color: rgba(255,255,255,0.8); margin-bottom: 40px; line-height: 1.6;">
                Pour plus d'informations sur nos interventions ou pour demander des photos spécifiques, 
                n'hésitez pas à nous contacter.
            </p>
            <a href="{{ route('contact', ['locale' => app()->getLocale()]) }}" class="btn btn-primary zoom-hover" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1.1rem; transition: all 0.3s ease; box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);">
                <i class="fas fa-envelope"></i>
                Nous contacter
            </a>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.95); backdrop-filter: blur(10px);">
    <div class="lightbox-content" onclick="event.stopPropagation()" style="position: relative; margin: auto; padding: 30px; width: 90%; max-width: 900px; top: 50%; transform: translateY(-50%);">
        <span class="lightbox-close" onclick="closeLightbox()" style="position: absolute; top: 15px; right: 25px; color: white; font-size: 40px; font-weight: bold; cursor: pointer; z-index: 1001; transition: all 0.3s ease;">&times;</span>
        <img id="lightbox-image" src="" alt="" style="width: 100%; max-height: 70vh; object-fit: contain; border-radius: 12px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <div class="lightbox-info" style="background: rgba(255, 255, 255, 0.98); padding: 30px; border-radius: 12px; margin-top: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <h3 id="lightbox-title" style="color: #1f2937; margin-bottom: 15px; font-size: 1.5rem; font-weight: 700;"></h3>
            <p id="lightbox-description" style="color: #6b7280; line-height: 1.6; font-size: 1.1rem;"></p>
        </div>
    </div>
</div>
<!-- Styles CSS pour le diaporama -->
<style>
/* Animations de base - optimisées pour la performance */
@keyframes float {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg); 
        opacity: 0.8;
    }
    50% { 
        transform: translateY(-15px) rotate(180deg); 
        opacity: 1;
    }
}

@keyframes gridMove {
    0% { transform: translate(0, 0); opacity: 0.2; }
    50% { opacity: 0.1; }
    100% { transform: translate(60px, 60px); opacity: 0.2; }
}

@keyframes slideIn {
    from { 
        opacity: 0; 
        transform: translateY(30px); 
    }
    to { 
        opacity: 1; 
        transform: translateY(0); 
    }
}

/* Styles pour le diaporama - optimisés */
.slideshow-container {
    position: relative;
    transition: transform 0.4s ease, box-shadow 0.4s ease;
    will-change: transform;
}

.slideshow-container:hover {
    transform: scale(1.005);
    box-shadow: 0 30px 100px rgba(0,0,0,0.2);
}

.slide {
    transition: opacity 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94), 
                transform 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    will-change: opacity, transform;
}

.slide.active {
    opacity: 1;
    transform: translateX(0);
}

.slide:not(.active) {
    opacity: 0;
    transform: translateX(100px);
}

/* Boutons de navigation - optimisés */
.prev-btn, .next-btn {
    transition: background-color 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
    will-change: background-color, transform;
}

.prev-btn:hover, .next-btn:hover {
    background: #22c55e !important;
    color: #fff !important;
    transform: translateY(-50%) scale(1.05);
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.3);
}

.prev-btn:hover i, .next-btn:hover i {
    color: #fff !important;
}

/* Indicateurs */
.indicator {
    transition: all 0.3s ease;
}

.indicator:hover {
    transform: scale(1.3);
}

.indicator.active {
    background: #22c55e !important;
    box-shadow: 0 2px 8px rgba(34, 197, 94, 0.4);
    transform: scale(1.2);
}

/* Animations d'apparition */
.fade-in {
    animation: slideIn 0.8s ease forwards;
}

/* Responsive */
@media (max-width: 768px) {
    .slideshow-images {
        height: 500px !important;
    }
    
    .slide h3 {
        font-size: 1.5rem !important;
    }
    
    .slide p {
        font-size: 0.9rem !important;
        line-height: 1.4 !important;
    }
    
    .slide-text-overlay {
        padding: 20px 15px !important;
        background: linear-gradient(transparent 0%, rgba(0,0,0,0.7) 30%, rgba(0,0,0,0.95) 100%) !important;
        max-height: 35% !important;
        overflow: hidden !important;
    }
    
    .slide-text-overlay h3 {
        font-size: 1.5rem !important;
        margin-bottom: 8px !important;
    }
    
    .slide-text-overlay p {
        font-size: 0.9rem !important;
        line-height: 1.4 !important;
        margin-bottom: 12px !important;
    }
    
    .slide-text-overlay div {
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
    
    .slide-text-overlay span {
        font-size: 0.8rem !important;
        padding: 6px 12px !important;
    }
    
    .prev-btn, .next-btn {
        width: 45px !important;
        height: 45px !important;
        background: rgba(255,255,255,0.95) !important;
    }
    
    .prev-btn {
        left: 10px !important;
    }
    
    .next-btn {
        right: 10px !important;
    }
    
    .slide-indicators {
        bottom: 15px !important;
    }
    
    .indicator {
        width: 10px !important;
        height: 10px !important;
    }
}
</style>

<!-- JavaScript pour le diaporama -->
<script>
let currentSlide = 0;
let isAutoplay = true;
let autoplayInterval;

// Démarre le diaporama automatique
function startAutoplay() {
    // Adapter la vitesse selon la taille d'écran
    let interval = 8000; // Desktop: 8 secondes
    if (window.innerWidth <= 480) {
        interval = 18000; // Très petit mobile: 18 secondes
    } else if (window.innerWidth <= 768) {
        interval = 15000; // Mobile: 15 secondes
    } else if (window.innerWidth <= 1024) {
        interval = 12000; // Tablette: 12 secondes
    }
    
    autoplayInterval = setInterval(() => {
        if (isAutoplay) {
            changeSlide(1);
        }
    }, interval);
}

// Arrête le diaporama automatique
function stopAutoplay() {
    clearInterval(autoplayInterval);
}

// Change de slide
function changeSlide(direction) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    // Retirer la classe active de la slide actuelle
    slides[currentSlide].classList.remove('active');
    slides[currentSlide].style.opacity = '0';
    indicators[currentSlide].classList.remove('active');
    indicators[currentSlide].style.background = 'rgba(255,255,255,0.5)';
    indicators[currentSlide].style.boxShadow = 'none';
    
    // Calculer la nouvelle slide
    currentSlide += direction;
    
    if (currentSlide >= slides.length) {
        currentSlide = 0;
    } else if (currentSlide < 0) {
        currentSlide = slides.length - 1;
    }
    
    // Activer la nouvelle slide
    setTimeout(() => {
        slides[currentSlide].classList.add('active');
        slides[currentSlide].style.opacity = '1';
        indicators[currentSlide].classList.add('active');
        indicators[currentSlide].style.background = '#22c55e';
        indicators[currentSlide].style.boxShadow = '0 2px 8px rgba(34, 197, 94, 0.4)';
    }, 100);
}

// Va directement à une slide spécifique
function goToSlide(slideIndex) {
    const slides = document.querySelectorAll('.slide');
    const indicators = document.querySelectorAll('.indicator');
    
    // Retirer la classe active de toutes les slides
    slides.forEach(slide => {
        slide.classList.remove('active');
        slide.style.opacity = '0';
    });
    
    indicators.forEach(indicator => {
        indicator.classList.remove('active');
        indicator.style.background = 'rgba(255,255,255,0.5)';
        indicator.style.boxShadow = 'none';
    });
    
    // Activer la slide sélectionnée
    currentSlide = slideIndex;
    setTimeout(() => {
        slides[currentSlide].classList.add('active');
        slides[currentSlide].style.opacity = '1';
        indicators[currentSlide].classList.add('active');
        indicators[currentSlide].style.background = '#22c55e';
        indicators[currentSlide].style.boxShadow = '0 2px 8px rgba(34, 197, 94, 0.4)';
    }, 100);
}

// Toggle autoplay
function toggleAutoplay() {
    const playPauseIcon = document.getElementById('playPauseIcon');
    const playPauseText = document.getElementById('playPauseText');
    
    isAutoplay = !isAutoplay;
    
    if (isAutoplay) {
        playPauseIcon.className = 'fas fa-pause';
        playPauseText.textContent = 'Pause';
        startAutoplay();
    } else {
        playPauseIcon.className = 'fas fa-play';
        playPauseText.textContent = 'Lecture';
        stopAutoplay();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Démarrer l'autoplay
    startAutoplay();
    
    // Pause au hover
    const slideshowContainer = document.querySelector('.slideshow-container');
    slideshowContainer.addEventListener('mouseenter', () => {
        if (isAutoplay) stopAutoplay();
    });
    
    slideshowContainer.addEventListener('mouseleave', () => {
        if (isAutoplay) startAutoplay();
    });
    
    // Navigation au clavier
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            changeSlide(-1);
        } else if (e.key === 'ArrowRight') {
            changeSlide(1);
        } else if (e.key === ' ') {
            e.preventDefault();
            toggleAutoplay();
        }
    });
    
    // Redémarrer l'autoplay avec la bonne vitesse lors du redimensionnement
    window.addEventListener('resize', function() {
        if (isAutoplay) {
            stopAutoplay();
            setTimeout(() => {
                startAutoplay();
            }, 100);
        }
    });
    
    // Animation d'apparition des éléments
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, {
        threshold: 0.1
    });
    
    // Observer les sections
    document.querySelectorAll('.section, .slideshow-section').forEach(el => {
        observer.observe(el);
    });
    
    console.log('🎬 Diaporama CSAR initialisé avec succès !');
});

// Préchargement des images
window.addEventListener('load', function() {
    const images = document.querySelectorAll('.slide img');
    images.forEach(img => {
        if (!img.complete) {
            img.addEventListener('load', () => {
                console.log(`✅ Image chargée: ${img.alt}`);
            });
        }
    });
});
</script>

@endsection

@push('styles')
<style>
/* Animations pour les cercles flottants */
@keyframes float {
    0%, 100% { 
        transform: translateY(0px) rotate(0deg); 
    }
    50% { 
        transform: translateY(-20px) rotate(180deg); 
    }
}

.floating-circle {
    animation: float 6s ease-in-out infinite;
}

/* Animation du titre principal - effet de typewriter */
.animated-title {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}

.title-word {
    animation: color-animation 4s linear infinite;
    display: inline-block;
    position: relative;
    overflow: hidden;
}

.title-word::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shine 3s infinite;
}

.title-word-1 {
    --color-1: #ffffff;
    --color-2: #e5e7eb;
    --color-3: #d1d5db;
    animation-delay: 0s;
}

.title-word-2 {
    --color-1: #e5e7eb;
    --color-2: #ffffff;
    --color-3: #d1d5db;
    animation-delay: 0.5s;
}

.title-word-3 {
    --color-1: #d1d5db;
    --color-2: #ffffff;
    --color-3: #e5e7eb;
    animation-delay: 1s;
}

.title-word-4 {
    --color-1: #e5e7eb;
    --color-2: #ffffff;
    --color-3: #d1d5db;
    animation-delay: 1.5s;
}

@keyframes color-animation {
    0%    { color: var(--color-1) }
    32%   { color: var(--color-1) }
    33%   { color: var(--color-2) }
    65%   { color: var(--color-2) }
    66%   { color: var(--color-3) }
    99%   { color: var(--color-3) }
    100%  { color: var(--color-1) }
}

@keyframes shine {
    0% { left: -100%; }
    50% { left: 100%; }
    100% { left: 100%; }
}

/* Animation du sous-titre - effet de fade-in progressif */
.animated-subtitle {
    animation: subtitle-animation 2s ease-out;
    opacity: 0;
    animation-fill-mode: forwards;
    animation-delay: 1s;
}

@keyframes subtitle-animation {
    0% {
        opacity: 0;
        transform: translateY(30px);
        filter: blur(5px);
    }
    50% {
        opacity: 0.5;
        transform: translateY(15px);
        filter: blur(2px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
    }
}

/* Animation de fade-in générale */
.fade-in {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Effet de zoom au hover */
.zoom-hover:hover {
    transform: scale(1.02);
    transition: all 0.3s ease;
}

/* Gallery Grid */
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.gallery-item {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    background: #fff;
}

.gallery-item:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
}

.gallery-item img {
    width: 100%;
    height: 280px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.gallery-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
    color: white;
    padding: 30px;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.gallery-item:hover .gallery-overlay {
    transform: translateY(0);
}

.gallery-overlay h3 {
    font-size: 1.3rem;
    margin-bottom: 12px;
    font-weight: 700;
}

.gallery-overlay p {
    font-size: 1rem;
    margin-bottom: 15px;
    opacity: 0.9;
    line-height: 1.5;
}

.gallery-date {
    font-size: 0.9rem;
    opacity: 0.8;
    font-weight: 600;
}

/* Empty Gallery */
.empty-gallery {
    text-align: center;
    padding: 80px 20px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 2px dashed #e5e7eb;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
}

.stat-item {
    text-align: center;
    padding: 40px 30px;
    border-radius: 20px;
    transition: all 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.stat-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 32px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.stat-number {
    font-size: 3rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 1.1rem;
    color: #6b7280;
    font-weight: 600;
}

/* Cards Grid */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.card {
    background: #fff;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border: 1px solid #f3f4f6;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.card-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 32px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.card h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
    text-align: center;
}

.card p {
    color: #6b7280;
    margin-bottom: 25px;
    line-height: 1.6;
    text-align: center;
}

.card ul {
    margin: 0;
    padding-left: 20px;
    color: #6b7280;
    line-height: 1.8;
}

.card li {
    margin-bottom: 8px;
}

/* Lightbox */
.lightbox {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.95);
    backdrop-filter: blur(10px);
}

.lightbox-content {
    position: relative;
    margin: auto;
    padding: 30px;
    width: 90%;
    max-width: 900px;
    top: 50%;
    transform: translateY(-50%);
}

.lightbox-close {
    position: absolute;
    top: 15px;
    right: 25px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 1001;
    transition: all 0.3s ease;
}

.lightbox-close:hover {
    color: #ccc;
    transform: scale(1.1);
}

#lightbox-image {
    width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
}

.lightbox-info {
    background: rgba(255, 255, 255, 0.98);
    padding: 30px;
    border-radius: 12px;
    margin-top: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.lightbox-info h3 {
    color: #1f2937;
    margin-bottom: 15px;
    font-size: 1.5rem;
    font-weight: 700;
}

.lightbox-info p {
    color: #6b7280;
    line-height: 1.6;
    font-size: 1.1rem;
}

/* Button hover effects */
.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(34, 197, 94, 0.4) !important;
}

/* Forcer le positionnement correct des textes sur toutes les tailles */
.slide-text-overlay {
    position: absolute !important;
    bottom: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 10 !important;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
    /* Ralentir et optimiser les animations sur tablette */
    .slide {
        transition: opacity 2.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important, 
                    transform 2.2s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }
    
    .slide-text-overlay {
        max-height: 40% !important;
        padding: 30px 20px !important;
    }
    
    /* Animations d'arrière-plan plus lentes sur tablette */
    .floating-circle {
        animation: float 12s ease-in-out infinite !important;
    }
    
    .hero div[style*="animation: gridMove"] {
        animation: gridMove 40s linear infinite !important;
    }
    
    /* Réduire les effets de hover sur tablette */
    .slideshow-container:hover {
        transform: scale(1.002) !important;
        box-shadow: 0 25px 80px rgba(0,0,0,0.15) !important;
    }
    
    .prev-btn:hover, .next-btn:hover {
        transform: translateY(-50%) scale(1.03) !important;
    }
}

@media (max-width: 768px) {
    .main-title {
        font-size: 2.2rem !important;
        line-height: 1.2 !important;
        margin-bottom: 15px !important;
    }
    
    .title-word {
        font-size: 2rem !important;
    }
    
    .main-subtitle {
        font-size: 1rem !important;
        line-height: 1.5 !important;
        padding: 0 20px !important;
    }
    
    .animated-title {
        flex-direction: column;
        gap: 4px;
    }
    
    .hero {
        min-height: 50vh !important;
        padding: 60px 0 !important;
    }
    
    .slideshow-section {
        padding: 60px 0 !important;
    }
    
    .slideshow-container {
        margin: 0 10px !important;
        border-radius: 15px !important;
    }
    
    .gallery-grid {
        grid-template-columns: 1fr;
    }
    
    .gallery-overlay {
        transform: translateY(0);
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.9));
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cards-grid {
        grid-template-columns: 1fr;
        gap: 20px !important;
    }
    
    .card {
        padding: 25px 20px !important;
    }
    
    .lightbox-content {
        width: 95%;
        padding: 15px;
    }
    
    /* Animations très lentes et optimisées sur mobile */
    .slide {
        transition: opacity 3.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important, 
                    transform 3.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }
    
    /* Animations d'arrière-plan très lentes sur mobile */
    .floating-circle {
        animation: float 25s ease-in-out infinite !important;
    }
    
    .hero div[style*="animation: gridMove"] {
        animation: gridMove 70s linear infinite !important;
    }
    
    /* Désactiver complètement les effets de hover sur mobile */
    .slideshow-container:hover {
        transform: none !important;
        box-shadow: 0 25px 80px rgba(0,0,0,0.15) !important;
    }
    
    .prev-btn:hover, .next-btn:hover {
        transform: translateY(-50%) !important;
        background: rgba(255,255,255,0.95) !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    
    .zoom-hover:hover {
        transform: none !important;
    }
    
    .card:hover {
        transform: none !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
    }
    
    .btn-primary:hover {
        transform: none !important;
    }
    
    .indicator:hover {
        transform: none !important;
    }
    
    /* Optimisation des performances sur mobile */
    * {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
    }
}

@media (max-width: 480px) {
    .title-word {
        font-size: 1.6rem !important;
    }
    
    .main-title {
        font-size: 1.8rem !important;
    }
    
    .main-subtitle {
        font-size: 0.9rem !important;
        padding: 0 15px !important;
    }
    
    .hero {
        min-height: 45vh !important;
        padding: 40px 0 !important;
    }
    
    .slideshow-images {
        height: 400px !important;
    }
    
    .slide h3 {
        font-size: 1.3rem !important;
    }
    
    .slide p {
        font-size: 0.8rem !important;
        line-height: 1.3 !important;
    }
    
    .slide-text-overlay {
        padding: 15px 10px !important;
        max-height: 30% !important;
        background: linear-gradient(transparent 0%, rgba(0,0,0,0.8) 20%, rgba(0,0,0,0.98) 100%) !important;
    }
    
    .slide-text-overlay h3 {
        font-size: 1.3rem !important;
        margin-bottom: 6px !important;
    }
    
    .slide-text-overlay p {
        font-size: 0.8rem !important;
        line-height: 1.3 !important;
        margin-bottom: 8px !important;
    }
    
    .slide-text-overlay div {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 6px !important;
    }
    
    .slide-text-overlay span {
        font-size: 0.7rem !important;
        padding: 4px 8px !important;
    }
    
    .slideshow-container {
        margin: 0 5px !important;
        border-radius: 12px !important;
    }
    
    .prev-btn, .next-btn {
        width: 40px !important;
        height: 40px !important;
    }
    
    .prev-btn i, .next-btn i {
        font-size: 16px !important;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stat-item {
        padding: 25px 15px;
    }
    
    .card {
        padding: 20px 15px;
    }
    
    .card h3 {
        font-size: 1.3rem !important;
    }
    
    .card p {
        font-size: 0.9rem !important;
    }
    
    /* Animations ultra-lentes sur très petits écrans */
    .slide {
        transition: opacity 4.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important, 
                    transform 4.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) !important;
    }
    
    /* Animations d'arrière-plan ultra-lentes */
    .floating-circle {
        animation: float 30s ease-in-out infinite !important;
    }
    
    .hero div[style*="animation: gridMove"] {
        animation: gridMove 90s linear infinite !important;
    }
    
    /* Performance maximale sur très petits écrans */
    .slideshow-container {
        will-change: auto !important;
    }
    
    .slide {
        will-change: auto !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function openLightbox(imageSrc, title, description) {
    document.getElementById('lightbox-image').src = imageSrc;
    document.getElementById('lightbox-title').textContent = title;
    document.getElementById('lightbox-description').textContent = description;
    document.getElementById('lightbox').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Fermer avec la touche Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeLightbox();
    }
});
</script>
@endpush 
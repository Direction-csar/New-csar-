@extends('layouts.public')

@section('title', 'Discours Officiels - CSAR')

@section('content')
<!-- Hero Section -->
<section class="hero-section fade-in" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); min-height: 40vh; display: flex; align-items: center; position: relative; overflow: hidden;">
    <!-- Floating decorative elements -->
    <div style="position: absolute; top: 10%; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
    <div style="position: absolute; top: 20%; right: 15%; width: 80px; height: 80px; background: rgba(255,255,255,0.08); border-radius: 50%; animation: float 8s ease-in-out infinite reverse;"></div>
    <div style="position: absolute; bottom: 15%; left: 20%; width: 120px; height: 120px; background: rgba(255,255,255,0.05); border-radius: 50%; animation: float 7s ease-in-out infinite;"></div>

    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 50px 20px; text-align: center; position: relative; z-index: 2;">
        <h1 class="main-title" style="font-size: 3.5rem; font-weight: 800; color: #fff; margin-bottom: 20px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            Discours Officiels
        </h1>
        <p class="main-subtitle" style="font-size: 1.4rem; color: rgba(255,255,255,0.9); max-width: 600px; margin: 0 auto; line-height: 1.6; font-weight: 400;">
            Messages et allocutions des dirigeants du Commissariat à la Sécurité Alimentaire et à la Résilience
        </p>
    </div>
</section>

<!-- Discours Section -->
<section class="speeches-section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">
                Discours et Allocutions
            </h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto;">
                Messages officiels des dirigeants du Commissariat à la Sécurité Alimentaire et à la Résilience
            </p>
        </div>

        @if(isset($speeches) && $speeches->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px;">
            @foreach($speeches as $speech)
            <article class="speech-card zoom-hover" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease; position: relative;">
                <div style="padding: 30px;">
                    <!-- Speech Header -->
                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 25px;">
                        @if($speech->portrait)
                        <div style="width: 80px; height: 80px; border-radius: 50%; overflow: hidden; border: 3px solid #22c55e; flex-shrink: 0;">
                            <img src="{{ asset($speech->portrait) }}" alt="{{ $speech->author }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        @else
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-user-tie" style="color: #fff; font-size: 32px;"></i>
                        </div>
                        @endif

                        <div style="flex: 1;">
                            <h3 style="font-size: 1.3rem; font-weight: 700; color: #1f2937; margin-bottom: 5px;">
                                {{ $speech->author }}
                            </h3>
                            @if($speech->function)
                            <p style="color: #22c55e; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px;">
                                {{ $speech->function }}
                            </p>
                            @endif
                            <div style="display: flex; align-items: center; gap: 15px; color: #6b7280; font-size: 0.85rem;">
                                <span style="display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $speech->formatted_date }}
                                </span>
                                @if($speech->location)
                                <span style="display: flex; align-items: center; gap: 5px;">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $speech->location }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Speech Title -->
                    <h4 style="font-size: 1.2rem; font-weight: 700; color: #1f2937; margin-bottom: 15px; line-height: 1.4;">
                        {{ $speech->title }}
                    </h4>

                    <!-- Speech Excerpt -->
                    @if($speech->excerpt)
                    <blockquote style="font-style: italic; color: #374151; border-left: 4px solid #22c55e; padding-left: 20px; margin: 0 0 20px 0; font-size: 1rem; line-height: 1.6;">
                        "{{ $speech->excerpt }}"
                    </blockquote>
                    @endif

                    <!-- Speech Summary -->
                    <p style="color: #6b7280; line-height: 1.6; margin-bottom: 25px; font-size: 0.95rem;">
                        {{ Str::limit($speech->content, 200) }}
                    </p>

                    <!-- Speech Actions -->
                    <div style="display: flex; gap: 15px;">
                        <a href="{{ route('speech', $speech->id) }}" class="btn-primary" style="flex: 1; padding: 12px 20px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; text-decoration: none; border-radius: 10px; font-weight: 600; text-align: center; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-eye"></i>
                            Lire le discours complet
                        </a>
                        <button onclick="window.print()" class="btn-secondary" style="padding: 12px 20px; background: #f3f4f6; color: #374151; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <i class="fas fa-print"></i>
                            Imprimer
                        </button>
                    </div>
                </div>
            </article>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div style="text-align: center; padding: 80px 20px;">
            <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                <i class="fas fa-microphone-slash" style="font-size: 48px; color: #9ca3af;"></i>
            </div>
            <h3 style="font-size: 1.8rem; font-weight: 700; color: #1f2937; margin-bottom: 15px;">
                Aucun discours disponible
            </h3>
            <p style="color: #6b7280; font-size: 1.1rem; max-width: 500px; margin: 0 auto; line-height: 1.6;">
                Les discours officiels du CSAR seront publiés ici prochainement. Revenez régulièrement pour découvrir les messages de nos dirigeants.
            </p>
        </div>
        @endif
    </div>
</section>

<!-- Nos Dirigeants Section -->
<section class="leaders-section fade-in" style="background: #fff; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">
                Nos Dirigeants
            </h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto;">
                Les voix du CSAR
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 40px;">
            <!-- DG Card -->
            <div class="leader-card zoom-hover" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease;">
                <div style="height: 320px; overflow: hidden; position: relative;">
                    <img src="{{ asset('images/dg-mame-ndeye-sene.jpg') }}" alt="Madame Mame Ndeye SENE" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center 15%; transition: transform .6s ease;">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 40px 20px 20px;">
                        <h3 style="color: #fff; font-size: 1.4rem; font-weight: 700; margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Madame Mame Ndeye SENE</h3>
                        <p style="color: rgba(255,255,255,0.95); font-size: 1rem; font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">Directrice générale du CSAR</p>
                    </div>
                </div>

                <div style="padding: 30px;">
                    <p style="color: #6b7280; line-height: 1.6; margin-bottom: 25px; font-size: 0.95rem;">
                        Directrice générale du CSAR, Madame SENE assure la coordination générale des activités de sécurité alimentaire et de résilience au Sénégal, dans la continuité des missions de l'institution et au service des populations.
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 10px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #22c55e; margin-bottom: 5px;">135</div>
                            <div style="font-size: 0.8rem; color: #374151; font-weight: 600;">Agents</div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 10px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #22c55e; margin-bottom: 5px;">70</div>
                            <div style="font-size: 0.8rem; color: #374151; font-weight: 600;">Entrepôts</div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 10px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #22c55e; margin-bottom: 5px;">86K</div>
                            <div style="font-size: 0.8rem; color: #374151; font-weight: 600;">Tonnes</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ministre Card -->
            <div class="leader-card zoom-hover" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease;">
                <div style="height: 320px; overflow: hidden; position: relative;">
                    <img src="{{ asset('images/ministre-marie-angelique-diouf.jpg') }}" alt="Madame Marie Angélique Mame Selbé DIOUF" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; object-position: center 20%; transition: transform .6s ease;">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(transparent, rgba(0,0,0,0.85)); padding: 40px 20px 20px;">
                        <h3 style="color: #fff; font-size: 1.4rem; font-weight: 700; margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">Madame Marie Angélique Mame Selbé DIOUF</h3>
                        <p style="color: rgba(255,255,255,0.95); font-size: 1rem; font-weight: 500; text-shadow: 0 1px 2px rgba(0,0,0,0.3);">Ministre de la Famille, de l'Action sociale et des Solidarités</p>
                    </div>
                </div>

                <div style="padding: 30px;">
                    <p style="color: #6b7280; line-height: 1.6; margin-bottom: 25px; font-size: 0.95rem;">
                        Ministre de tutelle, Madame DIOUF supervise les politiques de solidarité nationale et de sécurité alimentaire. Elle accompagne le CSAR dans l'accomplissement de ses missions au service des populations vulnérables.
                    </p>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px;">
                        <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 10px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #22c55e; margin-bottom: 5px;">50</div>
                            <div style="font-size: 0.8rem; color: #374151; font-weight: 600;">Milliards FCFA</div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 10px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #22c55e; margin-bottom: 5px;">500K</div>
                            <div style="font-size: 0.8rem; color: #374151; font-weight: 600;">Bénéficiaires</div>
                        </div>
                        <div style="text-align: center; padding: 15px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 10px;">
                            <div style="font-size: 1.5rem; font-weight: 800; color: #22c55e; margin-bottom: 5px;">14</div>
                            <div style="font-size: 0.8rem; color: #374151; font-weight: 600;">Régions</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<!-- Contact Section -->
<section class="contact-section fade-in" style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align: center; max-width: 600px; margin: 0 auto;">
            <h2 style="font-size: 2.5rem; font-weight: 700; color: #fff; margin-bottom: 20px;">
                Besoin d'informations ?
            </h2>
            <p style="font-size: 1.2rem; color: rgba(255,255,255,0.8); margin-bottom: 40px; line-height: 1.6;">
                Pour toute question concernant les discours officiels ou pour demander des informations supplémentaires, n'hésitez pas à nous contacter.
            </p>

            <a href="{{ route('contact.simple') }}" class="btn-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 15px 30px; background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); color: #fff; text-decoration: none; border-radius: 10px; font-weight: 600; font-size: 1rem; transition: all 0.3s ease;">
                <i class="fas fa-envelope"></i>
                Nous contacter
            </a>
        </div>
    </div>
</section>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

.zoom-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(34,197,94,0.3);
}

.btn-secondary:hover {
    background: #e5e7eb !important;
    transform: translateY(-2px);
}

.fade-in {
    animation: fadeIn 1s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .main-title {
        font-size: 2.5rem !important;
    }

    .section-title {
        font-size: 2rem !important;
    }

    .speech-card {
        margin: 0 10px;
    }

    .leader-card {
        margin: 0 10px;
    }

    .overview-card {
        margin: 0 10px;
    }
}
</style>
@endsection

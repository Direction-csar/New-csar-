@extends('layouts.public')

@section('title', __('pages.rapports'))

@section('content')
<!-- Hero Section -->
<section class="hero-section fade-in" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); min-height: 40vh; padding: 50px 0; position: relative; overflow: hidden;">
    <!-- Floating decorative elements -->
    <div style="position: absolute; top: 20px; left: 10%; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; animation: float 6s ease-in-out infinite;"></div>
    <div style="position: absolute; top: 80px; right: 15%; width: 40px; height: 40px; background: rgba(255,255,255,0.08); border-radius: 50%; animation: float 8s ease-in-out infinite reverse;"></div>
    <div style="position: absolute; bottom: 40px; left: 20%; width: 50px; height: 50px; background: rgba(255,255,255,0.06); border-radius: 50%; animation: float 7s ease-in-out infinite;"></div>
    
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px; text-align: center; position: relative; z-index: 2;">
        <h1 class="main-title" style="font-size: 2.8rem; font-weight: 800; color: #fff; margin-bottom: 20px; text-shadow: 0 4px 8px rgba(0,0,0,0.3);">
            {{ __('messages.rapports.title') }}
        </h1>
        <p class="main-subtitle" style="font-size: 1.2rem; color: rgba(255,255,255,0.9); max-width: 800px; margin: 0 auto; line-height: 1.6; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
            {{ __('messages.rapports.subtitle') }}
        </p>
    </div>
</section>

<!-- Rapports Section -->
<section class="reports-section fade-in" style="background: #f8fafc; padding: 80px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
        <div style="text-align: center; margin-bottom: 60px;">
            <h2 class="section-title" style="font-size: 2.5rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">
                {{ __('messages.rapports.documents') }}
            </h2>
            <p class="section-subtitle" style="font-size: 1.2rem; color: #6b7280; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                {{ __('messages.rapports.documents_desc') }}
            </p>
        </div>

        @if(isset($reports) && $reports->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; margin-bottom: 60px;">
            @foreach($reports as $report)
            <div class="report-card zoom-hover" style="background: #fff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #f3f4f6; transition: all 0.3s ease;">
                <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%); padding: 30px; position: relative;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center;">
                            <div style="background: rgba(255,255,255,0.2); padding: 15px; border-radius: 15px; margin-right: 20px;">
                                <i class="fas fa-file-pdf" style="font-size: 2rem; color: #fff;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 1.4rem; font-weight: 700; color: #fff; margin-bottom: 5px;">
                                    {{ $report->title }}
                                </h3>
                                <p style="color: rgba(255,255,255,0.9); font-size: 0.9rem; font-weight: 500;">{{ __('messages.rapports.sim') }}</p>
                            </div>
                        </div>
                        @if($report->published_at && $report->published_at->diffInDays() <= 30)
                        <span style="background: rgba(255,255,255,0.2); color: #fff; font-size: 0.75rem; font-weight: 600; padding: 8px 16px; border-radius: 20px; backdrop-filter: blur(10px);">
                            {{ __('messages.rapports.new') }}
                        </span>
                        @endif
                    </div>
                    <p style="color: rgba(255,255,255,0.9); line-height: 1.6; margin-bottom: 0;">
                        {{ $report->description ?? 'Rapport officiel du CSAR disponible au téléchargement.' }}
                    </p>
                </div>
                
                <div style="padding: 30px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px;">
                        <div style="display: flex; align-items: center; color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-calendar-alt" style="margin-right: 8px; color: #22c55e;"></i>
                            <span>{{ $report->published_at ? $report->published_at->format('F Y') : 'Date non disponible' }}</span>
                        </div>
                        <div style="display: flex; align-items: center; color: #6b7280; font-size: 0.9rem;">
                            <i class="fas fa-eye" style="margin-right: 8px; color: #059669;"></i>
                            <span>{{ $report->view_count ?? 0 }} {{ __('messages.rapports.views') }}</span>
                        </div>
                    </div>
                    
                    @if($report->download_url)
                    <a href="{{ $report->download_url }}" 
                       target="_blank"
                       class="download-btn" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 15px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 1rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);">
                        <i class="fas fa-download"></i>
                        Télécharger PDF
                    </a>
                    @else
                    <div style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 15px; background: #f3f4f6; color: #6b7280; border-radius: 12px; font-weight: 600; font-size: 1rem;">
                        <i class="fas fa-lock"></i>
                        {{ __('messages.rapports.download_na') }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Message professionnel si aucun rapport -->
        <div style="text-align: center; padding: 80px 20px; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 60px;">
            <i class="fas fa-file-pdf" style="font-size: 4rem; color: #9ca3af; margin-bottom: 2rem;"></i>
            <h3 style="color: #6b7280; margin-bottom: 1rem; font-size: 1.5rem;">Aucun rapport disponible pour le moment</h3>
            <p style="color: #9ca3af; font-size: 1.1rem; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Les rapports officiels du CSAR seront publiés ici dès qu'ils seront disponibles. 
                Consultez régulièrement cette page pour accéder aux derniers documents.
            </p>
        </div>
        @endif

        <!-- Informations Section -->
        <div class="info-section fade-in" style="background: #fff; border-radius: 20px; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); margin-bottom: 60px;">
            <div style="text-align: center; margin-bottom: 50px;">
                <h3 style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 16px;">
                    {{ __('messages.rapports.info_title') }}
                </h3>
                <p style="color: #6b7280; max-width: 800px; margin: 0 auto; line-height: 1.6; font-size: 1.1rem;">
                    {{ __('messages.rapports.info_desc') }}
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                <div class="info-card zoom-hover" style="text-align: center; padding: 30px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 15px; border: 1px solid #bbf7d0;">
                    <div style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%); padding: 20px; border-radius: 15px; display: inline-block; margin-bottom: 20px; box-shadow: 0 8px 20px rgba(34, 197, 94, 0.3);">
                        <i class="fas fa-eye" style="font-size: 2rem; color: #fff;"></i>
                    </div>
                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 1.2rem;">Transparence</h4>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.6;">
                        Accès libre aux informations publiques et aux rapports officiels
                    </p>
                </div>

                <div class="info-card zoom-hover" style="text-align: center; padding: 30px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 15px; border: 1px solid #bfdbfe;">
                    <div style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); padding: 20px; border-radius: 15px; display: inline-block; margin-bottom: 20px; box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3);">
                        <i class="fas fa-shield-alt" style="font-size: 2rem; color: #fff;"></i>
                    </div>
                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 1.2rem;">{{ __('messages.rapports.reliability') }}</h4>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.6;">
                        {{ __('messages.rapports.reliability_desc') }}
                    </p>
                </div>

                <div class="info-card zoom-hover" style="text-align: center; padding: 30px; background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); border-radius: 15px; border: 1px solid #d8b4fe;">
                    <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 20px; border-radius: 15px; display: inline-block; margin-bottom: 20px; box-shadow: 0 8px 20px rgba(139, 92, 246, 0.3);">
                        <i class="fas fa-clock" style="font-size: 2rem; color: #fff;"></i>
                    </div>
                    <h4 style="font-weight: 700; color: #1f2937; margin-bottom: 12px; font-size: 1.2rem;">{{ __('messages.rapports.currency') }}</h4>
                    <p style="color: #6b7280; font-size: 0.95rem; line-height: 1.6;">
                        {{ __('messages.rapports.currency_desc') }}
                    </p>
                </div>
            </div>
        </div>


    </div>
</section>

<style>
@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes zoomHover {
    0% { transform: scale(1); }
    100% { transform: scale(1.02); }
}

.fade-in {
    animation: fadeIn 0.8s ease-out;
}

.zoom-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.download-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.info-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(34, 197, 94, 0.4);
}

.btn-secondary:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .main-title { font-size: 2.2rem !important; }
    .section-title { font-size: 2rem !important; }
    .container { padding: 0 15px !important; }
    .report-card { margin-bottom: 20px; }
}
</style>
@endsection 
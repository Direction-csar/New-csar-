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
                @if($report->cover_image)
                <div style="height: 200px; background-image: url('{{ $report->cover_image }}'); background-size: cover; background-position: center; position: relative;">
                    <div style="position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.1), rgba(5,150,105,0.7));"></div>
                </div>
                @endif
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
                    
                    @if($report->document_file)
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button type="button"
                                onclick="openReportPreview({{ $loop->index }})"
                                class="preview-btn"
                                style="flex: 1; min-width: 140px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; background: #fff; border: 2px solid #059669; color: #059669; cursor: pointer; border-radius: 12px; font-weight: 600; font-size: 0.95rem; transition: all 0.3s ease;">
                            <i class="fas fa-eye"></i>
                            Aperçu
                        </button>
                        <a href="{{ route('sim.download', $report->id) }}"
                           class="download-btn" style="flex: 1; min-width: 140px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 13px; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; text-decoration: none; border-radius: 12px; font-weight: 600; font-size: 0.95rem; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);">
                            <i class="fas fa-download"></i>
                            Télécharger PDF
                        </a>
                    </div>
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

@if(isset($reports) && $reports->count() > 0)
<!-- ============ MODAL APERÇU PDF ============ -->
<div id="reportPreviewModal" class="report-modal" role="dialog" aria-modal="true" aria-hidden="true">
    <div class="report-modal__backdrop" onclick="closeReportPreview()"></div>

    <button type="button" class="report-modal__close" onclick="closeReportPreview()" aria-label="Fermer">
        <i class="fas fa-times"></i>
    </button>

    <button type="button" class="report-modal__nav report-modal__nav--prev" onclick="navigateReport(-1)" aria-label="Précédent">
        <i class="fas fa-chevron-left"></i>
    </button>

    <button type="button" class="report-modal__nav report-modal__nav--next" onclick="navigateReport(1)" aria-label="Suivant">
        <i class="fas fa-chevron-right"></i>
    </button>

    <div class="report-modal__content">
        <div class="report-modal__header">
            <div class="report-modal__icon">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div class="report-modal__title-wrap">
                <h3 id="modalReportTitle" class="report-modal__title"></h3>
                <p id="modalReportMeta" class="report-modal__meta"></p>
            </div>
            <div class="report-modal__counter">
                <span id="modalReportIndex">1</span> / <span id="modalReportTotal">{{ $reports->count() }}</span>
            </div>
        </div>

        <div class="report-modal__viewer">
            <div id="modalPdfLoader" class="report-modal__loader">
                <div class="report-modal__spinner"></div>
                <p>Chargement du document...</p>
            </div>
            <iframe id="modalPdfFrame" src="" frameborder="0" onload="document.getElementById('modalPdfLoader').style.display='none'"></iframe>
        </div>

        <div class="report-modal__footer">
            <a id="modalDownloadBtn" href="#" class="report-modal__download">
                <i class="fas fa-download"></i> Télécharger le PDF
            </a>
            <div class="report-modal__dots" id="modalDots"></div>
        </div>
    </div>
</div>

@php
    $reportsForJs = $reports->map(function ($r) {
        return [
            'id' => $r->id,
            'title' => $r->title,
            'description' => $r->description,
            'view_url' => route('sim.view', $r->id),
            'download_url' => route('sim.download', $r->id),
            'published_at' => $r->published_at ? $r->published_at->format('F Y') : null,
            'view_count' => $r->view_count ?? 0,
        ];
    })->values();
@endphp
<script>
const reportsData = {!! $reportsForJs->toJson() !!};

let currentReportIdx = 0;

function openReportPreview(index) {
    currentReportIdx = index;
    renderReportPreview();
    const modal = document.getElementById('reportPreviewModal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeReportPreview() {
    const modal = document.getElementById('reportPreviewModal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    document.getElementById('modalPdfFrame').src = '';
}

function navigateReport(delta) {
    const total = reportsData.length;
    currentReportIdx = (currentReportIdx + delta + total) % total;
    renderReportPreview();
}

function renderReportPreview() {
    const r = reportsData[currentReportIdx];
    if (!r) return;

    const viewer = document.querySelector('.report-modal__viewer');
    viewer.classList.remove('is-animating');
    void viewer.offsetWidth;
    viewer.classList.add('is-animating');

    document.getElementById('modalReportTitle').textContent = r.title;
    document.getElementById('modalReportMeta').textContent =
        (r.published_at ? r.published_at + ' • ' : '') + r.view_count + ' vues';
    document.getElementById('modalReportIndex').textContent = currentReportIdx + 1;
    document.getElementById('modalDownloadBtn').href = r.download_url;
    document.getElementById('modalPdfLoader').style.display = 'flex';
    document.getElementById('modalPdfFrame').src = r.view_url;

    const dots = document.getElementById('modalDots');
    dots.innerHTML = reportsData.map((_, i) =>
        `<button type="button" class="report-modal__dot${i === currentReportIdx ? ' is-active' : ''}" onclick="currentReportIdx=${i};renderReportPreview()" aria-label="Rapport ${i+1}"></button>`
    ).join('');
}

document.addEventListener('keydown', function(e) {
    if (!document.getElementById('reportPreviewModal').classList.contains('is-open')) return;
    if (e.key === 'Escape') closeReportPreview();
    else if (e.key === 'ArrowLeft') navigateReport(-1);
    else if (e.key === 'ArrowRight') navigateReport(1);
});
</script>
@endif

<style>
/* ============ MODAL APERÇU PDF ============ */
.report-modal {
    position: fixed; inset: 0; z-index: 9999;
    display: none; align-items: center; justify-content: center;
    padding: 20px;
}
.report-modal.is-open { display: flex; animation: modalFadeIn 0.35s ease-out; }

.report-modal__backdrop {
    position: absolute; inset: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    cursor: pointer;
}

.report-modal__close {
    position: absolute; top: 20px; right: 20px; z-index: 10;
    width: 48px; height: 48px; border-radius: 50%;
    background: rgba(255,255,255,0.95); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #1f2937; font-size: 1.2rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.report-modal__close:hover { background: #ef4444; color: #fff; transform: rotate(90deg) scale(1.1); }

.report-modal__nav {
    position: absolute; top: 50%; transform: translateY(-50%); z-index: 10;
    width: 54px; height: 54px; border-radius: 50%;
    background: rgba(255,255,255,0.95); border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #059669; font-size: 1.3rem;
    transition: all 0.3s ease;
    box-shadow: 0 4px 20px rgba(0,0,0,0.25);
}
.report-modal__nav:hover {
    background: #059669; color: #fff;
    transform: translateY(-50%) scale(1.15);
    box-shadow: 0 8px 25px rgba(5, 150, 105, 0.5);
}
.report-modal__nav--prev { left: 30px; }
.report-modal__nav--next { right: 30px; }

.report-modal__content {
    position: relative; z-index: 5;
    width: 100%; max-width: 1100px; max-height: 92vh;
    background: #fff; border-radius: 24px; overflow: hidden;
    display: flex; flex-direction: column;
    box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    animation: modalSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

.report-modal__header {
    display: flex; align-items: center; gap: 16px;
    padding: 20px 28px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #fff;
}
.report-modal__icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; flex-shrink: 0;
}
.report-modal__title-wrap { flex: 1; min-width: 0; }
.report-modal__title {
    font-size: 1.15rem; font-weight: 700; margin: 0 0 4px 0;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.report-modal__meta { font-size: 0.85rem; opacity: 0.9; margin: 0; }
.report-modal__counter {
    background: rgba(255,255,255,0.2);
    padding: 8px 16px; border-radius: 20px;
    font-weight: 700; font-size: 0.9rem; flex-shrink: 0;
}

.report-modal__viewer {
    flex: 1; position: relative;
    background: #f3f4f6;
    min-height: 60vh;
}
.report-modal__viewer.is-animating { animation: pdfFade 0.45s ease-out; }
.report-modal__viewer iframe { width: 100%; height: 100%; min-height: 60vh; border: 0; display: block; }
.report-modal__loader {
    position: absolute; inset: 0;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 16px; color: #6b7280; background: #f3f4f6; z-index: 2;
}
.report-modal__spinner {
    width: 48px; height: 48px; border-radius: 50%;
    border: 4px solid #d1d5db; border-top-color: #059669;
    animation: spin 0.8s linear infinite;
}

.report-modal__footer {
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    padding: 18px 28px; background: #fff; border-top: 1px solid #f3f4f6;
}
.report-modal__download {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 24px; border-radius: 12px;
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #fff; text-decoration: none; font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(5,150,105,0.3);
}
.report-modal__download:hover {
    color: #fff; transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(5,150,105,0.5);
}
.report-modal__dots { display: flex; gap: 8px; }
.report-modal__dot {
    width: 10px; height: 10px; border-radius: 50%;
    border: none; background: #d1d5db; cursor: pointer;
    transition: all 0.3s ease;
}
.report-modal__dot:hover { background: #9ca3af; }
.report-modal__dot.is-active {
    background: #059669; width: 28px; border-radius: 5px;
}

@keyframes modalFadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(40px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}
@keyframes pdfFade {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes spin { to { transform: rotate(360deg); } }

.preview-btn:hover {
    background: #059669 !important; color: #fff !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(5,150,105,0.35);
}

@media (max-width: 768px) {
    .report-modal { padding: 0; }
    .report-modal__content { max-height: 100vh; height: 100vh; border-radius: 0; }
    .report-modal__nav { width: 44px; height: 44px; }
    .report-modal__nav--prev { left: 10px; }
    .report-modal__nav--next { right: 10px; }
    .report-modal__title { font-size: 1rem; }
    .report-modal__counter { padding: 6px 12px; font-size: 0.8rem; }
    .report-modal__footer { flex-direction: column; gap: 12px; padding: 14px; }
}
</style>

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
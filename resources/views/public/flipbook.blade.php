@extends('layouts.public')

@section('title', $report->title . ' - Flipbook')

@section('content')
<style>
.flipbook-wrapper {
    background: #1a1a2e;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px 0;
}

.flipbook-header {
    width: 100%;
    max-width: 1200px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 30px;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    margin-bottom: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.1);
}

.flipbook-header__info h1 {
    color: #fff;
    font-size: 1.3rem;
    font-weight: 700;
    margin: 0 0 5px 0;
}

.flipbook-header__info p {
    color: rgba(255,255,255,0.6);
    font-size: 0.9rem;
    margin: 0;
}

.flipbook-header__actions {
    display: flex;
    gap: 10px;
}

.flipbook-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    text-decoration: none;
}

.flipbook-btn--primary {
    background: linear-gradient(135deg, #059669, #047857);
    color: #fff;
}

.flipbook-btn--secondary {
    background: rgba(255,255,255,0.1);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}

.flipbook-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(5,150,105,0.4);
}

.flipbook-container {
    position: relative;
    width: 100%;
    max-width: 1200px;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
}

#flipbook-render {
    box-shadow: 0 25px 80px rgba(0,0,0,0.6);
    border-radius: 4px;
}

.flipbook-loader {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 20px;
    z-index: 10;
}

.flipbook-loader__spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255,255,255,0.1);
    border-top-color: #059669;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.flipbook-loader p {
    color: rgba(255,255,255,0.7);
    font-size: 1rem;
}

.flipbook-progress {
    width: 300px;
    height: 4px;
    background: rgba(255,255,255,0.1);
    border-radius: 2px;
    overflow: hidden;
}

.flipbook-progress__bar {
    height: 100%;
    background: linear-gradient(90deg, #059669, #047857);
    width: 0%;
    transition: width 0.3s ease;
}

.flipbook-controls {
    width: 100%;
    max-width: 1200px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    padding: 20px 30px;
    margin-top: 20px;
    background: rgba(255,255,255,0.05);
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.1);
}

.flipbook-controls__btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.flipbook-controls__btn:hover {
    background: #059669;
    border-color: #059669;
    transform: scale(1.1);
}

.flipbook-controls__page-info {
    color: rgba(255,255,255,0.7);
    font-size: 0.95rem;
    font-weight: 500;
    min-width: 100px;
    text-align: center;
}

.flipbook-controls__zoom {
    display: flex;
    align-items: center;
    gap: 8px;
}

.flipbook-controls__zoom input {
    width: 120px;
    accent-color: #059669;
}

/* StPageFlip styling override */
.stf__wrapper {
    perspective: 2000px;
}

.stf__block {
    border-radius: 4px;
}

.stf__item {
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

.stf__item img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #fff;
}

/* Cover styling */
.flipbook-cover {
    background: linear-gradient(135deg, #059669, #047857);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    padding: 40px;
    text-align: center;
}

.flipbook-cover h2 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 15px;
}

.flipbook-cover p {
    font-size: 1rem;
    opacity: 0.9;
}

/* Responsive */
@media (max-width: 768px) {
    .flipbook-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    .flipbook-header h1 {
        font-size: 1.1rem;
    }
    .flipbook-controls {
        flex-wrap: wrap;
        gap: 10px;
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Fullscreen */
.flipbook-wrapper:fullscreen {
    padding: 0;
    justify-content: center;
}
.flipbook-wrapper:fullscreen .flipbook-container {
    min-height: 90vh;
}
</style>

<div class="flipbook-wrapper" id="flipbookWrapper">
    <!-- Header -->
    <div class="flipbook-header">
        <div class="flipbook-header__info">
            <h1>{{ $report->title }}</h1>
            <p>
                {{ $report->categoryLabel }}
                @if($report->published_at)
                    &bull; {{ $report->published_at->format('d/m/Y') }}
                @endif
                @if($report->view_count)
                    &bull; {{ $report->view_count }} vues
                @endif
            </p>
        </div>
        <div class="flipbook-header__actions">
            <button class="flipbook-btn flipbook-btn--secondary" onclick="toggleFullscreen()" title="Plein écran">
                <i class="fas fa-expand"></i>
                <span class="d-none d-md-inline">Plein écran</span>
            </button>
            @if($report->public_download_url)
            <a href="{{ $report->public_download_url }}" class="flipbook-btn flipbook-btn--primary" target="_blank">
                <i class="fas fa-download"></i>
                <span class="d-none d-md-inline">Télécharger PDF</span>
            </a>
            @endif
            <a href="{{ url()->previous() }}" class="flipbook-btn flipbook-btn--secondary">
                <i class="fas fa-arrow-left"></i>
                <span class="d-none d-md-inline">Retour</span>
            </a>
        </div>
    </div>

    <!-- Flipbook Container -->
    <div class="flipbook-container">
        <div class="flipbook-loader" id="flipbookLoader">
            <div class="flipbook-loader__spinner"></div>
            <p>Chargement du document...</p>
            <div class="flipbook-progress">
                <div class="flipbook-progress__bar" id="flipbookProgress"></div>
            </div>
            <p id="flipbookProgressText" style="color:rgba(255,255,255,0.5);font-size:0.85rem;margin-top:5px;">0%</p>
        </div>
        <div id="flipbook-render"></div>
    </div>

    <!-- Controls -->
    <div class="flipbook-controls">
        <button class="flipbook-controls__btn" onclick="flipbook.prev()" title="Page précédente">
            <i class="fas fa-chevron-left"></i>
        </button>
        <span class="flipbook-controls__page-info" id="pageInfo">Page 1 / 1</span>
        <button class="flipbook-controls__btn" onclick="flipbook.next()" title="Page suivante">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="flipbook-controls__zoom">
            <button class="flipbook-controls__btn" onclick="zoomOut()" title="Zoom arrière">
                <i class="fas fa-minus"></i>
            </button>
            <span style="color:rgba(255,255,255,0.6);font-size:0.85rem;min-width:50px;text-align:center;" id="zoomLevel">100%</span>
            <button class="flipbook-controls__btn" onclick="zoomIn()" title="Zoom avant">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/page-flip@latest/dist/js/page-flip.browser.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let flipbook = null;
let currentZoom = 1;
const zoomStep = 0.1;
const minZoom = 0.5;
const maxZoom = 3;

const pdfUrl = '{{ $pdfUrl }}';
const renderEl = document.getElementById('flipbook-render');
const loader = document.getElementById('flipbookLoader');
const progressBar = document.getElementById('flipbookProgress');
const progressText = document.getElementById('flipbookProgressText');
const pageInfo = document.getElementById('pageInfo');
const zoomLevel = document.getElementById('zoomLevel');

async function loadPdfAndCreateFlipbook() {
    if (!pdfUrl) {
        loader.innerHTML = '<p style="color:#ef4444;">PDF non disponible</p>';
        return;
    }

    try {
        const pdf = await pdfjsLib.getDocument(pdfUrl).promise;
        const totalPages = pdf.numPages;
        const pageImages = [];

        for (let i = 1; i <= totalPages; i++) {
            const page = await pdf.getPage(i);
            const scale = 2;
            const viewport = page.getViewport({ scale });
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({ canvasContext: ctx, viewport }).promise;

            const img = document.createElement('img');
            img.src = canvas.toDataURL('image/jpeg', 0.9);
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'contain';
            img.style.background = '#fff';

            pageImages.push(img);

            const progress = Math.round((i / totalPages) * 100);
            progressBar.style.width = progress + '%';
            progressText.textContent = progress + '%';
        }

        loader.style.display = 'none';

        const pageWidth = pageImages[0].naturalWidth || 800;
        const pageHeight = pageImages[0].naturalHeight || 1100;
        const aspectRatio = pageHeight / pageWidth;

        const containerWidth = Math.min(window.innerWidth - 40, 1200);
        const calculatedWidth = containerWidth / 2;
        const calculatedHeight = calculatedWidth * aspectRatio;

        flipbook = new St.PageFlip(renderEl, {
            width: calculatedWidth,
            height: calculatedHeight,
            size: 'stretch',
            minWidth: 300,
            maxWidth: 800,
            minHeight: 400,
            maxHeight: 1200,
            maxShadowOpacity: 0.5,
            showCover: true,
            mobileScrollSupport: false,
            usePortrait: true,
            drawShadow: true,
            flippingTime: 800,
            startPage: 0,
            autoSize: true,
        });

        flipbook.loadFromHTML(pageImages);

        flipbook.on('flip', (e) => {
            const current = e.data + 1;
            const total = flipbook.getPageCount();
            pageInfo.textContent = `Page ${current} / ${total}`;
        });

        pageInfo.textContent = `Page 1 / ${totalPages}`;

    } catch (err) {
        console.error(err);
        loader.innerHTML = '<p style="color:#ef4444;">Erreur lors du chargement du PDF</p>';
    }
}

function nextPage() {
    if (flipbook) flipbook.flipNext('top');
}

function prevPage() {
    if (flipbook) flipbook.flipPrev('top');
}

function zoomIn() {
    if (currentZoom < maxZoom) {
        currentZoom += zoomStep;
        applyZoom();
    }
}

function zoomOut() {
    if (currentZoom > minZoom) {
        currentZoom -= zoomStep;
        applyZoom();
    }
}

function applyZoom() {
    if (flipbook) {
        const wrapper = document.querySelector('.stf__wrapper');
        if (wrapper) {
            wrapper.style.transform = `scale(${currentZoom})`;
            wrapper.style.transformOrigin = 'center center';
        }
    }
    zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
}

function toggleFullscreen() {
    const el = document.getElementById('flipbookWrapper');
    if (!document.fullscreenElement) {
        el.requestFullscreen().catch(err => console.log(err));
    } else {
        document.exitFullscreen();
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') nextPage();
    if (e.key === 'ArrowLeft') prevPage();
    if (e.key === 'Escape' && document.fullscreenElement) document.exitFullscreen();
});

window.addEventListener('resize', () => {
    if (flipbook) {
        flipbook.update();
    }
});

loadPdfAndCreateFlipbook();
</script>
@endpush

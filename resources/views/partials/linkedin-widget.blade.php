{{-- LinkedIn Section Widget --}}
@php
$liPosts = app(\App\Http\Controllers\LinkedInController::class)->getCachedPosts();
$hasRealPosts = !empty($liPosts);
if (!$hasRealPosts) {
    $liPosts = [
        ['date'=>'15 mai 2024','new'=>true, 'title'=>'Renforcement des capacités',  'desc'=>'Atelier de formation des producteurs sur les bonnes pratiques agricoles.',         'img'=>'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=400&q=80','url'=>config('services.linkedin.company_url')],
        ['date'=>'10 mai 2024','new'=>false,'title'=>"Projet d'irrigation durable",  'desc'=>"Installation de nouveaux systèmes d'irrigation dans la région de Thiès.",        'img'=>'https://images.unsplash.com/photo-1416879595882-3373a0480b5b?w=400&q=80','url'=>config('services.linkedin.company_url')],
        ['date'=>'5 mai 2024', 'new'=>false,'title'=>'Partenariat stratégique',      'desc'=>'Rencontre avec nos partenaires pour renforcer la sécurité alimentaire.',         'img'=>'https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?w=400&q=80','url'=>config('services.linkedin.company_url')],
        ['date'=>'30 avr 2024','new'=>false,'title'=>'Soutien aux communautés',      'desc'=>'Distribution de semences améliorées aux agriculteurs locaux.',                   'img'=>'https://images.unsplash.com/photo-1464226184884-fa280b87c399?w=400&q=80','url'=>config('services.linkedin.company_url')],
        ['date'=>'25 avr 2024','new'=>false,'title'=>'Résilience climatique',        'desc'=>"Nos actions pour une agriculture résiliente face aux changements climatiques.",  'img'=>'https://images.unsplash.com/photo-1520637836862-4d197d17c38a?w=400&q=80','url'=>config('services.linkedin.company_url')],
    ];
}
@endphp

<section class="li-section">

    {{-- Badge --}}
    <div class="li-badge">
        <svg class="li-badge-icon" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="5" fill="#0A66C2"/>
            <path d="M6.94 5a1.94 1.94 0 1 1-3.88 0 1.94 1.94 0 0 1 3.88 0zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91l.04-1.68z" fill="white"/>
        </svg>
        SUIVEZ-NOUS SUR LINKEDIN
    </div>

    {{-- Title --}}
    <h2 class="li-title">Restez Connecté à Notre Impact</h2>
    <p class="li-subtitle">
        Découvrez nos dernières actualités, projets et initiatives pour la sécurité alimentaire<br>
        et la résilience au Sénégal
    </p>

    {{-- Posts card --}}
    <div class="li-card">
        <div class="li-card-header">
            <div class="li-card-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <rect width="24" height="24" rx="5" fill="#0A66C2"/>
                    <path d="M6.94 5a1.94 1.94 0 1 1-3.88 0 1.94 1.94 0 0 1 3.88 0zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91l.04-1.68z" fill="white"/>
                </svg>
                Nos dernières actualités sur LinkedIn
            </div>
            <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/posts/?feedView=all"
               target="_blank" rel="noopener" class="li-see-all">
                Voir toutes nos actualités →
            </a>
        </div>

        <div class="li-track-wrap">
            <button class="li-arrow li-arrow--left" onclick="liScroll(-1)" aria-label="Précédent">&#8249;</button>
            <div class="li-track" id="liTrack">
                @foreach($liPosts as $i => $post)
                <a href="{{ $post['url'] ?? config('services.linkedin.company_url') }}" target="_blank" rel="noopener" class="li-post">
                    <div class="li-post-img-wrap">
                        @if(!empty($post['img']))
                            <img src="{{ $post['img'] }}" alt="{{ $post['title'] }}" class="li-post-img" loading="lazy">
                        @else
                            <div class="li-post-img-placeholder"></div>
                        @endif
                        @if($i === 0)
                            <span class="li-new">NOUVEAU</span>
                        @endif
                    </div>
                    <div class="li-post-body">
                        <span class="li-post-date">{{ $post['date'] }}</span>
                        <h4 class="li-post-title">{{ $post['title'] }}</h4>
                        <p class="li-post-desc">{{ $post['desc'] }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            <button class="li-arrow li-arrow--right" onclick="liScroll(1)" aria-label="Suivant">&#8250;</button>
        </div>
    </div>

    {{-- CTA --}}
    <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/posts/?feedView=all"
       target="_blank" rel="noopener" class="li-cta">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <rect width="24" height="24" rx="5" fill="#0A66C2"/>
            <path d="M6.94 5a1.94 1.94 0 1 1-3.88 0 1.94 1.94 0 0 1 3.88 0zM7 8.48H3V21h4V8.48zm6.32 0H9.34V21h3.94v-6.57c0-3.66 4.77-4 4.77 0V21H22v-7.93c0-6.17-7.06-5.94-8.72-2.91l.04-1.68z" fill="white"/>
        </svg>
        Suivre le CSAR sur LinkedIn &nbsp;→
    </a>

</section>

<style>
.li-section {
    position: relative;
    background: linear-gradient(135deg, #0072b1 0%, #00a0dc 60%, #0085cc 100%);
    padding: 64px 20px 56px;
    text-align: center;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.li-section::before,
.li-section::after {
    content: '';
    position: absolute;
    border-radius: 50%;
    opacity: 0.08;
    background: white;
    pointer-events: none;
}
.li-section::before { width: 400px; height: 400px; top: -120px; left: -80px; }
.li-section::after  { width: 300px; height: 300px; bottom: -80px; right: -60px; }

.li-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(6px);
    border: 1.5px solid rgba(255,255,255,0.35);
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1.5px;
    padding: 8px 20px;
    border-radius: 100px;
    margin-bottom: 24px;
}
.li-badge-icon { width: 20px; height: 20px; flex-shrink: 0; }

.li-title {
    color: #fff;
    font-size: clamp(24px, 4vw, 38px);
    font-weight: 800;
    margin: 0 0 14px;
    line-height: 1.2;
}

.li-subtitle {
    color: rgba(255,255,255,0.85);
    font-size: 15px;
    line-height: 1.6;
    margin: 0 auto 36px;
    max-width: 600px;
}

/* Card */
.li-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.18);
    max-width: 1000px;
    margin: 0 auto 40px;
    padding: 20px 0 24px;
    overflow: hidden;
}

.li-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 24px 16px;
    border-bottom: 1px solid #f0f0f0;
}

.li-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #1a1a2e;
}

.li-see-all {
    font-size: 13px;
    font-weight: 600;
    color: #0A66C2;
    text-decoration: none;
    white-space: nowrap;
    transition: opacity 0.2s;
}
.li-see-all:hover { opacity: 0.75; text-decoration: none; }

/* Track */
.li-track-wrap {
    position: relative;
    display: flex;
    align-items: center;
    padding: 20px 8px 4px;
}

.li-track {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    padding: 0 12px;
    flex: 1;
}
.li-track::-webkit-scrollbar { display: none; }

.li-post {
    flex: 0 0 175px;
    background: #fafafa;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #eee;
    transition: transform 0.2s, box-shadow 0.2s;
    text-decoration: none;
    color: inherit;
    display: block;
}

.li-post-img-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0072b1, #00a0dc);
}
.li-post:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.li-post-img-wrap {
    position: relative;
    height: 110px;
    overflow: hidden;
}
.li-post-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s;
}
.li-post:hover .li-post-img { transform: scale(1.05); }

.li-new {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #00c875;
    color: #fff;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.8px;
    padding: 3px 8px;
    border-radius: 100px;
}

.li-post-body {
    padding: 10px 12px 12px;
}
.li-post-date {
    font-size: 11px;
    color: #999;
    display: block;
    margin-bottom: 4px;
}
.li-post-title {
    font-size: 13px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0 0 5px;
    line-height: 1.3;
}
.li-post-desc {
    font-size: 12px;
    color: #666;
    line-height: 1.45;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    line-clamp: 3;
    overflow: hidden;
}

/* Arrows */
.li-arrow {
    background: rgba(255,255,255,0.9);
    border: 1.5px solid #e0e0e0;
    color: #444;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 22px;
    line-height: 1;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 2;
}
.li-arrow:hover { background: #0A66C2; color: #fff; border-color: #0A66C2; }
.li-arrow--left  { margin-right: 4px; }
.li-arrow--right { margin-left: 4px; }

/* CTA */
.li-cta {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    color: #1a1a2e;
    font-size: 15px;
    font-weight: 700;
    padding: 16px 36px;
    border-radius: 100px;
    text-decoration: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    transition: transform 0.2s, box-shadow 0.2s;
}
.li-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    text-decoration: none;
    color: #0A66C2;
}

/* Responsive */
@media (max-width: 640px) {
    .li-card-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .li-post { flex: 0 0 150px; }
    .li-cta { font-size: 13px; padding: 14px 24px; }
}
</style>

<script>
function liScroll(dir) {
    const track = document.getElementById('liTrack');
    track.scrollBy({ left: dir * 200, behavior: 'smooth' });
}
</script>

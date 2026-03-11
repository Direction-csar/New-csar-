<footer class="footer-responsive">
    <div class="footer-content">
        <div class="footer-grid">
            <!-- Section CSAR -->
            <div class="footer-section">
                <div class="footer-logo-section">
                    <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" class="footer-logo">
                    <div class="footer-logo-text">CSAR</div>
                </div>
                <div id="footer-typewriter" data-text="Commissariat à la Sécurité Alimentaire et à la Résilience" data-speed="60" class="footer-description"></div>
                
                <!-- Section Newsletter -->
                <div class="newsletter-section">
                    <div class="newsletter-title">Newsletter</div>
                    <div class="newsletter-underline"></div>
                    <form action="{{ route('newsletter.subscribe') }}" method="POST" class="newsletter-form">
                        @csrf
                        <input 
                            type="email" 
                            name="email" 
                            placeholder="Votre adresse email" 
                            required
                            class="newsletter-input"
                        >
                        <button type="submit" class="newsletter-button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
                
                <div class="footer-social">
                    <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=61562947586356&mibextid=wwXIfr&rdid=rdi0HoJAMnm5SUWB&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1A15LpvcqT%2F%3Fmibextid%3DwwXIfr" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://x.com/csar_sn?s=21" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://www.instagram.com/csar.sn?igsh=MWcxbTJnNzBnZGo5Mg%3D%3D&utm_source=qr" target="_blank" rel="noopener" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            
            <!-- Liens rapides -->
            <div class="footer-section">
                <h3 class="footer-section-title">Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="/" class="footer-link">Accueil</a></li>
                    <li><a href="/about" class="footer-link">À propos</a></li>
                    <li><a href="/institution" class="footer-link">Institution</a></li>
                    <li><a href="/actualites" class="footer-link">Actualités</a></li>
                    <li><a href="{{ route('sim.index') }}" class="footer-link">SIM</a></li>
                    <li><a href="{{ route('gallery') }}" class="footer-link">Nos missions</a></li>
                </ul>
            </div>
            
            <!-- Partenaires institutionnels -->
            <div class="footer-section">
                <h3 class="footer-section-title">Partenaires institutionnels</h3>
                <ul class="footer-links">
                    <li>
                        <a href="https://primature.sn/" target="_blank" rel="noopener nofollow" title="Primature du Sénégal" class="footer-link footer-link-with-icon">
                            <img src="{{ asset('images/primature.jpg') }}" alt="Primature" class="footer-link-icon">
                            <span>Primature</span>
                            <span class="footer-link-external">↗</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://femme.gouv.sn/" target="_blank" rel="noopener nofollow" title="Ministère de la famille de l'action sociale et des solidarités" class="footer-link footer-link-with-icon">
                            <img src="{{ asset('images/mfs.png') }}" alt="Ministère de la famille de l'action sociale et des solidarités" class="footer-link-icon">
                            <span>Ministère de la famille de l'action sociale et des solidarités</span>
                            <span class="footer-link-external">↗</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://www.presidence.sn/" target="_blank" rel="noopener nofollow" title="Présidence de la République" class="footer-link footer-link-with-icon">
                            <img src="{{ asset('images/presidence.png') }}" alt="Présidence de la République" class="footer-link-icon">
                            <span>Présidence de la République</span>
                            <span class="footer-link-external">↗</span>
                        </a>
                    </li>
                    <li class="footer-link-separator">
                        <a href="{{ route('partners.index') }}" title="Nos partenaires" class="footer-link footer-link-with-icon">
                            <i class="fas fa-handshake"></i>
                            <span>Nos partenaires</span>
                        </a>
                    </li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="footer-section">
                <h3 class="footer-section-title">Contact</h3>
                <ul class="footer-links">
                    <li><a href="{{ route('demande.selection') }}" class="footer-link">Effectuer une demande</a></li>
                    <li><a href="{{ route('contact.simple') }}" class="footer-link">Nous contacter</a></li>
                    <li class="footer-link-separator">
                        <a href="/about#dg-speech" class="footer-link footer-link-with-icon">
                            <i class="fas fa-file-alt" style="color: #059669;"></i>
                            <span>Mot introductif</span>
                        </a>
                    </li>
                    <li class="footer-contact-item">
                        <i class="fas fa-envelope"></i>
                        <span>contact@csar.sn</span>
                    </li>
                    <li class="footer-contact-item">
                        <i class="fas fa-phone"></i>
                        <span>+221 33 123 45 67</span>
                    </li>
                    <li class="footer-contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Dakar, Sénégal</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>© 2025 CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience. Tous droits réservés.</p>
    </div>

    <script>
    (function(){
        var el = document.getElementById('footer-typewriter');
        if(!el) return;
        var text = el.getAttribute('data-text') || '';
        var speed = Number(el.getAttribute('data-speed') || 70);
        var caret = document.createElement('span');
        caret.textContent = '▍';
        caret.style.marginLeft = '6px';
        caret.style.animation = 'blink 1s steps(1,end) infinite';
        el.appendChild(caret);
        
        if(!document.getElementById('ft-blink-style')){
            var s = document.createElement('style');
            s.id = 'ft-blink-style';
            s.textContent = '@keyframes blink{0%{opacity:1}50%{opacity:0}100%{opacity:1}}';
            document.head.appendChild(s);
        }
        
        function typeText(){
            var i = 0;
            // Effacer le texte existant (sauf le caret)
            while(el.firstChild && el.firstChild !== caret){
                el.removeChild(el.firstChild);
            }
            
            function step(){
                if(i < text.length){
                    el.insertBefore(document.createTextNode(text.charAt(i)), caret);
                    i++;
                    setTimeout(step, speed);
                } else {
                    // Attendre 3 secondes puis recommencer
                    setTimeout(function(){
                        typeText();
                    }, 3000);
                }
            }
            step();
        }
        
        var io = new IntersectionObserver(function(entries){
            if(entries[0].isIntersecting){
                typeText();
                io.disconnect();
            }
        }, {threshold: 0.1});
        io.observe(el);
    })();
    </script>
</footer>

<style>
/* Footer responsive styles */
.footer-responsive {
    background: linear-gradient(135deg, #1e3a8a 0%, #22c55e 50%, #1e3a8a 100%);
    color: white;
    margin-top: auto;
    width: 100vw;
    min-width: 100vw;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
}

.footer-content {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Footer grid */
.footer-grid {
    display: grid;
    gap: 2rem;
    grid-template-columns: 1fr;
    padding: 2rem 0 1rem;
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
}

/* Footer sections */
.footer-section {
    padding: 0;
    width: 100%;
}

.footer-logo-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.footer-logo {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.25));
}

.footer-logo-text {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: 0.3px;
}

.footer-description {
    min-height: 46px;
    font-size: 0.875rem;
    line-height: 1.6;
    color: rgba(255, 255, 255, 0.95);
    margin-bottom: 1rem;
}

/* Newsletter Section */
.newsletter-section {
    margin: 1.25rem 0 1rem 0;
}

.newsletter-title {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 0.5rem;
    letter-spacing: 0.3px;
    color: #fff;
}

.newsletter-underline {
    width: 100%;
    height: 2px;
    background: linear-gradient(90deg, #00d4aa, #00b894);
    margin-bottom: 0.75rem;
    border-radius: 1px;
}

.newsletter-form {
    display: flex;
    gap: 0;
    align-items: center;
    width: 100%;
}

.newsletter-input {
    flex: 1;
    padding: 0.625rem 0.75rem;
    border: none;
    border-radius: 6px 0 0 6px;
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 0.875rem;
    outline: none;
    transition: background 0.3s ease;
}

.newsletter-input::placeholder {
    color: rgba(255, 255, 255, 0.7);
}

.newsletter-input:focus {
    background: rgba(255, 255, 255, 0.25);
}

.newsletter-button {
    padding: 0.625rem 0.75rem;
    border: none;
    border-radius: 0 6px 6px 0;
    background: linear-gradient(135deg, #00d4aa, #00b894);
    color: #fff;
    cursor: pointer;
    font-size: 0.875rem;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
}

.newsletter-button:hover {
    background: linear-gradient(135deg, #00b894, #00a085);
}

.footer-social {
    display: flex;
    gap: 0.625rem;
}

.social-link {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.18);
    transition: all 0.2s;
    color: white;
    text-decoration: none;
}

.social-link:hover {
    transform: scale(1.08);
    background: rgba(255, 255, 255, 0.25);
}

.footer-section-title {
    font-weight: 700;
    font-size: 0.9375rem;
    margin-bottom: 0.75rem;
    letter-spacing: 0.3px;
    color: white;
}

.footer-links {
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 0.875rem;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-link {
    color: white;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-block;
}

.footer-link:hover {
    text-decoration: underline;
}

.footer-link-with-icon {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.footer-link-icon {
    width: 80px;
    height: 80px;
    object-fit: contain;
    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.25));
    flex-shrink: 0;
}

.footer-link-external {
    font-size: 0.75rem;
    opacity: 0.85;
    margin-left: auto;
}

.footer-link-separator {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.15);
}

.footer-contact-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: rgba(255, 255, 255, 0.9);
}

.footer-contact-item i {
    width: 16px;
    text-align: center;
    flex-shrink: 0;
}

/* Footer bottom */
.footer-bottom {
    text-align: center;
    font-size: 0.75rem;
    padding: 0.875rem 0;
    background: rgba(0, 0, 0, 0.08);
}

.footer-bottom p {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
}

/* Responsive breakpoints */
@media (min-width: 768px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .footer-content {
        padding: 0 1.5rem;
        width: 100%;
    }
}

@media (min-width: 1024px) {
    .footer-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .footer-content {
        padding: 0 2rem;
        width: 100%;
    }
    
    .footer-section {
        padding-left: 0.75rem;
        border-left: 1px solid rgba(255, 255, 255, 0.15);
        width: 100%;
    }
    
    .footer-section:first-child {
        border-left: none;
        padding-left: 0;
    }
}

@media (min-width: 1280px) {
    .footer-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>

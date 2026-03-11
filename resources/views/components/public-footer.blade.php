<footer style="background: linear-gradient(135deg, #1e3a8a 0%, #22c55e 50%, #1e3a8a 100%); color: #fff; font-family: inherit; box-shadow: 0 -4px 20px rgba(0,0,0,0.1);">
  <div style="max-width: 1200px; margin: 0 auto; padding: 0 24px;">
    <div style="display:grid; grid-template-columns: repeat(4, minmax(220px, 1fr)); gap: 24px; padding: 36px 0 8px 0;">
      <div style="padding-right:12px;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:10px;">
          <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR" style="width:44px;height:44px; object-fit:contain; filter: drop-shadow(0 2px 8px rgba(0,0,0,.25));">
          <div style="font-size:22px; font-weight:700; letter-spacing:.3px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">CSAR</div>
        </div>
        <div id="footer-typewriter" data-text="Commissariat à la Sécurité Alimentaire et à la Résilience" data-speed="60" style="min-height:46px; font-size:15px; line-height:1.7; color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,0.3); font-weight: 500;"></div>
        
        <!-- Section Newsletter -->
        <div class="newsletter-section" style="margin: 20px 0 16px 0;">
          <div style="font-weight: 700; font-size: 17px; margin-bottom: 8px; letter-spacing: 0.3px; color: #fff; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Newsletter</div>
          <div style="width: 100%; height: 2px; background: linear-gradient(90deg, #00d4aa, #00b894); margin-bottom: 12px; border-radius: 1px;"></div>
          <form action="{{ route('newsletter.subscribe') }}" method="POST" style="display: flex; gap: 0; align-items: center; width: 100%;">
            @csrf
            <input 
              type="email" 
              name="email" 
              placeholder="Votre adresse email" 
              required
              style="
                flex: 1; 
                padding: 10px 12px; 
                border: none; 
                border-radius: 6px 0 0 6px; 
                background: rgba(255,255,255,0.15); 
                color: #fff; 
                font-size: 14px; 
                outline: none;
                transition: background 0.3s ease;
              "
              onfocus="this.style.background='rgba(255,255,255,0.25)'"
              onblur="this.style.background='rgba(255,255,255,0.15)'"
            >
            <button 
              type="submit" 
              style="
                padding: 10px 12px; 
                border: none; 
                border-radius: 0 6px 6px 0; 
                background: linear-gradient(135deg, #00d4aa, #00b894); 
                color: #fff; 
                cursor: pointer; 
                font-size: 14px; 
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 40px;
              "
              onmouseover="this.style.background='linear-gradient(135deg, #00b894, #00a085)'"
              onmouseout="this.style.background='linear-gradient(135deg, #00d4aa, #00b894)'"
            >
              <i class="fas fa-paper-plane" style="font-size: 14px;"></i>
            </button>
          </form>
        </div>
        
        <div class="social-icons" style="display:flex; gap:10px; margin-top:14px;">
          <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.18);transition:transform .2s, background .2s;" onmouseover="this.style.transform='scale(1.08)';this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.transform='scale(1)';this.style.background='rgba(255,255,255,0.18)'"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://www.facebook.com/profile.php?id=61562947586356&mibextid=wwXIfr&rdid=rdi0HoJAMnm5SUWB&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1A15LpvcqT%2F%3Fmibextid%3DwwXIfr" target="_blank" rel="noopener" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.18);transition:transform .2s, background .2s;" onmouseover="this.style.transform='scale(1.08)';this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.transform='scale(1)';this.style.background='rgba(255,255,255,0.18)'"><i class="fab fa-facebook-f"></i></a>
          <a href="https://x.com/csar_sn?s=21" target="_blank" rel="noopener" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.18);transition:transform .2s, background .2s;" onmouseover="this.style.transform='scale(1.08)';this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.transform='scale(1)';this.style.background='rgba(255,255,255,0.18)'"><i class="fab fa-twitter"></i></a>
          <a href="https://www.instagram.com/csar.sn?igsh=MWcxbTJnNzBnZGo5Mg%3D%3D&utm_source=qr" target="_blank" rel="noopener" style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.18);transition:transform .2s, background .2s;" onmouseover="this.style.transform='scale(1.08)';this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.transform='scale(1)';this.style.background='rgba(255,255,255,0.18)'"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
      
      <div style="padding-left:12px; border-left:1px solid rgba(255,255,255,0.15);">
        <div style="font-weight:700; font-size:16px; margin-bottom:12px; letter-spacing:.3px; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Liens rapides</div>
        <ul style="font-size:15px; margin:0; padding:0; list-style:none; line-height:1.8;">
          <li style="margin-bottom:8px;"><a href="/" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Accueil</a></li>
          <li style="margin-bottom:8px;"><a href="/about" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">À propos</a></li>
          <li style="margin-bottom:8px;"><a href="/institution" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Institution</a></li>
          <li style="margin-bottom:8px;"><a href="/actualites" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Actualités</a></li>
          <li style="margin-bottom:8px;"><a href="{{ route('sim.index') }}" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">SIM</a></li>
          <li style="margin-top:8px;"><a href="{{ route('gallery') }}" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Nos missions</a></li>
          
        </ul>
      </div>

      <div style="padding-left:12px; border-left:1px solid rgba(255,255,255,0.15);">
        <div style="font-weight:700; font-size:16px; margin-bottom:12px; letter-spacing:.3px; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Partenaires institutionnels</div>
        <ul style="list-style:none; margin:0; padding:0; font-size:15px; line-height:1.8;">
          <li style="margin:8px 0;">
            <a href="https://primature.sn/" target="_blank" rel="noopener nofollow" title="Primature du Sénégal" style="color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
              <img src="{{ asset('images/primature.jpg') }}" alt="Primature" style="width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.25))">
              <span>Primature</span><span style="font-size:13px;opacity:.9;">↗</span>
            </a>
          </li>
          <li style="margin:8px 0;">
            <a href="https://femme.gouv.sn/" target="_blank" rel="noopener nofollow" title="Ministère de la famille de l'action sociale et des solidarités" style="color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
              <img src="{{ asset('images/mfs.png') }}" alt="Ministère de la famille de l'action sociale et des solidarités" style="width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.25))">
              <span>Ministère de la famille de l'action sociale et des solidarités</span><span style="font-size:13px;opacity:.9;">↗</span>
            </a>
          </li>
          <li style="margin:8px 0;">
            <a href="https://www.presidence.sn/" target="_blank" rel="noopener nofollow" title="Présidence de la République" style="color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
              <img src="{{ asset('images/presidence.png') }}" alt="Présidence de la République" style="width:80px;height:80px;object-fit:contain;filter:drop-shadow(0 1px 2px rgba(0,0,0,.25))">
              <span>Présidence de la République</span><span style="font-size:13px;opacity:.9;">↗</span>
            </a>
          </li>
          <li style="margin:10px 0 0 0; padding-top:10px; border-top:1px solid rgba(255,255,255,0.15);">
            <a href="{{ route('partners.index') }}" title="Nos partenaires" style="color:#fff; text-decoration:none; display:flex; align-items:center; gap:10px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
              <i class="fas fa-handshake"></i>
              <span>Nos partenaires</span>
            </a>
          </li>
        </ul>
      </div>
      
      <div style="padding-left:12px; border-left:1px solid rgba(255,255,255,0.15);">
        <div style="font-weight:700; font-size:16px; margin-bottom:12px; letter-spacing:.3px; text-shadow: 0 1px 3px rgba(0,0,0,0.3);">Contact</div>
        <ul style="font-size:15px; margin:0; padding:0; list-style:none; line-height:1.8;">
          <li style="margin-bottom:8px;"><a href="{{ route('demande.selection') }}" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Effectuer une demande</a></li>
          <li style="margin-bottom:10px;"><a href="{{ route('contact.simple') }}" style="color:#fff; text-decoration:none; text-shadow: 0 1px 2px rgba(0,0,0,0.2);" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Nous contacter</a></li>
          <li style="margin-bottom:8px; font-size:14px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);"><i class="fas fa-envelope" style="margin-right:8px;"></i>contact@csar.sn</li>
          <li style="margin-bottom:8px; font-size:14px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);"><i class="fas fa-phone" style="margin-right:8px;"></i>+221 33 123 45 67</li>
          <li style="font-size:14px; text-shadow: 0 1px 2px rgba(0,0,0,0.2);"><i class="fas fa-map-marker-alt" style="margin-right:8px;"></i>Dakar, Sénégal</li>
        </ul>
      </div>
    </div>
  </div>
  <div style="text-align:center; font-size:14px; padding:16px 0; background:rgba(0,0,0,0.15); color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,0.4); font-weight: 500;">
    © 2025 CSAR - Commissariat à la Sécurité Alimentaire et à la Résilience. Tous droits réservés.
  </div>

  <script>
  (function(){
    var el=document.getElementById('footer-typewriter');
    if(!el) return;
    var text=el.getAttribute('data-text')||''; var speed=Number(el.getAttribute('data-speed')||70);
    var caret=document.createElement('span'); caret.textContent='▍'; caret.style.marginLeft='6px'; caret.style.animation='blink 1s steps(1,end) infinite'; el.appendChild(caret);
    if(!document.getElementById('ft-blink-style')){var s=document.createElement('style'); s.id='ft-blink-style'; s.textContent='@keyframes blink{0%{opacity:1}50%{opacity:0}100%{opacity:1}}'; document.head.appendChild(s);}    
    
    function typeText(){
      var i=0;
      // Effacer le texte existant (sauf le caret)
      while(el.firstChild && el.firstChild !== caret){
        el.removeChild(el.firstChild);
      }
      
      function step(){ 
        if(i<text.length){ 
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
    
    var io=new IntersectionObserver(function(entries){ 
      if(entries[0].isIntersecting){ 
        typeText(); 
        io.disconnect(); 
      } 
    },{threshold:0.1}); 
    io.observe(el);
  })();
  </script>
</footer>
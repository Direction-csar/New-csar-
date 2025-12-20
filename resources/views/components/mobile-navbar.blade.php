{{-- ========================================
     NAVBAR MOBILE RESPONSIVE
     Composant réutilisable pour toutes les interfaces
     ======================================== --}}

<nav class="mobile-navbar visible-mobile">
    <div class="mobile-navbar-brand">
        <img src="{{ asset('images/logos/LOGO CSAR vectoriel-01.png') }}" 
             alt="CSAR Logo" 
             class="mobile-navbar-logo">
        <div>
            <div class="mobile-navbar-title">CSAR</div>
            <div class="mobile-navbar-subtitle">{{ $interface ?? 'Interface' }}</div>
        </div>
    </div>
    
    <button class="burger-menu" id="burgerMenu" aria-label="Menu de navigation">
        <span class="burger-line"></span>
        <span class="burger-line"></span>
        <span class="burger-line"></span>
    </button>
</nav>

<style>
:root {
    --interface-primary: #1e3a8a;
    --interface-secondary: #1e40af;
}

.mobile-navbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 70px;
    background: linear-gradient(135deg, var(--interface-primary), var(--interface-secondary));
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1rem;
    z-index: 1001;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.mobile-navbar-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.mobile-navbar-logo {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.25rem;
}

.mobile-navbar-title {
    font-weight: 700;
    font-size: 1.125rem;
    line-height: 1;
}

.mobile-navbar-subtitle {
    font-size: 0.75rem;
    opacity: 0.8;
    line-height: 1;
}

.burger-menu {
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.burger-menu:hover {
    background: rgba(255, 255, 255, 0.2);
}

.burger-menu:focus {
    outline: 2px solid rgba(255, 255, 255, 0.5);
    outline-offset: 2px;
}

.burger-line {
    width: 20px;
    height: 2px;
    background: white;
    border-radius: 1px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
}

.burger-menu.active .burger-line:nth-child(1) {
    transform: rotate(45deg) translate(5px, 5px);
}

.burger-menu.active .burger-line:nth-child(2) {
    opacity: 0;
    transform: scale(0);
}

.burger-menu.active .burger-line:nth-child(3) {
    transform: rotate(-45deg) translate(7px, -6px);
}

.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    backdrop-filter: blur(2px);
}

.sidebar-overlay.open {
    opacity: 1;
    visibility: visible;
}

/* Masquer sur desktop */
@media (min-width: 768px) {
    .mobile-navbar {
        display: none;
    }
    
    .sidebar-overlay {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const sidebar = document.getElementById('sidebar') || document.querySelector('.sidebar');
    let overlay = document.getElementById('sidebarOverlay');

    // Some layouts already render the overlay. If not, create it.
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'sidebarOverlay';
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    if (burgerMenu && sidebar && overlay) {
        burgerMenu.addEventListener('click', function() {
            burgerMenu.classList.toggle('active');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('open');
        });
        
        overlay.addEventListener('click', function() {
            burgerMenu.classList.remove('active');
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        });
    }
});
</script>

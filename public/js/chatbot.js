// CSAR Chatbot - Deferred Loading
(function() {
    const locale = '{{ app()->getLocale() }}';
    const base = '{{ url("") }}';
    const routes = {
        home: '{{ route("home", ["locale" => app()->getLocale()]) }}',
        about: '{{ route("about", ["locale" => app()->getLocale()]) }}',
        institution: '{{ route("institution", ["locale" => app()->getLocale()]) }}',
        news: '{{ route("news.index", ["locale" => app()->getLocale()]) }}',
        sim: '{{ url("/sim-reports") }}',
        projets: '{{ route("projets.index", ["locale" => app()->getLocale()]) }}',
        ressources: '{{ route("ressources.index", ["locale" => app()->getLocale()]) }}',
        faq: '{{ route("faq.index", ["locale" => app()->getLocale()]) }}',
        partners: '{{ route("partners.index", ["locale" => app()->getLocale()]) }}',
        contact: '{{ route("contact", ["locale" => app()->getLocale()]) }}',
        don: '{{ route("don.index", ["locale" => app()->getLocale()]) }}',
        demande: '{{ url("/demande") }}',
        action: '{{ route("action", ["locale" => app()->getLocale()]) }}',
        track: '{{ route("track", ["locale" => app()->getLocale()]) }}'
    };

    const replies = {
        'demande-aide': {
            html: '<p><strong>Demande d\'aide – guide en 3 étapes</strong></p>' +
                '<ol style="margin:8px 0 0 0; padding-left:20px; font-size:0.9rem;">' +
                '<li>Remplir le formulaire en ligne (coordonnées, type d\'aide, situation).</li>' +
                '<li>Recevoir un <strong>code de suivi</strong> par email/SMS.</li>' +
                '<li>Suivre l\'avancement avec ce code dans « Suivre ma demande ».</li></ol>' +
                '<p style="margin-top:12px;">Vous pouvez déposer une demande d\'assistance alimentaire ou une demande de partenariat.</p>' +
                '<p><a href="' + routes.demande + '">→ Aller au formulaire de demande d\'aide</a></p>'
        },
        'guide-site': {
            html: '<p><strong>Guide de la plateforme CSAR</strong></p>' +
                '<ul><li><a href="' + routes.home + '">Accueil</a></li>' +
                '<li><a href="' + routes.about + '">À propos</a></li>' +
                '<li><a href="' + routes.institution + '">Institution</a></li>' +
                '<li><a href="' + routes.news + '">Actualités</a></li>' +
                '<li><a href="' + routes.sim + '">Rapports SIM</a></li>' +
                '<li><a href="' + routes.projets + '">Projets & interventions</a></li>' +
                '<li><a href="' + routes.ressources + '">Ressources</a></li>' +
                '<li><a href="' + routes.faq + '">FAQ</a></li>' +
                '<li><a href="' + routes.partners + '">Partenaires</a></li>' +
                '<li><a href="' + routes.contact + '">Contact</a></li></ul>'
        },
        'suivi-demande': {
            html: '<p>Avec le <strong>code de suivi</strong> reçu après votre demande, vous pouvez consulter l\'état de votre dossier.</p>' +
                '<p><a href="' + routes.track + '">→ Suivre ma demande</a></p>'
        },
        'don': {
            html: '<p><strong>Faire un don</strong></p><p>Soutenez les actions du CSAR pour la sécurité alimentaire et la résilience. La page Don vous explique comment contribuer.</p><p><a href="' + routes.don + '">→ Faire un don</a></p>'
        },
        'contact': {
            html: '<p>Pour nous contacter : formulaire, email, téléphone et adresse.</p>' +
                '<p><a href="' + routes.contact + '">→ Page Contact</a></p>'
        },
        'faq': {
            html: '<p>Questions fréquentes pour les usagers et les partenaires (assistance, suivi, rapports, partenariat).</p>' +
                '<p><a href="' + routes.faq + '">→ Voir la FAQ</a></p>'
        }
    };

    const keywordMap = [
        { keys: ['demande', 'aide', 'assistance', 'formulaire', 'effectuer', 'déposer', 'request', 'help'], action: 'demande-aide' },
        { keys: ['don', 'donner', 'soutien', 'contribuer', 'donation'], action: 'don' },
        { keys: ['suivi', 'suivre', 'code', 'dossier', 'état', 'track', 'status'], action: 'suivi-demande' },
        { keys: ['contact', 'écrire', 'email', 'téléphone', 'joindre', 'contacter'], action: 'contact' },
        { keys: ['faq', 'question', 'fréquent', 'réponse'], action: 'faq' },
        { keys: ['rapport', 'sim', 'prix', 'marché', 'carte', 'donnée'], action: 'guide-site' },
        { keys: ['projet', 'intervention', 'ressource', 'document', 'accueil', 'à propos', 'institution', 'partenaire', 'navigation', 'guide'], action: 'guide-site' }
    ];

    function getActionFromQuestion(text) {
        if (!text || !text.trim()) return null;
        const t = text.toLowerCase().trim();
        for (let i = 0; i < keywordMap.length; i++) {
            for (let j = 0; j < keywordMap[i].keys.length; j++) {
                if (t.indexOf(keywordMap[i].keys[j]) !== -1) return keywordMap[i].action;
            }
        }
        return null;
    }

    const defaultReply = '<p>Je n\'ai pas bien compris votre question. Vous pouvez :</p>' +
        '<ul><li>Réécrire votre question avec des mots comme <strong>demande d\'aide</strong>, <strong>suivi</strong>, <strong>contact</strong>, <strong>don</strong>, <strong>FAQ</strong>.</li>' +
        '<li>Ou cliquer sur une des options ci-dessus pour accéder directement à l\'information.</li></ul>';

    // Create chatbot HTML
    const chatbotHTML = `
<div id="csar-chatbot" class="csar-chatbot">
    <button type="button" class="csar-chatbot-toggle" id="csar-chatbot-toggle" aria-label="Ouvrir l'assistant">
        <i class="fas fa-comments"></i>
        <span class="csar-chatbot-toggle-badge">Assistant CSAR</span>
    </button>
    <div class="csar-chatbot-panel" id="csar-chatbot-panel" aria-hidden="true">
        <div class="csar-chatbot-header">
            <div class="csar-chatbot-header-title"><i class="fas fa-robot"></i> Assistant CSAR</div>
            <button type="button" class="csar-chatbot-close" id="csar-chatbot-close" aria-label="Fermer"><i class="fas fa-times"></i></button>
        </div>
        <div class="csar-chatbot-messages" id="csar-chatbot-messages">
            <div class="csar-chatbot-msg csar-chatbot-msg-bot" data-role="welcome">
                <p><strong>Bonjour, je suis l'Assistant CSAR.</strong></p>
                <p>Posez-moi vos questions : demande d'aide, suivi de dossier, contact, dons, FAQ, rapports SIM, etc.</p>
                <p class="csar-chatbot-msg-hint">Écrivez dans la zone ci-dessous ou choisissez une option rapide.</p>
            </div>
        </div>
        <div class="csar-chatbot-quick-actions" id="csar-chatbot-quick">
            <button type="button" class="csar-chatbot-quick-btn" data-action="demande-aide"><i class="fas fa-hand-holding-heart"></i> Demande d'aide</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="don"><i class="fas fa-heart"></i> Faire un don</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="guide-site"><i class="fas fa-sitemap"></i> Guide du site</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="suivi-demande"><i class="fas fa-search"></i> Suivre ma demande</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="contact"><i class="fas fa-envelope"></i> Contact</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="faq"><i class="fas fa-question-circle"></i> FAQ</button>
        </div>
        <div class="csar-chatbot-write">
            <label for="csar-chatbot-input" class="visually-hidden">Posez votre question</label>
            <textarea id="csar-chatbot-input" class="csar-chatbot-input" rows="2" placeholder="Posez votre question ici..." maxlength="500"></textarea>
            <button type="button" class="csar-chatbot-send" id="csar-chatbot-send" aria-label="Envoyer"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
</div>
<style>
.csar-chatbot { position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: inherit; }
.csar-chatbot-toggle {
    width: 56px; height: 56px; border-radius: 50%;
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
    color: #fff; border: none; cursor: pointer; box-shadow: 0 4px 20px rgba(4, 120, 87, 0.45);
    display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.csar-chatbot-toggle:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(4, 120, 87, 0.5); }
.csar-chatbot-toggle-badge { display: none; }
@media (min-width: 768px) {
    .csar-chatbot-toggle { width: auto; height: auto; padding: 14px 20px; border-radius: 50px; gap: 8px; }
    .csar-chatbot-toggle-badge { display: inline; font-weight: 600; font-size: 0.9rem; }
}
.csar-chatbot-panel {
    position: absolute; bottom: 70px; right: 0; width: 360px; max-width: calc(100vw - 48px);
    max-height: 480px; background: #fff; border-radius: 16px; box-shadow: 0 12px 48px rgba(0,0,0,0.15);
    border: 1px solid rgba(0,0,0,0.06); display: none; flex-direction: column; overflow: hidden;
}
.csar-chatbot-panel.is-open { display: flex; animation: csar-chatbot-slide 0.25s ease; }
@keyframes csar-chatbot-slide { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.csar-chatbot-header {
    background: linear-gradient(135deg, #047857 0%, #059669 100%); color: #fff;
    padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;
}
.csar-chatbot-header-title { font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px; }
.csar-chatbot-close { background: none; border: none; color: #fff; cursor: pointer; padding: 4px; opacity: 0.9; }
.csar-chatbot-close:hover { opacity: 1; }
.csar-chatbot-messages {
    flex: 1; overflow-y: auto; padding: 16px; min-height: 180px; max-height: 280px;
}
.csar-chatbot-msg { margin-bottom: 12px; }
.csar-chatbot-msg-bot p { margin: 0 0 8px 0; font-size: 0.9rem; line-height: 1.5; color: #374151; }
.csar-chatbot-msg-bot p:last-child { margin-bottom: 0; }
.csar-chatbot-msg-hint { font-size: 0.85rem !important; color: #6b7280 !important; }
.csar-chatbot-msg a { color: #047857; font-weight: 600; text-decoration: none; }
.csar-chatbot-msg a:hover { text-decoration: underline; }
.csar-chatbot-msg ul { margin: 8px 0 0 0; padding-left: 20px; font-size: 0.9rem; color: #374151; }
.csar-chatbot-msg li { margin-bottom: 4px; }
.csar-chatbot-quick-actions { padding: 12px 16px; border-top: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 8px; }
.csar-chatbot-quick-btn {
    padding: 8px 12px; font-size: 0.8rem; border-radius: 8px; border: 1px solid #d1d5db;
    background: #f9fafb; color: #374151; cursor: pointer; transition: background 0.2s, color 0.2s;
    display: inline-flex; align-items: center; gap: 6px;
}
.csar-chatbot-quick-btn:hover { background: rgba(4, 120, 87, 0.1); color: #047857; border-color: #059669; }
.csar-chatbot-write {
    padding: 10px 16px 14px; border-top: 1px solid #e5e7eb; background: #f9fafb;
    display: flex; align-items: flex-end; gap: 8px;
}
.csar-chatbot-input {
    flex: 1; min-height: 44px; max-height: 100px; padding: 10px 12px;
    border: 1px solid #d1d5db; border-radius: 10px; font-size: 0.9rem; resize: none;
    font-family: inherit; transition: border-color 0.2s, box-shadow 0.2s;
}
.csar-chatbot-input:focus { outline: none; border-color: #047857; box-shadow: 0 0 0 2px rgba(4, 120, 87, 0.15); }
.csar-chatbot-input::placeholder { color: #9ca3af; }
.csar-chatbot-send {
    width: 44px; height: 44px; border-radius: 10px; border: none;
    background: linear-gradient(135deg, #047857 0%, #059669 100%); color: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: opacity 0.2s, transform 0.2s;
}
.csar-chatbot-send:hover { opacity: 0.95; transform: scale(1.05); }
.csar-chatbot-msg-user {
    margin-left: auto; max-width: 85%; margin-bottom: 12px;
    padding: 10px 14px; background: #047857; color: #fff; border-radius: 12px 12px 4px 12px;
    font-size: 0.9rem; line-height: 1.4;
}
.csar-chatbot-msg-user p { margin: 0; }
</style>
`;

    // Insert chatbot HTML
    const placeholder = document.getElementById('chatbot-placeholder');
    if (placeholder) {
        placeholder.insertAdjacentHTML('beforebegin', chatbotHTML);
        placeholder.remove();
    }

    // Initialize chatbot
    const panel = document.getElementById('csar-chatbot-panel');
    const toggle = document.getElementById('csar-chatbot-toggle');
    const closeBtn = document.getElementById('csar-chatbot-close');
    const messagesEl = document.getElementById('csar-chatbot-messages');
    const quickEl = document.getElementById('csar-chatbot-quick');

    function openPanel() {
        if (panel) {
            panel.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
        }
    }
    function closePanel() {
        if (panel) {
            panel.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
        }
    }
    function appendBotMessage(html) {
        if (!messagesEl) return;
        const div = document.createElement('div');
        div.className = 'csar-chatbot-msg csar-chatbot-msg-bot';
        div.innerHTML = html;
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function appendUserMessage(text) {
        if (!messagesEl || !text || !text.trim()) return;
        const div = document.createElement('div');
        div.className = 'csar-chatbot-msg csar-chatbot-msg-user';
        div.innerHTML = '<p>' + text.replace(/</g, '&lt;').replace(/>/g, '&gt;').trim() + '</p>';
        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function sendQuestion() {
        const input = document.getElementById('csar-chatbot-input');
        if (!input) return;
        const text = (input.value || '').trim();
        if (!text) return;
        appendUserMessage(text);
        input.value = '';
        input.style.height = 'auto';
        const action = getActionFromQuestion(text);
        const reply = action && replies[action] ? replies[action].html : defaultReply;
        appendBotMessage(reply);
    }

    if (toggle) toggle.addEventListener('click', openPanel);
    if (closeBtn) closeBtn.addEventListener('click', closePanel);

    if (quickEl) {
        quickEl.addEventListener('click', function(e) {
            const btn = e.target.closest('.csar-chatbot-quick-btn');
            if (!btn) return;
            const action = btn.getAttribute('data-action');
            const r = replies[action];
            if (r) appendBotMessage(r.html);
        });
    }

    const sendBtn = document.getElementById('csar-chatbot-send');
    const inputEl = document.getElementById('csar-chatbot-input');
    if (sendBtn) sendBtn.addEventListener('click', sendQuestion);
    if (inputEl) {
        inputEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendQuestion();
            }
        });
    }
})();

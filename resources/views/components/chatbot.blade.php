{{-- Chatbot d'assistance CSAR - Guide A à Z de la plateforme --}}
<div id="csar-chatbot" class="csar-chatbot">
    <button type="button" class="csar-chatbot-toggle" id="csar-chatbot-toggle" aria-label="Ouvrir l'assistant">
        <i class="fas fa-comments"></i>
        <span class="csar-chatbot-toggle-badge">Assistant</span>
    </button>
    <div class="csar-chatbot-panel" id="csar-chatbot-panel" aria-hidden="true">
        <div class="csar-chatbot-header">
            <div class="csar-chatbot-header-title">
                <i class="fas fa-robot"></i>
                <span>Assistant CSAR</span>
            </div>
            <button type="button" class="csar-chatbot-close" id="csar-chatbot-close" aria-label="Fermer"><i class="fas fa-times"></i></button>
        </div>
        <div class="csar-chatbot-messages" id="csar-chatbot-messages">
            <div class="csar-chatbot-msg csar-chatbot-msg-bot" data-role="welcome">
                <p>Bonjour ! Je suis l'assistant du CSAR. Je peux vous guider sur toute la plateforme : navigation, <strong>demande d'aide</strong>, contact, FAQ.</p>
                <p class="csar-chatbot-msg-hint">Choisissez une option ci-dessous :</p>
            </div>
        </div>
        <div class="csar-chatbot-quick-actions" id="csar-chatbot-quick">
            <button type="button" class="csar-chatbot-quick-btn" data-action="demande-aide"><i class="fas fa-hand-holding-heart"></i> Demande d'aide</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="guide-site"><i class="fas fa-sitemap"></i> Guide du site</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="suivi-demande"><i class="fas fa-search"></i> Suivre ma demande</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="contact"><i class="fas fa-envelope"></i> Contact</button>
            <button type="button" class="csar-chatbot-quick-btn" data-action="faq"><i class="fas fa-question-circle"></i> FAQ</button>
        </div>
    </div>
</div>

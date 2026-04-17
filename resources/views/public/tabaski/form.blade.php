<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Avance Tabaski 2026 — CSAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link rel="icon" type="image/png" href="{{ asset('images/csar-logo.png') }}">
    <style>
        body { background: linear-gradient(135deg, #065f46 0%, #047857 50%, #10b981 100%); min-height: 100vh; }
        .card { backdrop-filter: blur(10px); }
        .step { display: none; }
        .step.active { display: block; }
        .montant-btn { transition: all 0.2s; }
        .montant-btn.selected { background: #065f46; color: white; border-color: #065f46; transform: scale(1.02); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-in { animation: fadeIn 0.4s ease forwards; }
        .spinner { border: 3px solid rgba(255,255,255,0.3); border-top: 3px solid white; border-radius: 50%; width: 20px; height: 20px; animation: spin 0.8s linear infinite; display: inline-block; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="flex items-center justify-center p-4 py-10">

<div class="w-full max-w-lg">

    {{-- En-tête --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-full mb-4 shadow-lg overflow-hidden">
            <img src="{{ asset('img/Tabaski logo.jpeg') }}" alt="Tabaski 2026" class="w-full h-full object-cover">
        </div>
        <h1 class="text-3xl font-bold text-white">Avance Tabaski 2026</h1>
        <p class="text-green-100 mt-2 text-sm">CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience</p>

        @if(!$ferme)
        <div class="mt-4 inline-flex items-center gap-2 bg-white bg-opacity-20 text-white text-sm px-4 py-2 rounded-full">
            <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
            Inscriptions ouvertes jusqu'au {{ $expire->format('d/m/Y') }}
        </div>
        @endif
    </div>

    {{-- Carte principale --}}
    <div class="card bg-white rounded-3xl shadow-2xl overflow-hidden">

        @if($ferme)
        {{-- Inscriptions fermées --}}
        <div class="p-8 text-center">
            <div class="text-6xl mb-4">🔒</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Inscriptions closes</h2>
            <p class="text-gray-500">La période d'inscription pour l'avance Tabaski 2026 est terminée depuis le {{ $expire->format('d/m/Y') }}.</p>
            <p class="text-gray-400 mt-4 text-sm">Pour toute question, contactez la DRH.</p>
        </div>

        @else

        {{-- Barre de progression --}}
        <div class="bg-gray-100 px-6 pt-6 pb-2">
            <div class="flex items-center gap-2">
                <div id="prog1" class="flex items-center gap-2 flex-1">
                    <div class="w-8 h-8 rounded-full bg-emerald-600 text-white text-sm flex items-center justify-center font-bold shrink-0">1</div>
                    <span class="text-xs font-medium text-emerald-700 hidden sm:block">Identification</span>
                </div>
                <div class="h-0.5 flex-1 bg-gray-300" id="line12"></div>
                <div id="prog2" class="flex items-center gap-2 flex-1">
                    <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 text-sm flex items-center justify-center font-bold shrink-0" id="dot2">2</div>
                    <span class="text-xs font-medium text-gray-400 hidden sm:block">Confirmation</span>
                </div>
                <div class="h-0.5 flex-1 bg-gray-300" id="line23"></div>
                <div id="prog3" class="flex items-center gap-2 flex-1">
                    <div class="w-8 h-8 rounded-full bg-gray-300 text-gray-500 text-sm flex items-center justify-center font-bold shrink-0" id="dot3">3</div>
                    <span class="text-xs font-medium text-gray-400 hidden sm:block">Montant</span>
                </div>
            </div>
        </div>

        {{-- ÉTAPE 1 : Identification --}}
        <div id="step1" class="step active p-6 sm:p-8 animate-in">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Votre identité</h2>
            <p class="text-gray-500 text-sm mb-6">Entrez votre prénom, nom et région pour retrouver votre profil dans notre base.</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" id="inp_prenom" placeholder="Ex: Ousmane" autocomplete="given-name"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" id="inp_nom" placeholder="Ex: NDIAYE" autocomplete="family-name"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:border-emerald-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Région <span class="text-red-500">*</span></label>
                    <select id="inp_region"
                        class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-base focus:border-emerald-500 focus:outline-none transition bg-white">
                        <option value="">— Sélectionnez votre région —</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="error1" class="hidden mt-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"></div>

            <button onclick="doSearch()" id="btn_search"
                class="mt-6 w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-xl transition text-base flex items-center justify-center gap-2">
                <span>Rechercher mon profil</span>
                <span>→</span>
            </button>
        </div>

        {{-- ÉTAPE 2 : Sélection agent (si plusieurs résultats) --}}
        <div id="step2" class="step p-6 sm:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Confirmez votre identité</h2>
            <p class="text-gray-500 text-sm mb-5">Plusieurs agents correspondent. Sélectionnez votre profil.</p>
            <div id="agents_list" class="space-y-3"></div>
            <button onclick="goStep(1)" class="mt-4 text-gray-500 text-sm flex items-center gap-1 hover:text-emerald-600">
                ← Modifier ma saisie
            </button>
        </div>

        {{-- ÉTAPE 3 : Choix du montant --}}
        <div id="step3" class="step p-6 sm:p-8">
            <div id="agent_card" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6"></div>

            <h2 class="text-lg font-bold text-gray-800 mb-4">Choisissez votre montant</h2>
            <div class="grid grid-cols-3 gap-3 mb-6">
                <button onclick="selectMontant('100000')" class="montant-btn border-2 border-gray-200 rounded-2xl p-4 text-center cursor-pointer hover:border-emerald-400" data-m="100000">
                    <div class="text-base font-bold text-gray-800">100 000 FCFA</div>
                </button>
                <button onclick="selectMontant('150000')" class="montant-btn border-2 border-gray-200 rounded-2xl p-4 text-center cursor-pointer hover:border-emerald-400" data-m="150000">
                    <div class="text-base font-bold text-gray-800">150 000 FCFA</div>
                </button>
                <button onclick="selectMontant('200000')" class="montant-btn border-2 border-gray-200 rounded-2xl p-4 text-center cursor-pointer hover:border-emerald-400" data-m="200000">
                    <div class="text-base font-bold text-gray-800">200 000 FCFA</div>
                </button>
            </div>

            <div id="error3" class="hidden mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm"></div>

            <button onclick="doSubmit()" id="btn_submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-bold py-4 rounded-xl transition text-base flex items-center justify-center gap-2">
                ✅ Valider ma demande
            </button>
            <button onclick="goStep(1)" class="mt-3 w-full text-gray-500 text-sm py-2 hover:text-emerald-600">
                ← Recommencer
            </button>
        </div>

        {{-- ÉTAPE 4 : Succès --}}
        <div id="step4" class="step p-6 sm:p-8 text-center">
            <div class="text-7xl mb-4">✅</div>
            <h2 class="text-2xl font-bold text-emerald-700 mb-2">Demande enregistrée !</h2>
            <div id="success_msg" class="text-gray-600 mb-4"></div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-sm text-emerald-800 mb-5">
                Votre demande a été transmise à la DRH. Vous serez contacté pour la suite de la procédure.
            </div>
            <button onclick="telechargerTicket()" class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2 text-sm">
                <span>⬇️</span> Télécharger mon ticket de confirmation
            </button>
            <p class="text-gray-400 text-xs mt-4">En cas de question, contactez la Direction des Ressources Humaines.</p>
        </div>

        @endif
    </div>

    <p class="text-center text-green-200 text-xs mt-6">© 2026 CSAR — Commissariat à la Sécurité Alimentaire et à la Résilience</p>
</div>

<script>
    let selectedAgentId = null;
    let selectedMontant = null;
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    function goStep(n) {
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
        document.getElementById('step' + n).classList.add('active');
        document.getElementById('step' + n).classList.add('animate-in');
        updateProgress(n);
    }

    function updateProgress(step) {
        ['dot2','dot3'].forEach((id, i) => {
            const dot = document.getElementById(id);
            const active = step > i + 1;
            dot.className = 'w-8 h-8 rounded-full text-sm flex items-center justify-center font-bold shrink-0 ' + (active ? 'bg-emerald-600 text-white' : 'bg-gray-300 text-gray-500');
        });
    }

    function showError(id, msg) {
        const el = document.getElementById(id);
        el.textContent = msg;
        el.classList.remove('hidden');
    }
    function hideError(id) { document.getElementById(id).classList.add('hidden'); }

    function setLoading(btnId, loading) {
        const btn = document.getElementById(btnId);
        if (loading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span><span>Recherche en cours...</span>';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<span>Rechercher mon profil</span><span>→</span>';
        }
    }

    async function doSearch() {
        const prenom = document.getElementById('inp_prenom').value.trim();
        const nom    = document.getElementById('inp_nom').value.trim();
        hideError('error1');

        const region = document.getElementById('inp_region').value;
        if (!prenom || !nom) {
            showError('error1', 'Veuillez entrer votre prénom et votre nom.');
            return;
        }
        if (!region) {
            showError('error1', 'Veuillez sélectionner votre région.');
            return;
        }

        setLoading('btn_search', true);

        try {
            const res  = await fetch('/avance-tabaski/search', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ prenom, nom, region }),
            });
            const data = await res.json();

            if (!data.found) {
                showError('error1', data.message || 'Aucun agent trouvé. Vérifiez votre saisie.');
                return;
            }

            if (data.agents.length === 1) {
                selectAgent(data.agents[0]);
            } else {
                showMultiple(data.agents);
            }

        } catch (e) {
            showError('error1', 'Erreur réseau. Vérifiez votre connexion.');
        } finally {
            setLoading('btn_search', false);
        }
    }

    function showMultiple(agents) {
        const list = document.getElementById('agents_list');
        list.innerHTML = agents.map(a => `
            <button onclick='selectAgent(${JSON.stringify(a).replace(/'/g, "&#39;")})' class="w-full text-left border-2 border-gray-200 hover:border-emerald-400 rounded-xl p-4 transition ${a.deja_inscrit ? 'opacity-50 cursor-not-allowed' : ''}">
                <div class="font-bold text-gray-800">${a.prenom} ${a.nom}</div>
                <div class="text-sm text-gray-500">${a.poste}</div>
                <div class="text-xs text-gray-400 mt-1">📍 ${a.direction} — ${a.region}</div>
                ${a.deja_inscrit ? '<div class="text-xs text-orange-500 mt-1 font-semibold">⚠️ Déjà inscrit</div>' : ''}
            </button>
        `).join('');
        goStep(2);
    }

    function selectAgent(agent) {
        if (agent.deja_inscrit) {
            alert('Cet agent a déjà effectué sa demande d\'avance Tabaski.');
            return;
        }
        selectedAgentId = agent.id;
        document.getElementById('agent_card').innerHTML = `
            <div class="flex items-start gap-3">
                <div class="text-3xl">👤</div>
                <div>
                    <div class="font-bold text-gray-900 text-lg">${agent.prenom} ${agent.nom}</div>
                    <div class="text-sm text-emerald-700 font-medium">${agent.poste}</div>
                    <div class="text-xs text-gray-500 mt-1">📍 ${agent.direction} — ${agent.region}</div>
                </div>
            </div>
        `;
        selectedMontant = null;
        document.querySelectorAll('.montant-btn').forEach(b => b.classList.remove('selected'));
        goStep(3);
    }

    function selectMontant(m) {
        selectedMontant = m;
        document.querySelectorAll('.montant-btn').forEach(b => {
            b.classList.toggle('selected', b.dataset.m === m);
        });
        hideError('error3');
    }

    async function doSubmit() {
        hideError('error3');
        if (!selectedMontant) {
            showError('error3', 'Veuillez choisir un montant.');
            return;
        }

        const btn = document.getElementById('btn_submit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span><span>Envoi en cours...</span>';

        try {
            const res  = await fetch('/avance-tabaski/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ agent_id: selectedAgentId, montant: selectedMontant }),
            });
            const data = await res.json();

            if (data.success) {
                const now = new Date();
                const dateStr = now.toLocaleDateString('fr-FR', { day:'2-digit', month:'long', year:'numeric', hour:'2-digit', minute:'2-digit' });
                const ref = 'TAB2026-' + String(selectedAgentId).padStart(4,'0') + '-' + now.getTime().toString(36).toUpperCase();
                ticketData = {
                    prenom: data.agent.prenom, nom: data.agent.nom,
                    direction: data.agent.direction, region: data.agent.region || '—',
                    poste: data.agent.poste || '—', montant: data.agent.montant,
                    reference: ref, date: dateStr
                };
                document.getElementById('success_msg').innerHTML = `
                    <p class="font-semibold text-lg">${data.agent.prenom} ${data.agent.nom}</p>
                    <p class="text-emerald-600 font-bold text-2xl mt-2">${data.agent.montant}</p>
                    <p class="text-sm mt-2">${data.agent.direction}</p>
                    <p class="text-xs text-gray-400 mt-1">Réf : ${ref}</p>
                `;
                goStep(4);
            } else {
                showError('error3', data.message || 'Une erreur est survenue.');
                btn.disabled = false;
                btn.innerHTML = '✅ Valider ma demande';
            }
        } catch (e) {
            showError('error3', 'Erreur réseau. Veuillez réessayer.');
            btn.disabled = false;
            btn.innerHTML = '✅ Valider ma demande';
        }
    }

    document.getElementById('inp_nom')?.addEventListener('keypress', e => { if (e.key === 'Enter') doSearch(); });
    document.getElementById('inp_prenom')?.addEventListener('keypress', e => { if (e.key === 'Enter') doSearch(); });

    let ticketData = null;

    const LOGO_URL = '{{ asset("images/csar-logo.png") }}';

    function telechargerTicket() {
        if (!ticketData) return;
        const { prenom, nom, direction, region, poste, montant, reference, date } = ticketData;

        const container = document.createElement('div');
        container.style.cssText = 'font-family:Arial,sans-serif;width:500px;background:white;';
        container.innerHTML = `
          <div style="background:linear-gradient(135deg,#065f46,#10b981);color:white;padding:28px 24px;text-align:center;">
            <div style="margin-bottom:10px;"><img src="${LOGO_URL}" alt="CSAR" style="width:70px;height:70px;object-fit:contain;background:white;border-radius:50%;padding:6px;"></div>
            <h1 style="font-size:1.4rem;font-weight:700;margin:0;">Avance Tabaski 2026</h1>
            <p style="font-size:0.82rem;opacity:0.85;margin:4px 0 0;">CSAR &mdash; Commissariat &agrave; la S&eacute;curit&eacute; Alimentaire et &agrave; la R&eacute;silience</p>
            <div style="margin-top:12px;background:rgba(255,255,255,0.2);border-radius:12px;padding:8px 16px;display:inline-block;">
              <div style="font-size:0.72rem;opacity:0.85;">Montant accord&eacute;</div>
              <div style="font-size:1.6rem;font-weight:800;letter-spacing:1px;">${montant}</div>
            </div>
            <br><span style="display:inline-block;background:rgba(255,255,255,0.25);border-radius:20px;padding:4px 14px;font-size:0.75rem;margin-top:10px;">&#x2705; Demande confirm&eacute;e</span>
          </div>
          <div style="padding:24px;">
            ${[
              ['Nom complet', prenom + ' ' + nom],
              ['Poste', poste],
              ['Direction / Service', direction],
              ['R&eacute;gion', region],
              ['Montant demand&eacute;', '<span style="color:#065f46;font-size:1.1rem;font-weight:700;">' + montant + '</span>'],
              ['Date d&rsquo;inscription', date],
              ['R&eacute;f&eacute;rence', '<span style="font-family:monospace;background:#f3f4f6;padding:3px 8px;border-radius:6px;">' + reference + '</span>'],
            ].map(([l,v]) => `<div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f0f0f0;">
              <span style="font-size:0.8rem;color:#6b7280;">${l}</span>
              <span style="font-size:0.88rem;font-weight:600;color:#111827;text-align:right;">${v}</span>
            </div>`).join('')}
          </div>
          <div style="background:#f9fafb;padding:16px 24px;text-align:center;font-size:0.75rem;color:#9ca3af;border-top:1px solid #e5e7eb;">
            Ce ticket fait foi de votre demande d&rsquo;avance Tabaski 2026.<br>Conservez-le et pr&eacute;sentez-le &agrave; la DRH si n&eacute;cessaire.
          </div>`;

        const btn = document.querySelector('[onclick="telechargerTicket()"]');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span><span>G&eacute;n&eacute;ration PDF...</span>';

        html2pdf().set({
            margin: 10,
            filename: `avancement-tabaski-${nom.toLowerCase().replace(/\s+/g,'-')}.pdf`,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'mm', format: 'a5', orientation: 'portrait' }
        }).from(container).save().then(() => {
            btn.disabled = false;
            btn.innerHTML = orig;
        });
    }
</script>
</body>
</html>

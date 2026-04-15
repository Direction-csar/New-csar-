<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Avance Tabaski 2026 — CSAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4">
            <span class="text-4xl">🕌</span>
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
            <p class="text-gray-500 text-sm mb-6">Entrez votre prénom et nom pour retrouver votre profil dans notre base.</p>

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
                    <div class="text-2xl font-bold text-gray-800">100k</div>
                    <div class="text-xs text-gray-500 mt-1">100 000 FCFA</div>
                </button>
                <button onclick="selectMontant('150000')" class="montant-btn border-2 border-gray-200 rounded-2xl p-4 text-center cursor-pointer hover:border-emerald-400" data-m="150000">
                    <div class="text-2xl font-bold text-gray-800">150k</div>
                    <div class="text-xs text-gray-500 mt-1">150 000 FCFA</div>
                </button>
                <button onclick="selectMontant('200000')" class="montant-btn border-2 border-gray-200 rounded-2xl p-4 text-center cursor-pointer hover:border-emerald-400" data-m="200000">
                    <div class="text-2xl font-bold text-gray-800">200k</div>
                    <div class="text-xs text-gray-500 mt-1">200 000 FCFA</div>
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
            <div id="success_msg" class="text-gray-600 mb-6"></div>
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-sm text-emerald-800">
                Votre demande a été transmise à la DRH. Vous serez contacté pour la suite de la procédure.
            </div>
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

        if (!prenom || !nom) {
            showError('error1', 'Veuillez entrer votre prénom et votre nom.');
            return;
        }

        setLoading('btn_search', true);

        try {
            const res  = await fetch('/avance-tabaski/search', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ prenom, nom }),
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
                document.getElementById('success_msg').innerHTML = `
                    <p class="font-semibold text-lg">${data.agent.prenom} ${data.agent.nom}</p>
                    <p class="text-emerald-600 font-bold text-2xl mt-2">${data.agent.montant}</p>
                    <p class="text-sm mt-2">${data.agent.direction}</p>
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
</script>
</body>
</html>

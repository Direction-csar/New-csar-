@extends('layouts.public')
@section('title', 'Remplir une demande — Portail CSAR')
@section('content')
<div class="dm-container">
    <div class="dm-back">
        <a href="{{ route('demande.create') }}"><i class="fas fa-arrow-left"></i> Choix du mode de dépôt</a>
    </div>

    <header class="dm-header">
        <h1><i class="fas fa-file-signature"></i> Remplir une demande en ligne</h1>
        <p>Portail des demandes du CSAR — suivez les étapes, vérifiez le récapitulatif puis validez. Un code de suivi unique vous sera délivré.</p>
    </header>

    <ol class="dm-steps" id="stepNav">
        <li class="active" data-nav="1"><span>1</span> Identité</li>
        <li data-nav="2"><span>2</span> Coordonnées</li>
        <li data-nav="3"><span>3</span> Demande</li>
        <li data-nav="4"><span>4</span> Localisation</li>
        <li data-nav="5"><span>5</span> Validation</li>
    </ol>

    @if($errors->any())
        <div class="dm-alert dm-alert-error">
            <i class="fas fa-triangle-exclamation"></i>
            <div><strong>Le formulaire contient des erreurs :</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="dm-alert dm-alert-error"><i class="fas fa-triangle-exclamation"></i> {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('demande.store.direct') }}" id="demandeForm" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- Étape 1 : Identité --}}
        <fieldset class="dm-step" data-step="1">
            <legend><i class="fas fa-id-card"></i> Votre identité</legend>

            <p class="dm-note"><i class="fas fa-info-circle"></i> Déposer une demande ne garantit pas son acceptation : elle est instruite selon les procédures du CSAR.</p>

            <div class="dm-field">
                <label for="demandeur_type">Vous déposez en tant que *</label>
                <select name="demandeur_type" id="demandeur_type" required>
                    @foreach(config('demande.demandeur_types') as $v => $l)
                        <option value="{{ $v }}" {{ old('demandeur_type','particulier')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                @error('demandeur_type')<div class="dm-err">{{ $message }}</div>@enderror
            </div>

            <div class="dm-field" id="structureField" style="display:none">
                <label for="structure_name">Nom de la structure *</label>
                <input type="text" name="structure_name" id="structure_name" maxlength="255" value="{{ old('structure_name') }}">
                @error('structure_name')<div class="dm-err">{{ $message }}</div>@enderror
            </div>

            <div class="dm-grid2">
                <div class="dm-field">
                    <label for="nom"><span id="nomLabel">Nom</span> *</label>
                    <input type="text" name="nom" id="nom" required maxlength="255" value="{{ old('nom') }}" autocomplete="family-name">
                    @error('nom')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
                <div class="dm-field">
                    <label for="prenom"><span id="prenomLabel">Prénom(s)</span> *</label>
                    <input type="text" name="prenom" id="prenom" required maxlength="255" value="{{ old('prenom') }}" autocomplete="given-name">
                    @error('prenom')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="dm-field" id="cniField">
                <label for="cni">Numéro de CNI <small>(chiffres uniquement — restera confidentiel)</small></label>
                <input type="text" name="cni" id="cni" maxlength="30" value="{{ old('cni') }}" inputmode="numeric">
                @error('cni')<div class="dm-err">{{ $message }}</div>@enderror
            </div>

            <div class="dm-field" id="nineaField" style="display:none">
                <label for="ninea">NINEA ou identifiant de la structure <small>(si disponible)</small></label>
                <input type="text" name="ninea" id="ninea" maxlength="30" value="{{ old('ninea') }}">
                @error('ninea')<div class="dm-err">{{ $message }}</div>@enderror
            </div>

            <div class="dm-grid2">
                <div class="dm-field">
                    <label for="type_demande">Nature de la demande *</label>
                    <select name="type_demande" id="type_demande" required>
                        <option value="">— Choisir —</option>
                        @foreach(config('demande.types') as $v => $l)
                            <option value="{{ $v }}" {{ old('type_demande', $selectedType ?? '')===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('type_demande')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
                <div class="dm-field" id="autreField" style="display:none">
                    <label for="type_demande_autre">Précisez votre demande *</label>
                    <input type="text" name="type_demande_autre" id="type_demande_autre" maxlength="255" value="{{ old('type_demande_autre') }}">
                    @error('type_demande_autre')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
            </div>

            <div id="eventFields" style="display:none">
                <div class="dm-grid3">
                    <div class="dm-field">
                        <label for="event_date">Date de l'événement</label>
                        <input type="date" name="event_date" id="event_date" min="{{ date('Y-m-d') }}" value="{{ old('event_date') }}">
                        @error('event_date')<div class="dm-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="dm-field">
                        <label for="event_location">Lieu prévu</label>
                        <input type="text" name="event_location" id="event_location" maxlength="255" value="{{ old('event_location') }}">
                        @error('event_location')<div class="dm-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="dm-field">
                        <label for="event_participants">Participants estimés</label>
                        <input type="number" name="event_participants" id="event_participants" min="1" value="{{ old('event_participants') }}">
                        @error('event_participants')<div class="dm-err">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </fieldset>

        {{-- Étape 2 : Coordonnées --}}
        <fieldset class="dm-step" data-step="2" hidden>
            <legend><i class="fas fa-address-book"></i> Vos coordonnées</legend>

            <div class="dm-grid2">
                <div class="dm-field">
                    <label for="telephone">Téléphone (Sénégal) *</label>
                    <div class="dm-tel">
                        <span class="dm-tel-prefix" aria-hidden="true">+221</span>
                        <input type="tel" name="telephone" id="telephone" required maxlength="14" value="{{ old('telephone') ? preg_replace('/^\+221/', '', old('telephone')) : '' }}" placeholder="77 000 00 00" autocomplete="tel-national" inputmode="numeric" aria-label="Numéro de téléphone (indicatif +221 inclus)">
                    </div>
                    @error('telephone')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
                <div class="dm-field">
                    <label for="email">Adresse e-mail *</label>
                    <input type="email" name="email" id="email" required maxlength="255" value="{{ old('email') }}" autocomplete="email" placeholder="exemple@domaine.sn">
                    <small class="dm-hint">Un code de vérification vous sera envoyé pour consulter votre suivi.</small>
                    @error('email')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
            </div>
        </fieldset>

        {{-- Étape 3 : Demande --}}
        <fieldset class="dm-step" data-step="3" hidden>
            <legend><i class="fas fa-folder-open"></i> Votre demande</legend>

            <div class="dm-field">
                <label for="objet">Objet de la demande *</label>
                <input type="text" name="objet" id="objet" required maxlength="255" value="{{ old('objet') }}">
                @error('objet')<div class="dm-err">{{ $message }}</div>@enderror
            </div>

            <div class="dm-field">
                <label for="description">Description détaillée *</label>
                <textarea name="description" id="description" required maxlength="2000" rows="6">{{ old('description') }}</textarea>
                <small class="dm-hint"><span id="descCount">0</span>/2000 caractères</small>
                @error('description')<div class="dm-err">{{ $message }}</div>@enderror
            </div>

            <div class="dm-field">
                <label for="justificatifs">Documents complémentaires <small>(facultatif)</small></label>
                <input type="file" name="justificatifs[]" id="justificatifs" multiple accept=".pdf,.docx,.jpg,.jpeg,.png">
                <small class="dm-hint">PDF, DOCX, JPG ou PNG — {{ config('demande.upload.max_attachments') }} fichiers, {{ round(config('demande.upload.attachment_max_kb')/1024) }} Mo chacun.</small>
                @error('justificatifs')<div class="dm-err">{{ $message }}</div>@enderror
                @error('justificatifs.*')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
        </fieldset>

        {{-- Étape 4 : Localisation --}}
        <fieldset class="dm-step" data-step="4" hidden>
            <legend><i class="fas fa-map-marker-alt"></i> Localisation de la demande</legend>
            @include('public.partials.demande-geo')
            <div class="dm-err" id="geoRequired" hidden>Capturez votre position ou indiquez votre région pour continuer.</div>
        </fieldset>

        {{-- Étape 5 : Récapitulatif --}}
        <fieldset class="dm-step" data-step="5" hidden>
            <legend><i class="fas fa-circle-check"></i> Récapitulatif et envoi</legend>
            <dl class="dm-recap" id="recap"></dl>
            <div class="dm-field">
                <label class="dm-radio">
                    <input type="checkbox" name="exactitude" value="1" required {{ old('exactitude')?'checked':'' }}>
                    Je certifie l'exactitude des informations fournies et j'accepte que le CSAR les utilise pour traiter ma demande. *
                </label>
                @error('exactitude')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
            <p class="dm-hint"><i class="fas fa-lock"></i> Données transmises de façon sécurisée — consultation du suivi par code et vérification e-mail. Urgence : +221 77 645 92 42.</p>
        </fieldset>

        <div class="dm-nav">
            <button type="button" class="dm-btn dm-btn-ghost" id="prevBtn" style="display:none"><i class="fas fa-arrow-left"></i> Précédent</button>
            <button type="button" class="dm-btn dm-btn-primary" id="nextBtn">Suivant <i class="fas fa-arrow-right"></i></button>
            <button type="submit" class="dm-btn dm-btn-primary" id="submitBtn" style="display:none"><i class="fas fa-paper-plane"></i> Envoyer ma demande</button>
        </div>
    </form>
</div>

<style>
.dm-container{max-width:760px;margin:32px auto 60px;background:#fff;border-radius:18px;box-shadow:0 10px 30px rgba(2,132,199,.08);padding:34px 28px}
.dm-back{margin-bottom:16px}.dm-back a{color:#0284c7;text-decoration:none;font-weight:500}
.dm-header h1{font-size:1.7rem;font-weight:800;color:#0d9488;margin-bottom:6px}
.dm-header p{color:#475569;margin-bottom:22px;font-size:.95rem}
.dm-steps{display:flex;list-style:none;gap:4px;padding:0;margin:0 0 24px}
.dm-steps li{flex:1;text-align:center;font-size:.8rem;color:#94a3b8;padding:8px 4px;border-bottom:3px solid #e2e8f0;cursor:default}
.dm-steps li span{display:inline-flex;width:22px;height:22px;border-radius:50%;background:#e2e8f0;color:#64748b;font-size:.75rem;align-items:center;justify-content:center;margin-right:5px}
.dm-steps li.active{color:#0d9488;border-color:#0d9488;font-weight:600}
.dm-steps li.active span{background:#0d9488;color:#fff}
.dm-steps li.done{color:#64748b;border-color:#99f6e4}.dm-steps li.done span{background:#99f6e4;color:#0f766e}
.dm-step{border:1px solid #e2e8f0;border-radius:14px;padding:22px 20px;margin-bottom:20px}
.dm-step legend{font-weight:700;color:#0f172a;padding:0 10px;font-size:1.02rem}
.dm-step legend i{color:#0284c7;margin-right:6px}
.dm-field{margin-bottom:14px}.dm-field>label{display:block;font-weight:600;color:#334155;margin-bottom:5px;font-size:.92rem}
.dm-field small{font-weight:400;color:#94a3b8}
.dm-field input[type=text],.dm-field input[type=tel],.dm-field input[type=email],.dm-field input[type=file],.dm-field select,.dm-field textarea{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.95rem;background:#f8fafc}
.dm-field input:focus,.dm-field select:focus,.dm-field textarea:focus{outline:2px solid #5eead4;border-color:#0d9488;background:#fff}
.dm-grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.dm-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.dm-tel{display:flex;gap:0}
.dm-tel-prefix{display:flex;align-items:center;gap:6px;padding:10px 12px;background:#e2e8f0;border:1px solid #cbd5e1;border-right:none;border-radius:8px 0 0 8px;font-weight:600;color:#334155;white-space:nowrap}
.dm-tel input{border-radius:0 8px 8px 0 !important}
.dm-note{display:flex;gap:10px;align-items:flex-start;background:#f0f9ff;border:1px solid #bae6fd;color:#075985;border-radius:10px;padding:10px 14px;font-size:.85rem;margin-bottom:18px}
.dm-radio-row{display:flex;gap:14px;flex-wrap:wrap}
.dm-radio{display:flex;align-items:center;gap:8px;font-weight:500;color:#334155;cursor:pointer;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px}
.dm-radio input{accent-color:#0d9488}
.dm-hint{display:block;color:#94a3b8;font-size:.8rem;margin-top:4px}
.dm-err{color:#dc2626;font-size:.82rem;margin-top:4px}
.dm-alert{display:flex;gap:12px;align-items:flex-start;border-radius:12px;padding:14px 16px;margin-bottom:18px;font-size:.9rem}
.dm-alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.dm-alert ul{margin:6px 0 0;padding-left:18px}
#map{height:280px;border-radius:12px;margin-top:12px;border:1px solid #e2e8f0}
.dm-recap{display:grid;grid-template-columns:130px 1fr;gap:6px 14px;background:#f8fafc;border-radius:12px;padding:16px;font-size:.9rem}
.dm-recap dt{font-weight:600;color:#475569}.dm-recap dd{margin:0;color:#0f172a}
.dm-nav{display:flex;justify-content:space-between;gap:12px;margin-top:8px}
.dm-btn{padding:11px 22px;border-radius:10px;font-weight:600;font-size:.95rem;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:8px}
.dm-btn-primary{background:linear-gradient(135deg,#0d9488,#0284c7);color:#fff;margin-left:auto}
.dm-btn-primary:hover{opacity:.92}
.dm-btn-ghost{background:#f1f5f9;color:#475569}
.dm-btn-geo{background:#fff;color:#0284c7;border:1.5px solid #0284c7;font-size:1rem}
.dm-btn-geo:hover{background:#f0f9ff}
@media(max-width:640px){.dm-grid2,.dm-grid3{grid-template-columns:1fr}.dm-steps li{font-size:.68rem}.dm-container{padding:22px 16px;margin:16px 12px}}
</style>

<script>
(function(){
    const steps = [...document.querySelectorAll('.dm-step')];
    const nav = [...document.querySelectorAll('#stepNav li')];
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    let current = 1;

    // Téléphone : saisie nationale uniquement, l'indicatif +221 est ajouté à l'envoi.
    const tel = document.getElementById('telephone');
    const SN_MOBILE = /^(33|70|75|76|77|78)\d{7}$/;
    function nationalDigits(v){
        let d = (v || '').replace(/\D/g, '');
        if (d.startsWith('00221')) d = d.slice(5);
        else if (d.length > 9 && d.startsWith('221')) d = d.slice(3);
        return d;
    }
    tel.addEventListener('input', () => {
        const d = nationalDigits(tel.value).slice(0, 9);
        tel.value = d.replace(/^(\d{2})(\d{0,3})(\d{0,2})(\d{0,2}).*/, (m, a, b, c, e) => [a, b, c, e].filter(Boolean).join(' '));
    });
    tel.dispatchEvent(new Event('input'));

    function show(step){
        current = step;
        steps.forEach(s => s.hidden = +s.dataset.step !== step);
        nav.forEach(n => {
            n.classList.toggle('active', +n.dataset.nav === step);
            n.classList.toggle('done', +n.dataset.nav < step);
        });
        prevBtn.style.display = step === 1 ? 'none' : '';
        nextBtn.style.display = step === steps.length ? 'none' : '';
        submitBtn.style.display = step === steps.length ? '' : 'none';
        if (step === steps.length) buildRecap();
        window.scrollTo({top:0, behavior:'smooth'});
    }

    function validateStep(step){
        const fs = steps.find(s => +s.dataset.step === step);
        let ok = true;
        fs.querySelectorAll('[required]').forEach(el => {
            if (el.offsetParent === null) return; // champ masqué (ex: CNI org)
            if (!el.value || (el.type === 'checkbox' && !el.checked)) {
                el.style.borderColor = '#dc2626'; ok = false;
            } else { el.style.borderColor = ''; }
        });
        const email = fs.querySelector('#email');
        if (email && email.value && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email.value)) { email.style.borderColor = '#dc2626'; ok = false; }
        if (fs.contains(tel) && tel.value && !SN_MOBILE.test(nationalDigits(tel.value))) { tel.style.borderColor = '#dc2626'; ok = false; }
        if (step === 4) {
            const has = window.csarGeo.hasLocation();
            document.getElementById('geoRequired').hidden = !!has;
            if (!has) { window.csarGeo.showFallback(); ok = false; }
        }
        return ok;
    }

    nextBtn.addEventListener('click', () => { if (validateStep(current)) show(current + 1); });
    prevBtn.addEventListener('click', () => show(current - 1));

    // Type de demandeur → CNI / structure / NINEA
    const dtype = document.getElementById('demandeur_type');
    const cniField = document.getElementById('cniField');
    const nineaField = document.getElementById('nineaField');
    const structureField = document.getElementById('structureField');
    const nomLabel = document.getElementById('nomLabel');
    const prenomLabel = document.getElementById('prenomLabel');
    const structureInput = document.getElementById('structure_name');
    function toggleIdent() {
        const isPart = dtype.value === 'particulier';
        cniField.style.display = isPart ? '' : 'none';
        nineaField.style.display = isPart ? 'none' : '';
        structureField.style.display = isPart ? 'none' : '';
        structureInput.required = !isPart;
        nomLabel.textContent = isPart ? 'Nom' : 'Nom du représentant';
        prenomLabel.textContent = isPart ? 'Prénom(s)' : 'Prénom(s) du représentant';
    }
    dtype.addEventListener('change', toggleIdent);
    toggleIdent();

    // Nature (catégorie workflow) → précision « autre »
    const typeSel = document.getElementById('type_demande');
    const autreField = document.getElementById('autreField');
    const eventFields = document.getElementById('eventFields');
    const eventProfiles = @json(config('demande.event_profiles'));
    function toggleNature() {
        autreField.style.display = typeSel.value === 'autre' ? '' : 'none';
        eventFields.style.display = eventProfiles.includes(dtype.value) ? '' : 'none';
        document.getElementById('type_demande_autre').required = typeSel.value === 'autre';
    }
    typeSel.addEventListener('change', toggleNature);
    dtype.addEventListener('change', toggleNature);
    toggleNature();

    // Envoi : bloquer si la localisation manque, puis assembler +221
    document.getElementById('demandeForm').addEventListener('submit', e => {
        if (!window.csarGeo.hasLocation()) { e.preventDefault(); show(4); validateStep(4); return; }
        const d = nationalDigits(tel.value);
        tel.value = d ? '+221' + d : '';
    });

    // Compteur description
    const desc = document.getElementById('description');
    desc.addEventListener('input', () => document.getElementById('descCount').textContent = desc.value.length);

    // Récapitulatif — textContent uniquement (aucune saisie injectée en HTML)
    const val = id => document.getElementById(id)?.value?.trim() || '';
    const fileNames = id => { const f = document.getElementById(id); return f && f.files.length ? [...f.files].map(x => x.name).join(', ') : ''; };
    function buildRecap(){
        const dLabels = @json(config('demande.demandeur_types'));
        const isPart = dtype.value === 'particulier';
        const rows = [
            ['Profil', dLabels[dtype.value] || ''],
            [isPart ? '' : 'Structure', isPart ? '' : val('structure_name')],
            [isPart ? 'Identité' : 'Représentant', (val('nom') + ' ' + val('prenom')).trim()],
            ['Nature', typeSel.value ? typeSel.selectedOptions[0].textContent + (typeSel.value === 'autre' ? ' : ' + val('type_demande_autre') : '') : ''],
            ['Événement', [val('event_date'), val('event_location'), val('event_participants') && val('event_participants') + ' participants'].filter(Boolean).join(' — ')],
            ['Téléphone', tel.value ? '+221 ' + tel.value : ''],
            ['E-mail', val('email')],
            ['Objet', val('objet')],
            ['Localisation', window.csarGeo.label()],
            ['Documents', fileNames('justificatifs')],
        ].filter(([k, v]) => k && v);
        const dl = document.getElementById('recap');
        dl.replaceChildren();
        rows.forEach(([k, v]) => {
            const dt = document.createElement('dt'); dt.textContent = k;
            const dd = document.createElement('dd'); dd.textContent = v;
            dl.append(dt, dd);
        });
    }

    // Si le serveur a renvoyé des erreurs : revenir à l'étape concernée
    @if($errors->any())
        const errFields = @json(array_keys($errors->toArray()));
        const stepOf = { nom:1,prenom:1,demandeur_type:1,structure_name:1,cni:1,ninea:1,type_demande:1,type_demande_autre:1,event_date:1,event_location:1,event_participants:1,telephone:2,email:2,objet:3,description:3,justificatifs:3,region:4,latitude:4,longitude:4,exactitude:5 };
        const first = errFields.map(f => stepOf[f] || 1).sort()[0];
        show(first);
    @else
        show(1);
    @endif
})();
</script>
@endsection

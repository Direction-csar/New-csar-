@extends('layouts.public')
@section('title', 'Déposer une lettre de demande — Portail CSAR')
@section('content')
<div class="dl-container">
    <div class="dm-back">
        <a href="{{ route('demande.create') }}"><i class="fas fa-arrow-left"></i> Choix du mode de dépôt</a>
    </div>

    <header class="dm-header">
        <h1><i class="fas fa-file-arrow-up"></i> Déposer une lettre de demande</h1>
        <p>Téléversez votre courrier déjà rédigé (PDF, DOCX, JPG ou PNG — {{ round(config('demande.upload.letter_max_kb')/1024) }} Mo max) et complétez les informations indispensables au traitement.</p>
    </header>

    @if($errors->any())
        <div class="dm-alert dm-alert-error">
            <i class="fas fa-triangle-exclamation"></i>
            <div><strong>Le formulaire contient des erreurs :</strong>
                <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('demande.store.lettre') }}" enctype="multipart/form-data">
        @csrf

        <fieldset class="dm-step">
            <legend><i class="fas fa-id-card"></i> Votre identité</legend>
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
            <div class="dm-field">
                <label for="type_demande">Nature de la demande *</label>
                <select name="type_demande" id="type_demande" required>
                    <option value="">— Choisir —</option>
                    @foreach(config('demande.types') as $v => $l)
                        <option value="{{ $v }}" {{ old('type_demande', $selectedType ?? '')===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
                <input type="text" name="type_demande_autre" id="type_demande_autre" maxlength="255" value="{{ old('type_demande_autre') }}" placeholder="Précisez si « autre »" style="margin-top:6px;display:none">
                @error('type_demande')<div class="dm-err">{{ $message }}</div>@enderror
                @error('type_demande_autre')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
            <div class="dm-grid2">
                <div class="dm-field">
                    <label for="nom">Nom *</label>
                    <input type="text" name="nom" id="nom" required maxlength="255" value="{{ old('nom') }}" autocomplete="family-name">
                    @error('nom')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
                <div class="dm-field">
                    <label for="prenom">Prénom(s) *</label>
                    <input type="text" name="prenom" id="prenom" required maxlength="255" value="{{ old('prenom') }}" autocomplete="given-name">
                    @error('prenom')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="dm-field" id="cniField">
                <label for="cni">Numéro de CNI <small>(particulier — restera confidentiel)</small></label>
                <input type="text" name="cni" id="cni" maxlength="30" value="{{ old('cni') }}" inputmode="numeric">
                @error('cni')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
            <div class="dm-field" id="nineaField" style="display:none">
                <label for="ninea">NINEA ou identifiant de l'organisation <small>(si disponible)</small></label>
                <input type="text" name="ninea" id="ninea" maxlength="30" value="{{ old('ninea') }}">
                @error('ninea')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
        </fieldset>

        <fieldset class="dm-step">
            <legend><i class="fas fa-address-book"></i> Vos coordonnées</legend>
            <div class="dm-grid2">
                <div class="dm-field">
                    <label for="telephone">Téléphone (Sénégal) *</label>
                    <div class="dm-tel"><span class="dm-tel-prefix" aria-hidden="true">+221</span>
                    <input type="tel" name="telephone" id="telephone" required maxlength="14" value="{{ old('telephone') ? preg_replace('/^\+221/', '', old('telephone')) : '' }}" placeholder="77 000 00 00" autocomplete="tel-national" inputmode="numeric" aria-label="Numéro de téléphone (indicatif +221 inclus)"></div>
                    @error('telephone')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
                <div class="dm-field">
                    <label for="email">Adresse e-mail *</label>
                    <input type="email" name="email" id="email" required maxlength="255" value="{{ old('email') }}" autocomplete="email" placeholder="exemple@domaine.sn">
                    @error('email')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
            </div>
        </fieldset>

        <fieldset class="dm-step">
            <legend><i class="fas fa-file-arrow-up"></i> Votre lettre</legend>
            <div class="dm-grid2">
                <div class="dm-field">
                    <label for="objet">Objet de la demande *</label>
                    <input type="text" name="objet" id="objet" required maxlength="255" value="{{ old('objet') }}">
                    @error('objet')<div class="dm-err">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="dm-field">
                <label for="lettre">Lettre de demande *</label>
                <input type="file" name="lettre" id="lettre" required accept=".pdf,.docx,.jpg,.jpeg,.png">
                <small class="dm-hint">PDF, DOCX, JPG ou PNG — {{ round(config('demande.upload.letter_max_kb')/1024) }} Mo maximum.</small>
                @error('lettre')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
            <div class="dm-field">
                <label for="justificatifs">Pièces jointes à la lettre <small>(facultatif)</small></label>
                <input type="file" name="justificatifs[]" id="justificatifs" multiple accept=".pdf,.docx,.jpg,.jpeg,.png">
                <small class="dm-hint">{{ config('demande.upload.max_attachments') }} fichiers, {{ round(config('demande.upload.attachment_max_kb')/1024) }} Mo chacun.</small>
                @error('justificatifs')<div class="dm-err">{{ $message }}</div>@enderror
                @error('justificatifs.*')<div class="dm-err">{{ $message }}</div>@enderror
            </div>
        </fieldset>

        <fieldset class="dm-step">
            <legend><i class="fas fa-map-marker-alt"></i> Localisation de la demande</legend>
            @include('public.partials.demande-geo')
            <div class="dm-err" id="geoRequired" hidden>Capturez votre position ou indiquez votre région pour envoyer la lettre.</div>
        </fieldset>

        <div class="dm-field">
            <label class="dm-radio">
                <input type="checkbox" name="exactitude" value="1" required {{ old('exactitude')?'checked':'' }}>
                Je certifie l'exactitude des informations fournies et j'accepte que le CSAR les utilise pour traiter ma demande. *
            </label>
            @error('exactitude')<div class="dm-err">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="dm-btn dm-btn-primary" style="width:100%;justify-content:center;padding:14px">
            <i class="fas fa-paper-plane"></i> Déposer ma lettre de demande
        </button>
        <p class="dm-hint" style="margin-top:10px"><i class="fas fa-lock"></i> Document stocké en zone privée — visible uniquement par les agents habilités. Urgence : +221 77 645 92 42 — contact@csar.sn</p>
    </form>
</div>

<style>
.dl-container{max-width:760px;margin:32px auto 60px;background:#fff;border-radius:18px;box-shadow:0 10px 30px rgba(2,132,199,.08);padding:34px 28px}
.dm-back{margin-bottom:16px}.dm-back a{color:#0284c7;text-decoration:none;font-weight:500}
.dm-header h1{font-size:1.7rem;font-weight:800;color:#0d9488;margin-bottom:6px}
.dm-header p{color:#475569;margin-bottom:22px;font-size:.95rem}
.dm-step{border:1px solid #e2e8f0;border-radius:14px;padding:22px 20px;margin-bottom:20px}
.dm-step legend{font-weight:700;color:#0f172a;padding:0 10px;font-size:1.02rem}
.dm-step legend i{color:#0284c7;margin-right:6px}
.dm-field{margin-bottom:14px}.dm-field>label{display:block;font-weight:600;color:#334155;margin-bottom:5px;font-size:.92rem}
.dm-field small{font-weight:400;color:#94a3b8}
.dm-field input[type=text],.dm-field input[type=tel],.dm-field input[type=email],.dm-field input[type=file],.dm-field select{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:.95rem;background:#f8fafc}
.dm-field input:focus,.dm-field select:focus{outline:2px solid #5eead4;border-color:#0d9488;background:#fff}
.dm-grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.dm-grid3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
.dm-tel{display:flex;gap:0}
.dm-tel-prefix{display:flex;align-items:center;gap:6px;padding:10px 12px;background:#e2e8f0;border:1px solid #cbd5e1;border-right:none;border-radius:8px 0 0 8px;font-weight:600;color:#334155;white-space:nowrap}
.dm-tel input{border-radius:0 8px 8px 0 !important}
.dm-radio-row{display:flex;gap:14px;flex-wrap:wrap}
.dm-radio{display:flex;align-items:center;gap:8px;font-weight:500;color:#334155;cursor:pointer;padding:8px 12px;border:1px solid #e2e8f0;border-radius:8px}
.dm-radio input{accent-color:#0d9488}
.dm-hint{display:block;color:#94a3b8;font-size:.8rem;margin-top:4px}
.dm-err{color:#dc2626;font-size:.82rem;margin-top:4px}
.dm-alert{display:flex;gap:12px;align-items:flex-start;border-radius:12px;padding:14px 16px;margin-bottom:18px;font-size:.9rem}
.dm-alert-error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.dm-alert ul{margin:6px 0 0;padding-left:18px}
.dm-btn{padding:11px 22px;border-radius:10px;font-weight:600;font-size:.95rem;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:8px}
.dm-btn-primary{background:linear-gradient(135deg,#0d9488,#0284c7);color:#fff}
.dm-btn-primary:hover{opacity:.92}
@media(max-width:640px){.dm-grid2,.dm-grid3{grid-template-columns:1fr}.dl-container{padding:22px 16px;margin:16px 12px}}
</style>

<script>
(function(){
    const tel = document.getElementById('telephone');
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

    const cniField = document.getElementById('cniField');
    const nineaField = document.getElementById('nineaField');
    const structureField = document.getElementById('structureField');
    const dtype = document.getElementById('demandeur_type');
    const natureSel = document.getElementById('type_demande');
    const natureAutre = document.getElementById('type_demande_autre');
    function toggleIdent() {
        const isPart = dtype.value === 'particulier';
        cniField.style.display = isPart ? '' : 'none';
        nineaField.style.display = isPart ? 'none' : '';
        structureField.style.display = isPart ? 'none' : '';
        document.getElementById('structure_name').required = !isPart;
    }
    dtype.addEventListener('change', toggleIdent);
    toggleIdent();
    function toggleNature() { natureAutre.style.display = natureSel.value === 'autre' ? '' : 'none'; }
    natureSel.addEventListener('change', toggleNature);
    toggleNature();
    document.querySelector('form').addEventListener('submit', e => {
        const has = window.csarGeo.hasLocation();
        document.getElementById('geoRequired').hidden = !!has;
        if (!has) {
            e.preventDefault();
            window.csarGeo.showFallback();
            document.getElementById('geoBlock').scrollIntoView({behavior: 'smooth', block: 'center'});
            return;
        }
        const d = nationalDigits(tel.value);
        tel.value = d ? '+221' + d : '';
    });
})();
</script>
@endsection

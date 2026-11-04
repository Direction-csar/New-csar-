{{-- Localisation : capture GPS obligatoire, repli sur la région si la capture est impossible. --}}
<div class="dm-geo" id="geoBlock">
    <button type="button" class="dm-btn dm-btn-geo" id="geoBtn">
        <i class="fas fa-location-crosshairs"></i> Capturer ma position actuelle
    </button>
    <div id="geoStatus" class="dm-geo-status" role="status" aria-live="polite">
        @if(old('latitude') && old('longitude'))
            <span class="ok"><i class="fas fa-circle-check"></i> Position enregistrée</span>
        @endif
    </div>
    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
    @error('latitude')<div class="dm-err">{{ $message }}</div>@enderror
    @error('longitude')<div class="dm-err">{{ $message }}</div>@enderror

    <div id="geoFallback" class="dm-geo-fallback" @if(!old('region') && !$errors->has('region') && !$errors->has('latitude')) hidden @endif>
        <label for="region">Impossible de capturer votre position ? Indiquez votre région *</label>
        <select name="region" id="region">
            <option value="">— Choisir —</option>
            @foreach(array_keys(senegal_departements()) as $reg)
                <option value="{{ $reg }}" {{ old('region')===$reg?'selected':'' }}>{{ $reg }}</option>
            @endforeach
        </select>
        @error('region')<div class="dm-err">{{ $message }}</div>@enderror
    </div>
</div>

<style>
.dm-geo{text-align:center;padding:8px 0}
.dm-btn-geo{background:#fff;color:#0284c7;border:2px solid #0284c7;font-size:1.05rem;padding:14px 26px;justify-content:center}
.dm-btn-geo:hover{background:#f0f9ff}
.dm-geo-status{margin-top:12px;font-size:.92rem;min-height:1.4em}
.dm-geo-status .ok{color:#0d9488;font-weight:600}
.dm-geo-status .ko{color:#dc2626}
.dm-geo-fallback{text-align:left;margin-top:16px;padding-top:14px;border-top:1px dashed #cbd5e1}
.dm-geo-fallback label{display:block;font-weight:600;color:#334155;margin-bottom:5px;font-size:.9rem}
.dm-geo-fallback select{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#f8fafc}
</style>

<script>
window.csarGeo = (function(){
    const btn = document.getElementById('geoBtn');
    const status = document.getElementById('geoStatus');
    const lat = document.getElementById('latitude');
    const lng = document.getElementById('longitude');
    const fallback = document.getElementById('geoFallback');
    const region = document.getElementById('region');

    function fail(msg){
        lat.value = ''; lng.value = '';
        status.innerHTML = '';
        const s = document.createElement('span'); s.className = 'ko'; s.textContent = msg;
        status.appendChild(s);
        fallback.hidden = false;
    }

    btn.addEventListener('click', () => {
        if (!('geolocation' in navigator) || !window.isSecureContext) {
            return fail('La géolocalisation n\'est pas disponible sur cet appareil.');
        }
        status.textContent = 'Recherche de votre position…';
        btn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            pos => {
                btn.disabled = false;
                lat.value = pos.coords.latitude.toFixed(6);
                lng.value = pos.coords.longitude.toFixed(6);
                status.innerHTML = '<span class="ok"><i class="fas fa-circle-check"></i> Position enregistrée</span>';
            },
            err => {
                btn.disabled = false;
                fail(err.code === 1
                    ? 'Autorisation refusée. Autorisez la localisation dans votre navigateur ou indiquez votre région.'
                    : 'Position introuvable pour le moment. Réessayez ou indiquez votre région.');
            },
            {enableHighAccuracy: true, timeout: 10000, maximumAge: 0}
        );
    });

    return {
        hasLocation: () => (lat.value && lng.value) || region.value,
        label: () => lat.value && lng.value ? 'Position GPS enregistrée' : (region.value ? 'Région : ' + region.value : '—'),
        showFallback: () => { fallback.hidden = false; },
    };
})();
</script>

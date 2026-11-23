@extends('layouts.direction-portal')

@section('title', 'Transfert inter-magasins - CSAR')
@section('page-title', 'Transfert inter-magasins')

@section('content')
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="alert alert-warning py-2">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Le transfert crée une <strong>sortie</strong> sur le magasin source et une <strong>entrée</strong> sur le magasin destination (même référence, n° de transfert unique).
        </div>

        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <form method="POST" action="{{ route('magasinier.transferts.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Magasin source <span class="text-danger">*</span></label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ number_format($w->current_stock ?? 0, 0, ',', ' ') }} sacs)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Magasin destination <span class="text-danger">*</span></label>
                    <select name="destination_warehouse_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('destination_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="movement_date" class="form-control" value="{{ old('movement_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Produit <span class="text-danger">*</span></label>
                    <select name="product" id="product" class="form-select" required>
                        <option value="">— Choisir un produit —</option>
                        @foreach($products as $p)
                        <option value="{{ $p['name'] }}" data-unit="{{ $p['unit'] }}" data-formats='@json($p['formats_kg'])' @selected(old('product') === $p['name'])>{{ $p['name'] }} ({{ $p['unit'] }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Format du contenant <span class="text-danger">*</span></label>
                    <select name="format" id="format" class="form-select" required disabled>
                        <option value="">— Choisir d'abord un produit —</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
                    <input type="text" name="label" class="form-control" value="{{ old('label') }}" placeholder="Ex : Renfort stock région de Kaolack" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nombre de contenants <span class="text-danger">*</span></label>
                    <input type="number" name="sacs" id="sacs" class="form-control" step="0.01" min="0.01" value="{{ old('sacs') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Quantité totale (<span id="unit-label">kg / L</span>) <span class="text-danger">*</span></label>
                    <input type="number" name="kg" id="kg" class="form-control" step="0.01" min="0.01" value="{{ old('kg') }}" required>
                    <div class="form-text">Calculée automatiquement : contenants × format (modifiable).</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Observation</label>
                    <textarea name="observation" class="form-control" rows="2">{{ old('observation') }}</textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary"><i class="fas fa-exchange-alt"></i> Effectuer le transfert</button>
                <a href="{{ route('magasinier.warehouses.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
@push('scripts')
<script>
(function () {
    const prod = document.getElementById('product'), fmt = document.getElementById('format'),
          sacs = document.getElementById('sacs'), kg = document.getElementById('kg'),
          unitLabel = document.getElementById('unit-label');
    const oldFormat = @json(old('format'));
    function fillFormats() {
        const opt = prod.selectedOptions[0];
        fmt.innerHTML = '';
        if (!opt || !opt.value) { fmt.disabled = true; fmt.innerHTML = '<option value="">— Choisir d'abord un produit —</option>'; unitLabel.textContent = 'kg / L'; return; }
        const unit = opt.dataset.unit, formats = JSON.parse(opt.dataset.formats || '[]');
        unitLabel.textContent = unit;
        formats.forEach(f => { const o = document.createElement('option'); o.value = f; o.textContent = f + ' ' + unit; if (String(f) === String(oldFormat)) o.selected = true; fmt.appendChild(o); });
        fmt.disabled = false; recalc();
    }
    function recalc() { const n = parseFloat(sacs.value), f = parseFloat(fmt.value); if (!isNaN(n) && !isNaN(f)) kg.value = (n * f).toFixed(2).replace(/\.00$/, ''); }
    prod.addEventListener('change', fillFormats); fmt.addEventListener('change', recalc); sacs.addEventListener('input', recalc);
    fillFormats();
})();
</script>
@endpush
@endsection

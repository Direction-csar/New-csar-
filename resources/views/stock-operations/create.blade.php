@extends('layouts.direction-portal')

@section('title', 'Nouvelle instruction de stock - CSAR')
@section('page-title', 'Nouvelle instruction de stock')

@section('content')
<div class="card border-0 shadow-sm" style="max-width: 760px;">
    <div class="card-header bg-white fw-bold"><i class="fas fa-plus me-2 text-primary"></i>Instruction DG → DTL</div>
    <div class="card-body">
        <form method="POST" action="{{ route('dg.stock-operations.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Type d'opération *</label>
                    <select name="type" id="opType" class="form-select" required>
                        <option value="entry">Entrée de stock</option>
                        <option value="exit">Sortie de stock</option>
                        <option value="transfer">Transfert entre magasins</option>
                        <option value="inventory">Inventaire physique</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Magasin *</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}{{ $w->region ? ' ('.$w->region.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6" id="destWrap" style="display:none">
                    <label class="form-label">Magasin de destination *</label>
                    <select name="destination_warehouse_id" class="form-select">
                        <option value="">— Choisir —</option>
                        @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}{{ $w->region ? ' ('.$w->region.')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Produit</label>
                    <input type="text" name="produit" class="form-control" list="products" placeholder="Ex : Riz, Huile…">
                    <datalist id="products">
                        @foreach($products as $p)<option value="{{ $p['name'] ?? '' }}">@endforeach
                    </datalist>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unité</label>
                    <select name="unite" class="form-select">
                        <option value="kg">kg</option>
                        <option value="L">L</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Format contenant</label>
                    <input type="number" name="format" class="form-control" min="1" placeholder="Ex : 50, 25">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantité demandée (contenants) *</label>
                    <input type="number" name="quantity_requested" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Quantité en kg / L (optionnel)</label>
                    <input type="number" name="quantity_requested_kg" class="form-control" step="0.01" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Instructions / consignes</label>
                    <textarea name="instructions" class="form-control" rows="4" placeholder="Objet de l'opération, justificatifs attendus, consignes particulières…"></textarea>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Transmettre à la DTL</button>
                <a href="{{ route('dg.stock-operations.index') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('opType').addEventListener('change', function () {
    document.getElementById('destWrap').style.display = this.value === 'transfer' ? '' : 'none';
});
</script>
@endsection

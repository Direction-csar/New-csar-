@extends('layouts.direction-portal')

@section('title', 'Opérations de stock - CSAR')
@section('page-title', 'Opérations de stock — circuit DG → DTL → Inspecteur → Magasinier')

@section('content')
<div class="row g-3 mb-4">
    @foreach($stats as $key => $value)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-primary">{{ $value }}</h3>
            <small class="text-muted">{{ ['soumise'=>'Soumises (DTL)','affectees'=>'Affectées','a_executer'=>'À exécuter','a_verifier'=>'À vérifier','en_cours'=>'En cours','executees'=>'Exécutées','verifiees'=>'Vérifiées'][$key] ?? $key }}</small>
        </div></div>
    </div>
    @endforeach
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-bold"><i class="fas fa-dolly me-2 text-primary"></i>Opérations</span>
        <div class="d-flex gap-2 flex-wrap">
            @if(in_array(auth()->user()->role, ['dg','admin','super_admin']))
            <a href="{{ route($direction . '.stock-operations.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nouvelle instruction</a>
            @endif
            <form method="GET" class="d-flex gap-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tous statuts</option>
                    @foreach(\App\Models\StockOperation::STATUTS as $s)
                    <option value="{{ $s }}" @selected(request('status')===$s)>{{ (new \App\Models\StockOperation(['status'=>$s]))->status_label }}</option>
                    @endforeach
                </select>
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Tous types</option>
                    @foreach(['entry'=>'Entrée','exit'=>'Sortie','transfer'=>'Transfert','inventory'=>'Inventaire'] as $t=>$lbl)
                    <option value="{{ $t }}" @selected(request('type')===$t)>{{ $lbl }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Référence</th><th>Type</th><th>Magasin</th><th>Produit</th>
                        <th class="text-end">Demandé</th><th class="text-end">Exécuté</th>
                        <th>Statut</th><th>Acteurs</th><th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($operations as $op)
                    <tr>
                        <td class="fw-semibold">{{ $op->reference }}</td>
                        <td>{{ $op->type_label }}</td>
                        <td>{{ $op->warehouse?->name }}@if($op->destination_warehouse_id) → {{ $op->destinationWarehouse?->name }}@endif</td>
                        <td>{{ $op->produit ?? '—' }}@if($op->format) [{{ $op->format }}{{ $op->unite }}]@endif</td>
                        <td class="text-end">{{ rtrim(rtrim(number_format((float)$op->quantity_requested,2,'.',''),'0'),'.') }}{{ $op->unite ? ' '.$op->unite : '' }}</td>
                        <td class="text-end">{{ $op->quantity_executed !== null ? rtrim(rtrim(number_format((float)$op->quantity_executed,2,'.',''),'0'),'.') : '—' }}</td>
                        <td><span class="badge {{ match($op->status){ 'verifiee'=>'bg-success','rejetee','annulee'=>'bg-danger','anomalie'=>'bg-warning text-dark','executee'=>'bg-info text-dark','soumise'=>'bg-secondary', default=>'bg-primary' } }}">{{ $op->status_label }}</span></td>
                        <td class="small text-muted">
                            @if($op->magasinier) M: {{ $op->magasinier->name }}<br>@endif
                            @if($op->inspector) I: {{ $op->inspector->name }}<br>@endif
                            {{ $op->creator?->name }}
                        </td>
                        <td><a href="{{ route($direction . '.stock-operations.show', $op->id) }}" class="btn btn-sm btn-outline-primary">Détail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Aucune opération.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($operations->hasPages())
    <div class="card-footer bg-white">{{ $operations->links() }}</div>
    @endif
</div>
@endsection

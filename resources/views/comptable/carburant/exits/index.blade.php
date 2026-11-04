@extends('layouts.direction-portal')

@section('title', 'Sorties de carburant - Comptable - CSAR')
@section('page-title', 'Sorties de carburant')

@section('content')
@include('comptable.carburant._nav', ['tab' => 'exits'])

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Sorties / dotations chauffeurs</strong>
        <div class="d-flex gap-1">
            <a href="{{ route('comptable.carburant.export', array_merge(['type' => 'exit'], request()->only('status','warehouse_id'))) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-csv me-1"></i>CSV</a>
            <a href="{{ route('comptable.carburant.exits.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nouveau bon de sortie</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Bon, bénéficiaire, immat., mission…" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="warehouse_id" class="form-select form-select-sm">
                    <option value="">Tous les magasins</option>
                    @foreach($warehouses as $wid => $wname)
                        <option value="{{ $wid }}" @selected(request('warehouse_id') == $wid)>{{ $wname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    @foreach(['draft' => 'Brouillon', 'authorized' => 'Autorisé', 'validated' => 'Validé', 'rejected' => 'Rejeté'] as $s => $l)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-sm btn-outline-primary w-100">Filtrer</button></div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Bon</th><th>Date</th><th>Bénéficiaire</th><th>Immat.</th><th>Carburant</th>
                        <th class="text-end">Demandé</th><th class="text-end">Délivré</th><th>Statut</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exits as $x)
                    <tr>
                        <td><code>{{ $x->exit_number }}</code></td>
                        <td>{{ $x->operation_date?->format('d/m/Y') }}</td>
                        <td>{{ $x->beneficiary_name }}</td>
                        <td>{{ $x->vehicle_plate ?? '-' }}</td>
                        <td>{{ $x->fuelLabel() }}</td>
                        <td class="text-end">{{ number_format($x->quantity_requested, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ $x->quantity_delivered !== null ? number_format($x->quantity_delivered, 0, ',', ' ') : '—' }}</td>
                        <td>@include('comptable.carburant._status', ['status' => $x->status])</td>
                        <td><a href="{{ route('comptable.carburant.exits.show', $x->id) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">Aucune sortie enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $exits->links() }}</div>
    </div>
</div>
@endsection

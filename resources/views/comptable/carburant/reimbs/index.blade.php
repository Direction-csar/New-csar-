@extends('layouts.direction-portal')

@section('title', 'Remboursements carburant - Comptable - CSAR')
@section('page-title', 'Remboursements de carburant')

@section('content')
@include('comptable.carburant._nav', ['tab' => 'reimbs'])

@if(session('success'))<div class="alert alert-success py-2">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2">{{ session('error') }}</div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Demandes de remboursement</strong>
        <small class="text-muted">Carburant avancé par un agent — sans effet sur le stock.</small>
        <div class="d-flex gap-1">
            <a href="{{ route('comptable.carburant.export', array_merge(['type' => 'reimb'], request()->only('status'))) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-file-csv me-1"></i>CSV</a>
            <a href="{{ route('comptable.carburant.reimbs.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-plus me-1"></i>Nouvelle demande</a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Référence, agent, reçu, immat.…" value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Tous les statuts</option>
                    @foreach(['draft' => 'Brouillon', 'submitted' => 'Soumis', 'in_review' => 'En instruction', 'approved' => 'Approuvé', 'rejected' => 'Rejeté', 'paid' => 'Payé', 'closed' => 'Soldé'] as $s => $l)
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
                        <th>Référence</th><th>Agent</th><th>Date dépense</th><th>Motif</th>
                        <th class="text-end">Demandé FCFA</th><th class="text-end">Payé FCFA</th><th>Statut</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reimbs as $r)
                    <tr>
                        <td><code>{{ $r->reference }}</code>
                            @if($r->duplicate_of_ref)<br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> doublon ?</small>@endif</td>
                        <td>{{ $r->agent_name }}</td>
                        <td>{{ $r->expense_date?->format('d/m/Y') }}</td>
                        <td>{{ Str::limit($r->purpose, 40) }}</td>
                        <td class="text-end">{{ number_format($r->amount_requested, 0, ',', ' ') }}</td>
                        <td class="text-end">{{ $r->amount_paid !== null ? number_format($r->amount_paid, 0, ',', ' ') : '—' }}</td>
                        <td>@include('comptable.carburant._status', ['status' => $r->status])</td>
                        <td><a href="{{ route('comptable.carburant.reimbs.show', $r->id) }}" class="btn btn-sm btn-outline-primary">Voir</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Aucune demande enregistrée.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2">{{ $reimbs->links() }}</div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', $planning->name)
@section('page-title', $planning->name)

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 fw-bold">📋 {{ $planning->name }}</h1>
                        <p class="text-muted mb-0 small">{{ $planning->event?->name }} — {{ $planning->location ?? '' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.distribution.plannings.edit', $planning->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                        <a href="{{ route('admin.distribution.plannings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Quota (kg)</div><div class="h5 fw-bold">{{ number_format($planning->planned_quota_kg, 0, ',', ' ') }}</div></div></div>
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Exécuté (kg)</div><div class="h5 fw-bold text-success">{{ number_format($planning->executed_kg, 0, ',', ' ') }}</div></div></div>
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Bénéficiaires</div><div class="h5 fw-bold text-info">{{ $planning->beneficiaries_count }}</div></div></div>
        <div class="col-md-3 col-sm-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Taux d'exécution</div><div class="h5 fw-bold {{ $planning->execution_rate >= 80 ? 'text-success' : 'text-warning' }}">{{ $planning->execution_rate }}%</div></div></div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">👥 Bénéficiaires ({{ $planning->beneficiaries_count }})</h5>
                    <a href="{{ route('admin.distribution.beneficiaries.create') }}?planning_id={{ $planning->id }}" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Ajouter</a>
                </div>
                <table class="table table-sm">
                    <thead><tr><th>Nom</th><th>Téléphone</th><th>CNI</th><th>Quantité (kg)</th><th>Statut</th><th>Validé par</th><th>Date validation</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach($planning->beneficiaries as $b)
                        <tr>
                            <td><a href="{{ route('admin.distribution.beneficiaries.show', $b->id) }}">{{ $b->full_name }}</a></td>
                            <td>{{ $b->phone ?? '—' }}</td>
                            <td>{{ $b->cni ?? '—' }}</td>
                            <td>{{ number_format($b->quantity_kg, 1, ',', ' ') }}</td>
                            <td>
                                @if($b->status === 'pending') <span class="badge bg-secondary">En attente</span>
                                @elseif($b->status === 'validated') <span class="badge bg-info">Validé</span>
                                @elseif($b->status === 'ticket_issued') <span class="badge bg-warning">Ticket émis</span>
                                @elseif($b->status === 'kit_collected') <span class="badge bg-success">Kit récupéré</span>
                                @endif
                            </td>
                            <td>{{ $b->validator?->name ?? '—' }}</td>
                            <td>{{ $b->validated_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.distribution.beneficiaries.show', $b->id) }}" class="btn btn-outline-info btn-sm py-0 px-2"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('admin.distribution.beneficiaries.edit', $b->id) }}" class="btn btn-outline-warning btn-sm py-0 px-2"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('admin.distribution.beneficiaries.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bénéficiaire ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3">🎫 Tickets ({{ $planning->tickets_count }})</h5>
                <table class="table table-sm">
                    <thead><tr><th>Code</th><th>Bénéficiaire</th><th>Statut</th><th>Émis le</th><th>Récupéré le</th><th>Scanner par</th></tr></thead>
                    <tbody>
                        @foreach($planning->beneficiaries->flatMap->tickets as $t)
                        <tr>
                            <td><a href="{{ route('admin.distribution.tickets.show', $t->id) }}">{{ $t->ticket_code }}</a></td>
                            <td>{{ $t->beneficiary?->full_name }}</td>
                            <td>
                                @if($t->status === 'issued') <span class="badge bg-info">Émis</span>
                                @elseif($t->status === 'scanned') <span class="badge bg-warning">Scanné</span>
                                @elseif($t->status === 'collected') <span class="badge bg-success">Récupéré</span>
                                @elseif($t->status === 'cancelled') <span class="badge bg-danger">Annulé</span>
                                @endif
                            </td>
                            <td>{{ $t->issued_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $t->collected_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $t->scanner?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

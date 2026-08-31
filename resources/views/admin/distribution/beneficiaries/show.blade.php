@extends('layouts.admin')

@section('title', $beneficiary->full_name)
@section('page-title', 'Détails bénéficiaire')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">👤 {{ $beneficiary->full_name }}</h1>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.distribution.beneficiaries.edit', $beneficiary->id) }}" class="btn btn-outline-warning btn-sm"><i class="fas fa-edit me-1"></i>Modifier</a>
                        <form action="{{ route('admin.distribution.beneficiaries.destroy', $beneficiary->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bénéficiaire ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Supprimer</button>
                        </form>
                        <a href="{{ route('admin.distribution.beneficiaries.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">📋 Informations</h6>
                <table class="table table-sm">
                    <tr><th class="text-muted">Planning</th><td>{{ $beneficiary->planning?->name }}</td></tr>
                    <tr><th class="text-muted">Événement</th><td>{{ $beneficiary->planning?->event?->name }}</td></tr>
                    <tr><th class="text-muted">Téléphone</th><td>{{ $beneficiary->phone ?? '—' }}</td></tr>
                    <tr><th class="text-muted">CNI</th><td>{{ $beneficiary->cni ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Adresse</th><td>{{ $beneficiary->address ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Catégorie</th><td>{{ $beneficiary->category ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Quantité (kg)</th><td>{{ number_format($beneficiary->quantity_kg, 1, ',', ' ') }}</td></tr>
                    <tr><th class="text-muted">Statut</th><td>
                        @if($beneficiary->status === 'pending') <span class="badge bg-secondary">En attente</span>
                        @elseif($beneficiary->status === 'validated') <span class="badge bg-info">Validé</span>
                        @elseif($beneficiary->status === 'ticket_issued') <span class="badge bg-warning">Ticket émis</span>
                        @elseif($beneficiary->status === 'kit_collected') <span class="badge bg-success">Kit récupéré</span>
                        @endif
                    </td></tr>
                    <tr><th class="text-muted">Validé par</th><td>{{ $beneficiary->validator?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Date validation</th><td>{{ $beneficiary->validated_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">🏷️ Vulnérabilités</h6>
                <ul class="list-unstyled">
                    <li class="mb-1">{{ $beneficiary->is_vulnerable ? '✅' : '❌' }} Vulnérable</li>
                    <li class="mb-1">{{ $beneficiary->is_pregnant ? '✅' : '❌' }} Enceinte</li>
                    <li class="mb-1">{{ $beneficiary->is_elderly ? '✅' : '❌' }} Personne âgée</li>
                    <li class="mb-1">{{ $beneficiary->is_disabled ? '✅' : '❌' }} Handicap</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">🎫 Tickets ({{ $beneficiary->tickets->count() }})</h6>
                <table class="table table-sm">
                    <thead><tr><th>Code</th><th>Statut</th><th>Émis le</th><th>Scanné le</th><th>Récupéré le</th><th>Scanner par</th><th>Lieu</th></tr></thead>
                    <tbody>
                        @foreach($beneficiary->tickets as $t)
                        <tr>
                            <td><a href="{{ route('admin.distribution.tickets.show', $t->id) }}">{{ $t->ticket_code }}</a></td>
                            <td>
                                @if($t->status === 'issued') <span class="badge bg-info">Émis</span>
                                @elseif($t->status === 'scanned') <span class="badge bg-warning">Scanné</span>
                                @elseif($t->status === 'collected') <span class="badge bg-success">Récupéré</span>
                                @elseif($t->status === 'cancelled') <span class="badge bg-danger">Annulé</span>
                                @endif
                            </td>
                            <td>{{ $t->issued_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $t->scanned_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $t->collected_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>{{ $t->scanner?->name ?? '—' }}</td>
                            <td>{{ $t->scan_location ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

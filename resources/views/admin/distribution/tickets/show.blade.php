@extends('layouts.admin')

@section('title', 'Ticket ' . $ticket->ticket_code)
@section('page-title', 'Ticket ' . $ticket->ticket_code)

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">🎫 {{ $ticket->ticket_code }}</h1>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('admin.distribution.tickets.destroy', $ticket->id) }}" onsubmit="return confirm('Supprimer définitivement ce ticket ? Le bénéficiaire repassera au statut « validé ».');">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"><i class="fas fa-trash me-1"></i>Supprimer</button>
                        </form>
                        <a href="{{ route('admin.distribution.tickets.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
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
                    <tr><th class="text-muted">Code</th><td><strong>{{ $ticket->ticket_code }}</strong></td></tr>
                    <tr><th class="text-muted">QR Token</th><td><code>{{ $ticket->qr_token }}</code></td></tr>
                    <tr><th class="text-muted">Bénéficiaire</th><td><a href="{{ route('admin.distribution.beneficiaries.show', $ticket->beneficiary->id) }}">{{ $ticket->beneficiary?->full_name }}</a></td></tr>
                    <tr><th class="text-muted">Planning</th><td>{{ $ticket->planning?->name }}</td></tr>
                    <tr><th class="text-muted">Événement</th><td>{{ $ticket->planning?->event?->name }}</td></tr>
                    <tr><th class="text-muted">Quantité (kg)</th><td>{{ number_format($ticket->beneficiary?->quantity_kg ?? 0, 1, ',', ' ') }}</td></tr>
                    <tr><th class="text-muted">Statut</th><td>
                        @if($ticket->status === 'issued') <span class="badge bg-info">Émis</span>
                        @elseif($ticket->status === 'scanned') <span class="badge bg-warning">Scanné</span>
                        @elseif($ticket->status === 'collected') <span class="badge bg-success">Récupéré</span>
                        @elseif($ticket->status === 'cancelled') <span class="badge bg-danger">Annulé</span>
                        @endif
                    </td></tr>
                    <tr><th class="text-muted">Émis le</th><td>{{ $ticket->issued_at?->format('d/m/Y H:i') }}</td></tr>
                    <tr><th class="text-muted">Scanné le</th><td>{{ $ticket->scanned_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Récupéré le</th><td>{{ $ticket->collected_at?->format('d/m/Y H:i') ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Scanner par</th><td>{{ $ticket->scanner?->name ?? '—' }}</td></tr>
                    <tr><th class="text-muted">Lieu</th><td>{{ $ticket->scan_location ?? '—' }}</td></tr>
                </table>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card-modern p-3 mb-3">
                <h6 class="fw-bold mb-3">✏️ Modifier le statut (admin)</h6>
                <form method="POST" action="{{ route('admin.distribution.tickets.update', $ticket->id) }}" class="row g-2">
                    @csrf @method('PUT')
                    <div class="col-md-5">
                        <select name="status" class="form-select form-select-sm">
                            <option value="issued" {{ $ticket->status === 'issued' ? 'selected' : '' }}>Émis (don non récupéré)</option>
                            <option value="collected" {{ $ticket->status === 'collected' ? 'selected' : '' }}>Récupéré (don servi)</option>
                            <option value="cancelled" {{ $ticket->status === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Motif / note (optionnel)" maxlength="500">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-primary btn-sm w-100"><i class="fas fa-save"></i></button>
                    </div>
                    <div class="col-12"><small class="text-muted">Chaque modification est tracée dans l'historique ci-dessous et recalcule les quantités exécutées du planning.</small></div>
                </form>
            </div>
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3">📜 Historique des scans</h6>
                <table class="table table-sm">
                    <thead><tr><th>Action</th><th>Utilisateur</th><th>Date</th><th>Notes</th></tr></thead>
                    <tbody>
                        @foreach($ticket->scanLogs as $log)
                        <tr>
                            <td><span class="badge {{ $log->action === 'collect' ? 'bg-success' : ($log->action === 'cancel' ? 'bg-danger' : 'bg-info') }}">{{ $log->action }}</span></td>
                            <td>{{ $log->user?->name }}</td>
                            <td>{{ $log->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

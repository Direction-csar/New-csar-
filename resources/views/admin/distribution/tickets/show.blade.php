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
                    <a href="{{ route('admin.distribution.tickets.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
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

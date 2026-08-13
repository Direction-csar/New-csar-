@extends('layouts.admin')

@section('title', 'Tickets')
@section('page-title', 'Tickets de retrait')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Liste des tickets</h2>
        <a href="{{ route('admin.distribution.tickets.scan') }}" class="btn btn-success">
            <i class="fas fa-qrcode me-2"></i>Scanner un ticket
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Bénéficiaire</th>
                        <th>Bon</th>
                        <th>Quantité</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $ticket)
                    <tr>
                        <td><strong>{{ $ticket->code }}</strong></td>
                        <td>{{ $ticket->bonMatiere?->beneficiaire?->name }}</td>
                        <td>{{ $ticket->bonMatiere?->numero_bon }}</td>
                        <td>{{ number_format($ticket->bonMatiere?->quantite_kg ?? 0, 2, ',', ' ') }} kg</td>
                        <td>
                            <span class="badge bg-{{ $ticket->used ? 'success' : 'info' }}">
                                {{ $ticket->used ? 'Utilisé' : 'Actif' }}
                            </span>
                        </td>
                        <td class="text-end">
                            @if(!$ticket->used)
                            <form action="{{ route('admin.distribution.tickets.reissue', $ticket) }}" method="POST" class="d-inline" onsubmit="return confirm('Rééditer ce ticket ?')">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="text" name="reason" class="form-control" placeholder="Motif" required>
                                    <button type="submit" class="btn btn-warning">Rééditer</button>
                                </div>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucun ticket enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="card-footer">
            {{ $tickets->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

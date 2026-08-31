@extends('layouts.admin')

@section('title', 'Tickets')
@section('page-title', 'Tickets de distribution')

@section('content')
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h4 mb-0 fw-bold">🎫 Tickets</h1>
                    <a href="{{ route('admin.distribution.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Retour</a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success small py-1">{{ session('success') }}</div>@endif

    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <form method="GET" class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Recherche</label>
                        <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Code ticket, QR token">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Statut</label>
                        <select class="form-select form-select-sm" name="status">
                            <option value="">Tous</option>
                            <option value="issued" {{ request('status') === 'issued' ? 'selected' : '' }}>Émis</option>
                            <option value="scanned" {{ request('status') === 'scanned' ? 'selected' : '' }}>Scanné</option>
                            <option value="collected" {{ request('status') === 'collected' ? 'selected' : '' }}>Récupéré</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                        </select>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100"><i class="fas fa-search me-1"></i>Filtrer</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <table class="table table-sm">
                    <thead><tr><th>Code</th><th>Bénéficiaire</th><th>Planning</th><th>Statut</th><th>Émis le</th><th>Récupéré le</th><th>Scanner par</th><th>Actions</th></tr></thead>
                    <tbody>
                        @forelse($tickets as $t)
                        <tr>
                            <td><a href="{{ route('admin.distribution.tickets.show', $t->id) }}"><strong>{{ $t->ticket_code }}</strong></a></td>
                            <td>{{ $t->beneficiary?->full_name }}</td>
                            <td>{{ $t->planning?->name }}</td>
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
                            <td><a href="{{ route('admin.distribution.tickets.show', $t->id) }}" class="btn btn-outline-info btn-sm py-0 px-2"><i class="fas fa-eye"></i></a></td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-3">Aucun ticket.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

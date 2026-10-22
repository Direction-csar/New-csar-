@extends('layouts.direction-portal')

@section('title', 'Dossiers approuvés - DTL - CSAR')
@section('page-title', 'Dossiers approuvés transmis par la Direction')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-primary">{{ $stats['transmises'] }}</h3>
            <small class="text-muted">Dossiers transmis</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-warning">{{ $stats['a_instruire'] }}</h3>
            <small class="text-muted">À instruire</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-info">{{ $stats['missions_actives'] }}</h3>
            <small class="text-muted">Missions actives</small>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-dark">{{ $stats['cloturees'] }}</h3>
            <small class="text-muted">Clôturées</small>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Demandeur</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Transmise le</th>
                        <th>Missions</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr>
                        <td><code>{{ $demande->tracking_code }}</code></td>
                        <td>{{ $demande->full_name ?? $demande->name }}</td>
                        <td>{{ $demande->type }}</td>
                        <td>{!! $demande->workflow_status_badge !!}</td>
                        <td>{{ $demande->dtl_transmitted_at ? \Carbon\Carbon::parse($demande->dtl_transmitted_at)->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $demande->missions->count() }}</span>
                            @if($demande->missions->whereNotIn('status', ['terminee','annulee'])->isNotEmpty())
                                <span class="badge bg-warning text-dark">en cours</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('dtl.demandes.show', $demande->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Instruire
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                            <strong>Aucun dossier transmis</strong><br>
                            <small>Les demandes approuvées par la DG apparaîtront ici.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $demandes->links() }}</div>
    </div>
</div>
@endsection

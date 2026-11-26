@extends('layouts.direction-portal')

@section('title', 'Mes missions - Inspecteur - CSAR')
@section('page-title', 'Mes missions d\'inspection')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-warning">{{ $stats['affectees'] }}</h3>
            <small class="text-muted">À prendre en charge</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-info">{{ $stats['en_cours'] }}</h3>
            <small class="text-muted">En cours</small>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm"><div class="card-body text-center">
            <h3 class="mb-0 fw-bold text-success">{{ $stats['terminees'] }}</h3>
            <small class="text-muted">Réalisées</small>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Dossier</th><th>Instructions</th><th>Affectée le</th><th>Statut</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($missions as $mission)
                    <tr>
                        <td>{{ $mission->id }}</td>
                        <td><code>{{ $mission->request?->tracking_code }}</code></td>
                        <td class="small">{{ Str::limit($mission->instructions, 80) }}</td>
                        <td>{{ $mission->created_at->format('d/m/Y') }}</td>
                        <td><span class="badge bg-{{ $mission->status === 'terminee' ? 'success' : ($mission->status === 'rapport_soumis' ? 'info' : ($mission->status === 'annulee' ? 'dark' : 'warning text-dark')) }}">{{ $mission->status_label }}</span></td>
                        <td>
                            <a href="{{ route('inspecteur.missions.show', $mission->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-clipboard-list fa-2x d-block mb-2 opacity-50"></i>
                            <strong>Aucune mission affectée</strong><br>
                            <small>Les missions assignées par le DTL apparaîtront ici.</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $missions->links() }}</div>
    </div>
</div>
@endsection

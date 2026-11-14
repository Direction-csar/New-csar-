@extends('layouts.direction-portal')

@section('title', 'Missions DTL - CSAR')
@section('page-title', 'Missions de sortie de stock (DTL)')

@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="fas fa-truck-loading me-2 text-warning"></i>Missions affectées</span>
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Actives (affectée / en cours)</option>
                <option value="affectee" {{ request('status') === 'affectee' ? 'selected' : '' }}>Affectée</option>
                <option value="en_cours" {{ request('status') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                <option value="terminee" {{ request('status') === 'terminee' ? 'selected' : '' }}>Terminée</option>
                <option value="annulee" {{ request('status') === 'annulee' ? 'selected' : '' }}>Annulée</option>
            </select>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>#</th><th>Référence demande</th><th>Demandeur</th><th>Région</th><th>Instructions</th><th>Statut</th><th>Clôturer</th></tr></thead>
            <tbody>
            @forelse($missions as $m)
                <tr>
                    <td>{{ $m['id'] }}</td>
                    <td>{{ $m['tracking_code'] ?? '—' }}</td>
                    <td>{{ $m['demandeur'] ?? '—' }}</td>
                    <td>{{ $m['region'] ?? '—' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($m['instructions'] ?? '', 60) }}</td>
                    <td><span class="badge bg-{{ $m['status'] === 'terminee' ? 'success' : ($m['status'] === 'annulee' ? 'secondary' : 'warning') }}">{{ $m['status'] }}</span></td>
                    <td>
                        @if(!in_array($m['status'], ['terminee', 'annulee']))
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reportModal{{ $m['id'] }}"><i class="fas fa-check"></i> Clôturer</button>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                </tr>
                @if(!in_array($m['status'], ['terminee', 'annulee']))
                <div class="modal fade" id="reportModal{{ $m['id'] }}" tabindex="-1">
                    <div class="modal-dialog"><div class="modal-content">
                        <form method="POST" action="{{ route('magasinier.missions.report', $m['id']) }}">
                            @csrf
                            <div class="modal-header"><h5 class="modal-title">Clôturer la mission #{{ $m['id'] }}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Référence du mouvement de sortie</label>
                                    <input type="text" name="movement_reference" class="form-control" placeholder="Ex : STK-XXXXXXX (optionnel)">
                                    <small class="text-muted">Doit correspondre à une sortie de stock enregistrée.</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Rapport d'exécution <span class="text-danger">*</span></label>
                                    <textarea name="report" class="form-control" rows="4" required minlength="10" placeholder="Décrivez l'exécution : produits livrés, quantités, observations…"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                                <button type="submit" class="btn btn-primary">Clôturer la mission</button>
                            </div>
                        </form>
                    </div></div>
                </div>
                @endif
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Aucune mission.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection

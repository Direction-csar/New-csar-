@extends('layouts.admin')

@section('title', 'Magasin : '.$warehouse->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Retour</a>
        <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="btn btn-sm btn-primary-modern btn-modern"><i class="fas fa-edit me-1"></i>Modifier</a>
    </div>

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card-modern p-3 h-100">
                <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Informations</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nom</dt><dd class="col-sm-7">{{ $warehouse->name }}</dd>
                    <dt class="col-sm-5">Région</dt><dd class="col-sm-7">{{ $warehouse->region ?? '—' }}</dd>
                    <dt class="col-sm-5">Ville</dt><dd class="col-sm-7">{{ $warehouse->city ?? '—' }}</dd>
                    <dt class="col-sm-5">Adresse</dt><dd class="col-sm-7">{{ $warehouse->address ?? '—' }}</dd>
                    <dt class="col-sm-5">Téléphone</dt><dd class="col-sm-7">{{ $warehouse->phone ?? '—' }}</dd>
                    <dt class="col-sm-5">E-mail</dt><dd class="col-sm-7">{{ $warehouse->email ?? '—' }}</dd>
                    <dt class="col-sm-5">Capacité</dt><dd class="col-sm-7">{{ $warehouse->capacity ?? '—' }}</dd>
                    <dt class="col-sm-5">Statut</dt><dd class="col-sm-7">{{ $warehouse->is_active && $warehouse->status==='active' ? 'Actif' : 'Inactif' }}</dd>
                    <dt class="col-sm-5">Créé le</dt><dd class="col-sm-7">{{ $warehouse->created_at?->format('d/m/Y') }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card-modern p-3 mb-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-users me-2 text-primary"></i>Magasiniers / responsables ({{ $warehouse->users->count() }})</h5>
                <table class="table table-sm">
                    <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th></tr></thead>
                    <tbody>
                    @forelse($warehouse->users as $u)
                        <tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->role }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">Aucun utilisateur rattaché.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-modern p-3">
                <h5 class="fw-bold mb-3"><i class="fas fa-boxes me-2 text-primary"></i>Stocks déclarés ({{ $warehouse->stocks->count() }})</h5>
                <table class="table table-sm">
                    <thead><tr><th>Produit</th><th class="text-end">Quantité</th><th>Expiration</th></tr></thead>
                    <tbody>
                    @forelse($warehouse->stocks as $s)
                        <tr><td>{{ $s->item_name ?? '—' }}</td><td class="text-end">{{ $s->quantity }}</td><td>{{ $s->expiry_date?->format('d/m/Y') ?? '—' }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">Aucun stock déclaré.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <p class="text-muted small mb-0 mt-2">Mouvements : {{ $warehouse->movements_count }} — dont {{ $warehouse->pending_count }} en attente de vérification.</p>
            </div>
        </div>
    </div>
</div>
@endsection

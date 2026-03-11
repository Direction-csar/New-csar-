@extends('layouts.admin')

@section('title', 'SIM — Détail collecte')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Collecte — {{ $simCollection->market?->name }} ({{ $simCollection->collected_on?->format('d/m/Y') }})</h1>
        <a href="{{ route('admin.sim.collections') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <p><strong>Marché :</strong> {{ $simCollection->market?->name }} ({{ $simCollection->market?->department?->region?->name }})</p>
                    <p><strong>Collecteur :</strong> {{ $simCollection->collector?->name ?? '-' }}</p>
                    <p><strong>Superviseur :</strong> {{ $simCollection->supervisor?->name ?? '-' }}</p>
                    <p><strong>Statut :</strong> <span class="badge bg-secondary">{{ $simCollection->status }}</span></p>
                    <p><strong>Dernière activité :</strong> {{ $simCollection->last_activity_at?->format('d/m/Y H:i') ?? '-' }}</p>
                    @if($simCollection->admin_notes)
                        <p><strong>Notes admin :</strong> {{ $simCollection->admin_notes }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            @if(in_array($simCollection->status, ['submitted', 'under_review']))
                <div class="card shadow">
                    <div class="card-body">
                        <form action="{{ route('admin.sim.collections.validate', $simCollection) }}" method="post" class="d-inline">
                            @csrf
                            <input type="text" name="admin_notes" class="form-control mb-2" placeholder="Notes (optionnel)">
                            <button type="submit" class="btn btn-success">Valider</button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalReject">Rejeter</button>
                    </div>
                </div>
                <div class="modal fade" id="modalReject" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('admin.sim.collections.reject', $simCollection) }}" method="post">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title">Rejeter la collecte</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <label class="form-label">Motif obligatoire *</label>
                                    <textarea name="admin_notes" class="form-control" rows="3" required></textarea>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                                    <button type="submit" class="btn btn-danger">Rejeter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            @if($simCollection->status === 'validated')
                <div class="card shadow">
                    <div class="card-body">
                        <form action="{{ route('admin.sim.collections.publish', $simCollection) }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-primary">Publier la collecte</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header">Lignes de collecte</div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Catégorie</th>
                        <th>Prix prod.</th>
                        <th>Prix détail</th>
                        <th>Prix 1/2 gros</th>
                        <th>Provenance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($simCollection->items ?? [] as $item)
                        <tr>
                            <td>{{ $item->product?->name }}</td>
                            <td>{{ $item->product?->category?->name }}</td>
                            <td>{{ $item->price_producer !== null ? number_format($item->price_producer, 0, ',', ' ') : '-' }}</td>
                            <td>{{ $item->price_retail !== null ? number_format($item->price_retail, 0, ',', ' ') : '-' }}</td>
                            <td>{{ $item->price_wholesale !== null ? number_format($item->price_wholesale, 0, ',', ' ') : '-' }}</td>
                            <td>{{ $item->provenance ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">Aucune ligne.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

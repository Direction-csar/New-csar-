@extends('layouts.direction-portal')

@section('title', 'Carburant - Comptable des matières - CSAR')
@section('page-title', 'Suivi du carburant (soldes en litres)')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <strong>Soldes carburant par produit et magasin</strong>
        <small class="text-muted d-block">Agrégation des mouvements en litres (unite = « L »), mêmes règles que l'API mobile.</small>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Produit</th>
                        <th>Magasin</th>
                        <th class="text-end">Entrées (L)</th>
                        <th class="text-end">Sorties (L)</th>
                        <th class="text-end">Solde (L)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fuel as $f)
                    <tr>
                        <td><i class="fas fa-gas-pump text-warning me-1"></i>{{ $f['produit'] }}</td>
                        <td>{{ $f['warehouse_name'] ?? '-' }}</td>
                        <td class="text-end text-success">{{ number_format($f['total_in'], 0, ',', ' ') }}</td>
                        <td class="text-end text-danger">{{ number_format($f['total_out'], 0, ',', ' ') }}</td>
                        <td class="text-end">
                            <strong class="{{ $f['balance'] <= 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($f['balance'], 0, ',', ' ') }}
                            </strong>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-5">
                        <i class="fas fa-gas-pump fa-2x d-block mb-2 opacity-50"></i>
                        <strong>Aucun mouvement de carburant enregistré</strong><br>
                        <small>Les entrées/sorties en litres saisies par les magasiniers apparaîtront ici.</small>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Campagnes')
@section('page-title', 'Campagnes de distribution')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Liste des campagnes</h2>
        <a href="{{ route('admin.distribution.campaigns.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouvelle campagne
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Dates</th>
                        <th>Stock initial (kg)</th>
                        <th>Exécuté (kg)</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                    <tr>
                        <td><strong>{{ $campaign->name }}</strong></td>
                        <td>{{ $campaign->start_date?->format('d/m/Y') }} – {{ $campaign->end_date?->format('d/m/Y') ?? '—' }}</td>
                        <td>{{ number_format($campaign->initial_stock_kg, 2, ',', ' ') }}</td>
                        <td>{{ number_format($campaign->executed_stock_kg, 2, ',', ' ') }}</td>
                        <td>
                            <span class="badge bg-{{ $campaign->status === 'active' ? 'success' : ($campaign->status === 'closed' ? 'warning' : 'secondary') }}">
                                {{ $campaign->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.distribution.campaigns.edit', $campaign) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.distribution.campaigns.destroy', $campaign) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette campagne ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Aucune campagne enregistrée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($campaigns->hasPages())
        <div class="card-footer">
            {{ $campaigns->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

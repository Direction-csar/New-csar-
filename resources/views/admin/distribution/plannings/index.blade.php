@extends('layouts.admin')

@section('title', 'Plannings')
@section('page-title', 'Plannings / volets')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Liste des plannings</h2>
        <a href="{{ route('admin.distribution.plannings.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau planning
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
                        <th>Campagne</th>
                        <th>Planning</th>
                        <th>Catégorie</th>
                        <th>Quota planifié (kg)</th>
                        <th>Exécuté (kg)</th>
                        <th>Entrepôt</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plannings as $planning)
                    <tr>
                        <td>{{ $planning->campaign?->name }}</td>
                        <td><strong>{{ $planning->name }}</strong></td>
                        <td>{{ $planning->category }}</td>
                        <td>{{ number_format($planning->planned_quota_kg, 2, ',', ' ') }}</td>
                        <td>{{ number_format($planning->executed_quota_kg, 2, ',', ' ') }}</td>
                        <td>{{ $planning->warehouse?->name ?? '—' }}</td>
                        <td>
                            <span class="badge bg-{{ $planning->status === 'active' ? 'success' : ($planning->status === 'full' ? 'warning' : 'secondary') }}">
                                {{ $planning->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.distribution.plannings.edit', $planning) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.distribution.plannings.destroy', $planning) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce planning ?')">
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
                        <td colspan="8" class="text-center text-muted py-4">Aucun planning enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($plannings->hasPages())
        <div class="card-footer">
            {{ $plannings->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Bons-matière')
@section('page-title', 'Gestion des bons-matière')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Liste des bons-matière</h2>
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
                        <th>N° bon</th>
                        <th>Bénéficiaire</th>
                        <th>Planning</th>
                        <th>Quantité (kg)</th>
                        <th>Ticket</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bons as $bon)
                    <tr>
                        <td><strong>{{ $bon->numero_bon }}</strong></td>
                        <td>{{ $bon->beneficiaire?->name }}</td>
                        <td>{{ $bon->planning?->name }}</td>
                        <td>{{ number_format($bon->quantite_kg, 2, ',', ' ') }}</td>
                        <td>{{ $bon->ticket?->code }}</td>
                        <td>
                            <span class="badge bg-{{ $bon->statut === 'livre' ? 'success' : ($bon->statut === 'annule' ? 'danger' : 'warning') }}">
                                {{ $bon->statut }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.distribution.bon-matieres.show', $bon) }}" class="btn btn-sm btn-outline-info me-1">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if($bon->statut !== 'annule')
                            <form action="{{ route('admin.distribution.bon-matieres.cancel', $bon) }}" method="POST" class="d-inline" onsubmit="return confirm('Annuler ce bon et rétablir le stock ?')">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-ban"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Aucun bon-matière enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($bons->hasPages())
        <div class="card-footer">
            {{ $bons->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

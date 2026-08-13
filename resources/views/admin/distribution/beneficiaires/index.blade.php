@extends('layouts.admin')

@section('title', 'Bénéficiaires')
@section('page-title', 'Gestion des bénéficiaires')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Liste des bénéficiaires</h2>
        <a href="{{ route('admin.distribution.beneficiaires.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nouveau bénéficiaire
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
                        <th>Téléphone</th>
                        <th>CNI</th>
                        <th>Planning</th>
                        <th>Catégorie</th>
                        <th>Bon / Ticket</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($beneficiaires as $beneficiaire)
                    <tr>
                        <td><strong>{{ $beneficiaire->name }}</strong></td>
                        <td>{{ $beneficiaire->phone ?? '—' }}</td>
                        <td>{{ $beneficiaire->cni ?? '—' }}</td>
                        <td>{{ $beneficiaire->planning?->name }}</td>
                        <td>{{ $beneficiaire->category }}</td>
                        <td>
                            @if($bon = $beneficiaire->bonMatieres->first())
                                <small>{{ $bon->numero_bon }}</small>
                                <br>
                                <small class="text-muted">{{ $bon->ticket?->code }}</small>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $beneficiaire->status === 'active' ? 'success' : 'danger' }}">
                                {{ $beneficiaire->status }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.distribution.beneficiaires.edit', $beneficiaire) }}" class="btn btn-sm btn-outline-primary me-1">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.distribution.beneficiaires.destroy', $beneficiaire) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce bénéficiaire ?')">
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
                        <td colspan="8" class="text-center text-muted py-4">Aucun bénéficiaire enregistré.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($beneficiaires->hasPages())
        <div class="card-footer">
            {{ $beneficiaires->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

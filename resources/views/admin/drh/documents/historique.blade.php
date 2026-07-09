@extends('layouts.drh-portal')

@section('title', 'Historique Documents RH')
@section('page-title', 'Historique Documents RH')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="fas fa-history me-2 text-primary"></i>Historique des documents générés</h4>
    <div class="d-flex gap-2">
        <form action="{{ route('admin.drh.documents.destroyAll') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer TOUT l\'historique des documents RH ? Cette action est irréversible.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-trash-alt me-1"></i>Supprimer tout l'historique
            </button>
        </form>
        <a href="{{ route('admin.drh.documents') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour aux documents
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Agent</th>
                    <th>Généré par</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>{{ $doc->created_at?->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $types[$doc->type] ?? $doc->type }}</span></td>
                        <td>{{ $doc->personnel?->prenoms_nom ?? 'N/A' }}</td>
                        <td>{{ $doc->creator?->name ?? 'N/A' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.drh.documents.export', $doc) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-download"></i>
                            </a>
                            <form action="{{ route('admin.drh.documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce document ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Aucun document enregistré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
        <div class="card-footer">{{ $documents->links() }}</div>
    @endif
</div>

@endsection

@extends('layouts.drh-portal')

@section('title', 'Actualisation des rémunérations')
@section('page-title', 'Actualisation des rémunérations')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="mb-3"><i class="fas fa-file-import me-2 text-primary"></i>Importer le fichier TXT</h5>
                <p class="text-muted small mb-3">
                    Export TXT du « Tableau comparatif des rémunérations ». Les agents sont reconnus par leur
                    <strong>matricule solde</strong>. Aucune fiche n'est modifiée avant votre validation.
                </p>
                <form action="{{ route('admin.drh.remunerations.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="fichier" accept=".txt,text/plain" class="form-control @error('fichier') is-invalid @enderror" required>
                    @error('fichier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <button type="submit" class="btn btn-primary mt-3 w-100">
                        <i class="fas fa-search me-1"></i> Analyser et prévisualiser
                    </button>
                </form>
                <hr>
                <p class="small text-muted mb-0">
                    Champs actualisés : poste, catégorie, salaire de base (catégoriel), sursalaire, indemnité de fonction,
                    indemnité de transport, salaire brut et net à payer. Les données personnelles (CIN, adresse, filiation…)
                    ne sont ni lues ni modifiées.
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white"><strong>Historique des imports</strong></div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr><th>Date</th><th>Fichier</th><th>Agents</th><th>Statut</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($imports as $import)
                            <tr>
                                <td class="small">{{ $import->created_at->format('d/m/Y H:i') }}<br><span class="text-muted">{{ $import->creator?->name }}</span></td>
                                <td class="small text-break">{{ $import->filename }}</td>
                                <td>{{ $import->summary['total'] ?? 0 }}</td>
                                <td>
                                    @if($import->isApplied())
                                        <span class="badge bg-success">Enregistré ({{ $import->summary['updated'] ?? 0 }})</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Prévisualisation</span>
                                    @endif
                                </td>
                                <td class="text-end"><a href="{{ route('admin.drh.remunerations.show', $import) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucun import pour le moment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($imports->hasPages())<div class="card-footer">{{ $imports->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.drh-portal')

@section('title', 'Gestion du Personnel — DRH')
@section('page-title', 'Gestion du Personnel')

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Liste du Personnel</h5>
                <a href="{{ route('admin.drh.personnel.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Ajouter du Personnel
                </a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="personnelTable">
                    <thead class="table-light">
                        <tr>
                            <th width="60">Photo</th>
                            <th>Matricule</th>
                            <th>Nom & Prénoms</th>
                            <th>Poste</th>
                            <th>Direction</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th width="150" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($personnel as $person)
                            <tr>
                                <td>
                                    @if($person->photo_personnelle && $person->photo_url)
                                        <img src="{{ $person->photo_url }}" alt="{{ $person->prenoms_nom }}"
                                             class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                             style="width: 40px; height: 40px; font-size: 14px; font-weight: bold;">
                                            {{ substr($person->prenoms_nom, 0, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td><strong>{{ $person->matricule }}</strong></td>
                                <td>{{ $person->prenoms_nom }}</td>
                                <td><span class="badge bg-info">{{ $person->poste_actuel }}</span></td>
                                <td>{{ $person->direction_service }}</td>
                                <td><small>{{ $person->email }}</small></td>
                                <td><small>{{ $person->contact_telephonique }}</small></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.drh.personnel.show', $person->id) }}"
                                           class="btn btn-info" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.drh.personnel.edit', $person->id) }}"
                                           class="btn btn-warning" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('admin.drh.personnel.destroy', $person->id) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce personnel ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucun personnel enregistré pour le moment.</p>
                                    <a href="{{ route('admin.drh.personnel.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Ajouter du Personnel
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $personnel->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

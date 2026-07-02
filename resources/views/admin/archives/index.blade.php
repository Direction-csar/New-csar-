@extends('layouts.admin')

@section('title', 'Gestion Archives')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-archive text-danger"></i> Gestion des Archives</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.archives.logs') }}" class="btn btn-outline-info">
                <i class="fas fa-history"></i> Logs d'accès
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Stats --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ $stats['total'] }}</h5>
                            <small>Documents totaux</small>
                        </div>
                        <i class="fas fa-archive fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ $stats['active'] }}</h5>
                            <small>Actifs</small>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-0">{{ $stats['deleted'] }}</h5>
                            <small>Supprimés</small>
                        </div>
                        <i class="fas fa-trash fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2">
                <div class="col-md-2">
                    <select name="direction" class="form-select" onchange="this.form.submit()">
                        <option value="">Toutes directions</option>
                        @foreach($directions as $d)
                            <option value="{{ $d }}" {{ request('direction') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="annee" class="form-select" onchange="this.form.submit()">
                        <option value="">Toutes années</option>
                        @foreach($annees as $a)
                            <option value="{{ $a }}" {{ request('annee') == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Tous statuts</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actifs</option>
                        <option value="deleted" {{ request('status') == 'deleted' ? 'selected' : '' }}>Supprimés</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="search" name="q" class="form-control" placeholder="Rechercher..." value="{{ request('q') }}">
                        <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="?" class="btn btn-outline-danger w-100"><i class="fas fa-times"></i> Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Référence</th>
                        <th>Direction</th>
                        <th>Titre</th>
                        <th>Année</th>
                        <th>Auteur</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($archives as $doc)
                        <tr class="{{ $doc->deleted_at ? 'table-danger' : '' }}">
                            <td><code class="small">{{ $doc->reference }}</code></td>
                            <td><span class="badge bg-secondary">{{ $doc->direction }}</span></td>
                            <td>
                                <strong>{{ $doc->title }}</strong>
                            </td>
                            <td>{{ $doc->annee }}</td>
                            <td>{{ $doc->creator?->name ?? '—' }}</td>
                            <td>
                                @if($doc->deleted_at)
                                    <span class="badge bg-danger">Supprimé</span>
                                    <small class="text-muted d-block">{{ $doc->deleted_at->format('d/m/Y') }}</small>
                                @else
                                    <span class="badge bg-success">Actif</span>
                                @endif
                            </td>
                            <td>
                                @if($doc->deleted_at)
                                    <form method="POST" action="{{ route('admin.archives.restore', $doc) }}" class="d-inline" onsubmit="return confirm('Restaurer ce document ?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" title="Restaurer">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.archives.destroy', $doc) }}" class="d-inline" onsubmit="return confirm('SUPPRIMER DÉFINITIVEMENT ? Cette action est irréversible.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer définitivement">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Aucun document trouvé</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $archives->links() }}
        </div>
    </div>
</div>
@endsection

@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Gestion de la Galerie')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-images me-2"></i>
                Gestion de la Galerie
            </h1>
            <p class="text-muted">Gérez les images de la galerie publique</p>
        </div>
        <div>
            <a href="{{ route('public.galerie') }}" class="btn btn-info me-2" target="_blank">
                <i class="fas fa-external-link-alt me-2"></i>Voir la page publique
            </a>
            <button class="btn btn-primary me-2" onclick="toggleView()">
                <i class="fas fa-th me-2"></i><span id="view-toggle-text">Liste</span>
            </button>
            <a href="{{ route('admin.galerie.create') }}" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Ajouter une Image
            </a>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Images
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-images fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Images Actives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['actif'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Images Inactives
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['inactif'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-eye-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Taille Totale
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['taille_totale'], 2) }} MB</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtres et options d'affichage</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.galerie.index') }}" method="GET">
                <div class="form-row">
                    <div class="col-md-4 mb-3">
                        <label for="search">🔍 Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher par titre...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="categorie">🏷️ Catégorie</label>
                        <select class="form-control" id="categorie" name="categorie">
                            <option value="">Toutes les catégories</option>
                            <option value="Action humanitaire" {{ request('categorie') == 'Action humanitaire' ? 'selected' : '' }}>Action humanitaire</option>
                            <option value="Entrepôt" {{ request('categorie') == 'Entrepôt' ? 'selected' : '' }}>Entrepôt</option>
                            <option value="Événements" {{ request('categorie') == 'Événements' ? 'selected' : '' }}>Événements</option>
                            <option value="Personnel" {{ request('categorie') == 'Personnel' ? 'selected' : '' }}>Personnel</option>
                            <option value="Infrastructure" {{ request('categorie') == 'Infrastructure' ? 'selected' : '' }}>Infrastructure</option>
                            <option value="Autre" {{ request('categorie') == 'Autre' ? 'selected' : '' }}>Autre</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sort">📊 Trier par</label>
                        <select class="form-control" id="sort" name="sort">
                            <option value="recent">Plus récentes</option>
                            <option value="oldest">Plus anciennes</option>
                            <option value="name">Nom (A-Z)</option>
                            <option value="size">Taille</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrer</button>
                        <a href="{{ route('admin.galerie.index') }}" class="btn btn-secondary">Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Vue Grille -->
    <div id="grid-view" class="d-none">
        <div class="row">
            @forelse ($images as $image)
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="position-relative">
                            <img src="{{ $image->image_url }}" 
                                 class="card-img-top" 
                                 alt="{{ $image->title }}"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge {{ $image->status == 'active' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ucfirst($image->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">{{ $image->title ?: 'Sans titre' }}</h6>
                            <p class="card-text text-muted small flex-grow-1">
                                {{ Str::limit($image->description, 80) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">{{ $image->file_name }}</small>
                                <small class="text-muted">{{ $image->formatted_file_size }}</small>
                            </div>
                            <div class="mt-2">
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.galerie.show', $image->id) }}" class="btn btn-info btn-sm" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.galerie.edit', $image->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-{{ $image->status == 'active' ? 'secondary' : 'success' }} btn-sm" 
                                            title="{{ $image->status == 'active' ? 'Désactiver' : 'Activer' }}"
                                            onclick="toggleImageStatus({{ $image->id }})">
                                        <i class="fas fa-{{ $image->status == 'active' ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                    <form action="{{ route('admin.galerie.destroy', $image->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-images fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Aucune image trouvée</h5>
                        <p class="text-muted">Commencez par ajouter des images à votre galerie.</p>
                        <a href="{{ route('admin.galerie.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Ajouter une Image
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Vue Liste -->
    <div id="list-view">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Toutes les Images</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Titre</th>
                                <th>Catégorie</th>
                                <th>Statut</th>
                                <th>Taille</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($images as $image)
                                <tr>
                                    <td>
                                        <img src="{{ $image->image_url }}" 
                                             alt="{{ $image->title }}"
                                             class="img-thumbnail" 
                                             style="width: 60px; height: 60px; object-fit: cover;"
                                             onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                                    </td>
                                    <td>
                                        <strong>{{ $image->title ?: 'Sans titre' }}</strong>
                                        @if($image->description)
                                            <br><small class="text-muted">{{ Str::limit($image->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($image->category) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $image->status == 'active' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ ucfirst($image->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $image->formatted_file_size }}</td>
                                    <td>{{ $image->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.galerie.show', $image->id) }}" class="btn btn-info btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.galerie.edit', $image->id) }}" class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-{{ $image->status == 'active' ? 'secondary' : 'success' }} btn-sm" 
                                                    title="{{ $image->status == 'active' ? 'Désactiver' : 'Activer' }}"
                                                    onclick="toggleImageStatus({{ $image->id }})">
                                                <i class="fas fa-{{ $image->status == 'active' ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                            <form action="{{ route('admin.galerie.destroy', $image->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Aucune image trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentView = 'list';

function toggleView() {
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');
    const toggleText = document.getElementById('view-toggle-text');
    
    if (currentView === 'list') {
        gridView.classList.remove('d-none');
        listView.classList.add('d-none');
        toggleText.textContent = 'Grille';
        currentView = 'grid';
    } else {
        gridView.classList.add('d-none');
        listView.classList.remove('d-none');
        toggleText.textContent = 'Liste';
        currentView = 'list';
    }
}

function toggleImageStatus(imageId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cette image ?')) {
        fetch(`/admin/galerie/${imageId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la mise à jour du statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la mise à jour du statut');
        });
    }
}
</script>
@endsection

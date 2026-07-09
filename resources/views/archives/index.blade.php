@extends($layout ?? 'layouts.admin', ['direction' => $direction])

@section('title', 'Archives ' . $direction)

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-archive text-primary"></i> Archives {{ $direction }}</h2>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload"></i> Importer un document
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        {{-- Sidebar Dossiers --}}
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-folder"></i> Dossiers
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <a href="?" class="list-group-item list-group-item-action {{ request('folder_id') ? '' : 'active' }}">
                            <i class="fas fa-folder-open text-warning"></i> Tous les documents
                        </a>
                        @foreach($folders as $folder)
                            <li class="list-group-item list-group-item-action p-0 {{ request('folder_id') == $folder->id ? 'active' : '' }}">
                                <div class="d-flex justify-content-between align-items-center px-3 py-2">
                                    <a href="?folder_id={{ $folder->id }}" class="text-decoration-none {{ request('folder_id') == $folder->id ? 'text-white' : 'text-dark' }}">
                                        <i class="fas fa-folder text-warning"></i> {{ $folder->name }}
                                    </a>
                                    @if(auth()->user() && in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-link text-success" title="Nouveau sous-dossier" data-bs-toggle="modal" data-bs-target="#createFolderModal" data-parent-id="{{ $folder->id }}" onclick="setCreateFolderParent({{ $folder->id }})">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-link text-warning" title="Renommer" data-bs-toggle="modal" data-bs-target="#editFolderModal{{ $folder->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" action="{{ route('folders.destroy', $folder) }}" class="d-inline" onsubmit="return confirm('Supprimer ce dossier ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-link text-danger" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                                @if($folder->children->count())
                                    <ul class="list-unstyled ms-4 pb-2">
                                        @foreach($folder->children as $child)
                                            <li class="d-flex justify-content-between align-items-center py-1">
                                                <a href="?folder_id={{ $child->id }}" class="text-decoration-none {{ request('folder_id') == $child->id ? 'fw-bold text-primary' : 'text-muted' }}">
                                                    <i class="fas fa-folder text-warning small"></i> {{ $child->name }}
                                                </a>
                                                @if(auth()->user() && in_array(auth()->user()->role, ['admin', 'super_admin'], true))
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-link text-success" title="Nouveau sous-dossier" data-bs-toggle="modal" data-bs-target="#createFolderModal" data-parent-id="{{ $child->id }}" onclick="setCreateFolderParent({{ $child->id }})">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-link text-warning" title="Renommer" data-bs-toggle="modal" data-bs-target="#editFolderModal{{ $child->id }}">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" action="{{ route('folders.destroy', $child) }}" class="d-inline" onsubmit="return confirm('Supprimer ce sous-dossier ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-link text-danger" title="Supprimer">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    <button class="btn btn-outline-primary btn-sm w-100 mt-3" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="fas fa-plus"></i> Nouveau dossier
                    </button>
                </div>
            </div>
        </div>

        {{-- Contenu principal --}}
        <div class="col-md-9">
            {{-- Filtres --}}
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" id="archiveFilterForm" class="row g-2">
                        <div class="col-md-3">
                            <select name="annee" class="form-select" onchange="this.form.submit()">
                                <option value="">Toutes les années</option>
                                @foreach($annees as $a)
                                    <option value="{{ $a }}" {{ request('annee') == $a ? 'selected' : '' }}>{{ $a }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="search" name="q" class="form-control" placeholder="Rechercher (titre, référence, description...)" value="{{ request('q') }}">
                                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <a href="?" class="btn btn-outline-danger w-100"><i class="fas fa-times"></i> Réinitialiser</a>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-link text-decoration-none" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-sliders-h"></i> Mode filtre avancé
                            </button>
                        </div>
                        <div class="collapse {{ request('date_from') || request('date_to') || request('mime_type') || request('created_by') || request('reference') ? 'show' : '' }}" id="advancedFilters">
                            <div class="row g-2 mt-1 border-top pt-2">
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Référence</label>
                                    <input type="text" name="reference" class="form-control form-control-sm" value="{{ request('reference') }}" placeholder="Référence">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Date début</label>
                                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Date fin</label>
                                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Type de fichier</label>
                                    <select name="mime_type" class="form-select form-select-sm">
                                        <option value="">Tous</option>
                                        @foreach($mimeTypes as $mt)
                                            <option value="{{ $mt }}" {{ request('mime_type') == $mt ? 'selected' : '' }}>{{ $mt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1">Ajouté par</label>
                                    <select name="created_by" class="form-select form-select-sm">
                                        <option value="">Tous</option>
                                        @foreach($creators as $creator)
                                            <option value="{{ $creator->id }}" {{ request('created_by') == $creator->id ? 'selected' : '' }}>{{ $creator->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-filter"></i> Appliquer les filtres</button>
                                </div>
                            </div>
                        </div>
                        @if(request('folder_id'))
                            <input type="hidden" name="folder_id" value="{{ request('folder_id') }}">
                        @endif
                    </form>
                </div>
            </div>

            {{-- Tableau documents --}}
            <div class="card shadow-sm">
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Référence</th>
                                <th>Titre</th>
                                <th>Année</th>
                                <th>Dossier</th>
                                <th>Taille</th>
                                <th>Pages</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($archives as $doc)
                                <tr>
                                    <td><code class="small">{{ $doc->reference }}</code></td>
                                    <td>
                                        <strong>{{ $doc->title }}</strong>
                                        @if($doc->description)
                                            <div class="text-muted small">{{ Str::limit($doc->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $doc->annee }}</td>
                                    <td>{{ $doc->folder?->name ?? '-' }}</td>
                                    <td>{{ number_format($doc->file_size / 1024, 2) }} KB</td>
                                    <td>{{ $doc->page_count ?? '-' }}</td>
                                    <td>
                                        <a href="{{ route('archives.' . strtolower($direction) . '.show', $doc) }}" class="btn btn-sm btn-info" title="Ouvrir / Visualiser">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('archives.' . strtolower($direction) . '.download', $doc) }}" class="btn btn-sm btn-secondary" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @can('update', $doc)
                                        <button type="button" class="btn btn-sm btn-warning" title="Modifier"
                                            data-bs-toggle="modal" data-bs-target="#editModal{{ $doc->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endcan
                                        @can('delete', $doc)
                                        <form method="POST" action="{{ route('archives.' . strtolower($direction) . '.destroy', $doc) }}" class="d-inline"
                                            onsubmit="return confirm('Supprimer définitivement ce document ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
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
    </div>
</div>

{{-- Modals Modification (un par document, en dehors du tableau) --}}
@foreach($archives as $doc)
    @can('update', $doc)
    <div class="modal fade" id="editModal{{ $doc->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('archives.' . strtolower($direction) . '.update', $doc) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier le document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Titre <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ $doc->title }}" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nom du fichier <span class="text-danger">*</span></label>
                            <input type="text" name="file_name" class="form-control" value="{{ $doc->file_name }}" required maxlength="255">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $doc->description }}</textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Année <span class="text-danger">*</span></label>
                                <input type="number" name="annee" class="form-control" value="{{ $doc->annee }}" min="2000" max="{{ date('Y') + 1 }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Dossier</label>
                                <select name="folder_id" class="form-select">
                                    <option value="">-- Racine --</option>
                                    @foreach($folders as $f)
                                        <option value="{{ $f->id }}" {{ $doc->folder_id == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                        @foreach($f->children as $c)
                                            <option value="{{ $c->id }}" {{ $doc->folder_id == $c->id ? 'selected' : '' }}>— {{ $c->name }}</option>
                                        @endforeach
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endforeach

{{-- Modals Modification Dossiers --}}
@if(auth()->user() && in_array(auth()->user()->role, ['admin', 'super_admin'], true))
    @foreach($folders as $folder)
        @include('archives.partials.edit-folder-modal', ['folder' => $folder])
        @foreach($folder->children as $child)
            @include('archives.partials.edit-folder-modal', ['folder' => $child])
        @endforeach
    @endforeach
@endif

{{-- Modal Upload --}}
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('archives.' . strtolower($direction) . '.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload"></i> Importer un document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Année <span class="text-danger">*</span></label>
                            <input type="number" name="annee" class="form-control" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 1 }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dossier</label>
                            <select name="folder_id" class="form-select">
                                <option value="">-- Racine --</option>
                                @foreach($folders as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @foreach($f->children as $c)
                                        <option value="{{ $c->id }}">— {{ $c->name }}</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fichier <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                        <div class="form-text">Formats acceptés : PDF, Word, Excel, PowerPoint, JPG, PNG. Max 50 Mo.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Nouveau Dossier --}}
<div class="modal fade" id="createFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('folders.store') }}">
                @csrf
                <input type="hidden" name="direction" value="{{ $direction }}">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-folder-plus"></i> Nouveau dossier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom du dossier <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dossier parent</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- Aucun (racine) --</option>
                            @foreach($allFolders as $f)
                                @php
                                    $prefix = '';
                                    $current = $f;
                                    while ($current->parent_id) {
                                        $prefix .= '— ';
                                        $current = $allFolders->firstWhere('id', $current->parent_id);
                                        if (!$current) break;
                                    }
                                @endphp
                                <option value="{{ $f->id }}">{{ $prefix }}{{ $f->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
function setCreateFolderParent(parentId) {
    const select = document.querySelector('#createFolderModal select[name="parent_id"]');
    if (select) {
        select.value = parentId;
    }
}
</script>
@endpush

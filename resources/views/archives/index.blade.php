@extends('layouts.admin')

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
                            <a href="?folder_id={{ $folder->id }}" class="list-group-item list-group-item-action {{ request('folder_id') == $folder->id ? 'active' : '' }}">
                                <i class="fas fa-folder text-warning"></i> {{ $folder->name }}
                                @if($folder->children->count())
                                    <ul class="list-unstyled ms-3 mt-1">
                                        @foreach($folder->children as $child)
                                            <li>
                                                <a href="?folder_id={{ $child->id }}" class="text-decoration-none {{ request('folder_id') == $child->id ? 'fw-bold text-primary' : 'text-muted' }}">
                                                    <i class="fas fa-folder text-warning small"></i> {{ $child->name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </a>
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
                    <form method="GET" class="row g-2">
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
                            @foreach($folders as $f)
                                <option value="{{ $f->id }}">{{ $f->name }}</option>
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
@endsection

@extends('layouts.ctc')

@section('title', 'Modifier l\'Actualité - CTC')

@section('content')
<div class="container-fluid">
    <!-- Header CTC -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">
                <i class="fas fa-edit me-2"></i>
                Modifier l'Actualité
            </h1>
            <p class="text-white-50 mb-0">Modifier l'actualité : {{ $actualite->title ?? $actualite->titre }}</p>
        </div>
        <div>
            <a href="{{ route('ctc.actualites.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: var(--ctc-primary); color: white;">
                    <h6 class="m-0 font-weight-bold">Informations de l'actualité</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('ctc.actualites.update', $actualite->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Titre de l'actualité *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $actualite->title ?? $actualite->titre) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="category" class="form-label">Catégorie *</label>
                                    <select class="form-control @error('category') is-invalid @enderror" 
                                            id="category" name="category" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        <option value="actualite" {{ old('category', $actualite->category) == 'actualite' ? 'selected' : '' }}>Actualité</option>
                                        <option value="mission" {{ old('category', $actualite->category) == 'mission' ? 'selected' : '' }}>Mission</option>
                                        <option value="communication" {{ old('category', $actualite->category) == 'communication' ? 'selected' : '' }}>Communication</option>
                                        <option value="formation" {{ old('category', $actualite->category) == 'formation' ? 'selected' : '' }}>Formation</option>
                                        <option value="evenement" {{ old('category', $actualite->category) == 'evenement' ? 'selected' : '' }}>Événement</option>
                                        <option value="publication" {{ old('category', $actualite->category) == 'publication' ? 'selected' : '' }}>Publication</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Statut *</label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Sélectionner un statut</option>
                                        <option value="draft" {{ old('status', $actualite->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                        <option value="published" {{ old('status', $actualite->status) == 'published' ? 'selected' : '' }}>Publié</option>
                                        <option value="pending" {{ old('status', $actualite->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="excerpt" class="form-label">Extrait / Résumé</label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" name="excerpt" rows="3">{{ old('excerpt', $actualite->excerpt) }}</textarea>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="content" class="form-label">Contenu de l'actualité *</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="15" required>{{ old('content', $actualite->content ?? $actualite->contenu) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $actualite->is_featured ?? $actualite->featured ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Mettre en avant (À la une)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('ctc.actualites.index') }}" class="btn btn-outline-light me-2">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3" style="background: var(--ctc-secondary); color: white;">
                    <h6 class="m-0 font-weight-bold">Informations</h6>
                </div>
                <div class="card-body">
                    <p><strong>ID :</strong> {{ $actualite->id }}</p>
                    <p><strong>Créé le :</strong> {{ $actualite->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Dernière modification :</strong> {{ $actualite->updated_at->format('d/m/Y H:i') }}</p>
                    
                    <hr>
                    
                    <h6>Actions rapides :</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('ctc.actualites.show', $actualite->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Voir l'actualité
                        </a>
                        <form action="{{ route('ctc.actualites.destroy', $actualite->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette actualité ?')">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#content',
    height: 500,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | blocks | ' +
        'bold italic underline forecolor backcolor | alignleft aligncenter ' +
        'alignright alignjustify | bullist numlist outdent indent | ' +
        'image media link | removeformat | fullscreen | help',
    image_title: true,
    automatic_uploads: true,
    file_picker_types: 'image',
    images_upload_url: '{{ route("ctc.actualites.upload-image") }}',
    images_upload_handler: function (blobInfo, progress) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('_token', '{{ csrf_token() }}');
            fetch('{{ route("ctc.actualites.upload-image") }}', {
                method: 'POST',
                body: formData,
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.location) resolve(data.location);
                else reject('Erreur upload: ' + JSON.stringify(data));
            })
            .catch(err => reject('Erreur réseau: ' + err));
        });
    },
    content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif; font-size: 14px; } img { max-width: 100%; height: auto; }',
    branding: false,
    promotion: false,
    setup: function (editor) {
        editor.on('change', function () { editor.save(); });
    }
});
</script>
@endsection

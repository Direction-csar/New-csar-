@extends('layouts.admin')

@section('title', 'Modifier l\'Actualité')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>
                Modifier l'Actualité
            </h1>
            <p class="text-muted">Modifier l'actualité : {{ $actualite->titre }}</p>
        </div>
        <div>
            <a href="{{ route('admin.actualites.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations de l'actualité</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.actualites.update', $actualite->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="titre" class="form-label">Titre de l'actualité *</label>
                            <input type="text" class="form-control @error('titre') is-invalid @enderror" 
                                   id="titre" name="titre" value="{{ old('titre', $actualite->titre) }}" required>
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="categorie" class="form-label">Catégorie *</label>
                                    <select class="form-control @error('categorie') is-invalid @enderror" 
                                            id="categorie" name="categorie" required>
                                        <option value="">Sélectionner une catégorie</option>
                                        <option value="annonces" {{ old('categorie', $actualite->categorie) == 'annonces' ? 'selected' : '' }}>Annonces</option>
                                        <option value="activites" {{ old('categorie', $actualite->categorie) == 'activites' ? 'selected' : '' }}>Activités</option>
                                        <option value="evenements" {{ old('categorie', $actualite->categorie) == 'evenements' ? 'selected' : '' }}>Événements</option>
                                        <option value="formations" {{ old('categorie', $actualite->categorie) == 'formations' ? 'selected' : '' }}>Formations</option>
                                    </select>
                                    @error('categorie')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="statut" class="form-label">Statut *</label>
                                    <select class="form-control @error('statut') is-invalid @enderror" 
                                            id="statut" name="statut" required>
                                        <option value="">Sélectionner un statut</option>
                                        <option value="brouillon" {{ old('statut', $actualite->statut) == 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                                        <option value="publié" {{ old('statut', $actualite->statut) == 'publié' ? 'selected' : '' }}>Publié</option>
                                    </select>
                                    @error('statut')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="contenu" class="form-label">Contenu de l'actualité *</label>
                            <textarea class="form-control @error('contenu') is-invalid @enderror" 
                                      id="contenu" name="contenu" rows="10" required>{{ old('contenu', $actualite->contenu) }}</textarea>
                            @error('contenu')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" {{ old('featured', $actualite->featured ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">
                                    Mettre en avant (À la une)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.actualites.index') }}" class="btn btn-secondary me-2">Annuler</a>
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
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations</h6>
                </div>
                <div class="card-body">
                    <p><strong>ID :</strong> {{ $actualite->id }}</p>
                    <p><strong>Créé le :</strong> {{ $actualite->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Dernière modification :</strong> {{ $actualite->updated_at->format('d/m/Y H:i') }}</p>
                    
                    <hr>
                    
                    <h6>Actions rapides :</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.actualites.show', $actualite->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Voir l'actualité
                        </a>
                        <form action="{{ route('admin.actualites.destroy', $actualite->id) }}" method="POST" class="d-inline">
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
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#contenu',
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
    images_upload_url: '{{ route(route_prefix() . ".actualites.upload-image") }}',
    images_upload_handler: function (blobInfo, progress) {
        return new Promise(function(resolve, reject) {
            var formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            formData.append('_token', '{{ csrf_token() }}');
            fetch('{{ route(route_prefix() . ".actualites.upload-image") }}', {
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


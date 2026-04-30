@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Ajouter une Image à la Galerie')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus me-2"></i>
                Ajouter une Image à la Galerie
            </h1>
            <p class="text-muted">Ajoutez une nouvelle image à la galerie publique</p>
        </div>
        <div>
            <a href="{{ route('admin.galerie.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la galerie
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations de l'image</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.galerie.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Titre de l'image <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" 
                                   placeholder="Entrez le titre de l'image" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="category" class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-control @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">Sélectionnez une catégorie</option>
                                <option value="Action humanitaire" {{ old('category') == 'Action humanitaire' ? 'selected' : '' }}>Action humanitaire</option>
                                <option value="Entrepôt" {{ old('category') == 'Entrepôt' ? 'selected' : '' }}>Entrepôt</option>
                                <option value="Événements" {{ old('category') == 'Événements' ? 'selected' : '' }}>Événements</option>
                                <option value="Personnel" {{ old('category') == 'Personnel' ? 'selected' : '' }}>Personnel</option>
                                <option value="Infrastructure" {{ old('category') == 'Infrastructure' ? 'selected' : '' }}>Infrastructure</option>
                                <option value="Autre" {{ old('category') == 'Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Décrivez l'image (optionnel)">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                   id="image" name="image" accept="image/*" required>
                            <div class="form-text">
                                Formats acceptés : JPEG, PNG, JPG, GIF, WebP. Taille maximale : 2MB
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Mettre en vedette
                                </label>
                            </div>
                            <div class="form-text">
                                Les images en vedette apparaîtront en premier dans la galerie publique
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.galerie.index') }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Ajouter l'image
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Aperçu</h6>
                </div>
                <div class="card-body">
                    <div id="image-preview" class="text-center">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aperçu de l'image</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Conseils</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Utilisez des images de bonne qualité
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Optimisez la taille des fichiers
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Ajoutez des descriptions pertinentes
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Choisissez la bonne catégorie
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('image-preview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <img src="${e.target.result}" class="img-fluid rounded" alt="Aperçu">
                <p class="mt-2 text-muted">${file.name}</p>
                <p class="text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = `
            <i class="fas fa-image fa-3x text-muted mb-3"></i>
            <p class="text-muted">Aperçu de l'image</p>
        `;
    }
});
</script>
@endsection

@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Modifier l\'Image')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>
                Modifier l'Image
            </h1>
            <p class="text-muted">Modifiez les informations de l'image</p>
        </div>
        <div>
            <a href="{{ route('admin.galerie.show', $image->id) }}" class="btn btn-info me-2">
                <i class="fas fa-eye me-2"></i>Voir les détails
            </a>
            <a href="{{ route('admin.galerie.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la galerie
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Modifier les informations</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.galerie.update', $image->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Titre de l'image <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $image->title) }}" 
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
                                <option value="Action humanitaire" {{ old('category', $image->category) == 'Action humanitaire' ? 'selected' : '' }}>Action humanitaire</option>
                                <option value="Entrepôt" {{ old('category', $image->category) == 'Entrepôt' ? 'selected' : '' }}>Entrepôt</option>
                                <option value="Événements" {{ old('category', $image->category) == 'Événements' ? 'selected' : '' }}>Événements</option>
                                <option value="Personnel" {{ old('category', $image->category) == 'Personnel' ? 'selected' : '' }}>Personnel</option>
                                <option value="Infrastructure" {{ old('category', $image->category) == 'Infrastructure' ? 'selected' : '' }}>Infrastructure</option>
                                <option value="Autre" {{ old('category', $image->category) == 'Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" 
                                      placeholder="Décrivez l'image (optionnel)">{{ old('description', $image->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="status" class="form-label">Statut <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $image->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ old('status', $image->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="pending" {{ old('status', $image->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" 
                                       {{ old('is_featured', $image->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    Mettre en vedette
                                </label>
                            </div>
                            <div class="form-text">
                                Les images en vedette apparaîtront en premier dans la galerie publique
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.galerie.show', $image->id) }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Image actuelle -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Image actuelle</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $image->image_url }}" 
                         alt="{{ $image->title }}" 
                         class="img-fluid rounded shadow"
                         style="max-height: 300px; object-fit: contain;">
                    <p class="mt-2 text-muted">{{ $image->file_name }}</p>
                    <p class="text-muted">{{ $image->formatted_file_size }}</p>
                </div>
            </div>

            <!-- Informations du fichier -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations du fichier</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Nom :</strong></td>
                            <td>{{ $image->file_name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Taille :</strong></td>
                            <td>{{ $image->formatted_file_size }}</td>
                        </tr>
                        <tr>
                            <td><strong>Type :</strong></td>
                            <td>{{ $image->file_type }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ajoutée :</strong></td>
                            <td>{{ $image->created_at->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Modifiée :</strong></td>
                            <td>{{ $image->updated_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

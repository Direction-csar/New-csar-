@extends('layouts.admin')

@section('title', 'Nouvelle Actualité - Plateforme Institutionnelle CSAR')

@section('content')
<div class="container-fluid">
    <!-- Header Institutionnel -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-newspaper me-2 text-primary"></i>
                Nouvelle Actualité
            </h1>
            <p class="text-muted mb-0">Créer une publication institutionnelle pour le CSAR</p>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Interface de gestion de presse institutionnelle
            </small>
        </div>
        <div>
            <a href="{{ route('admin.actualites.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <!-- Formulaire Institutionnel -->
    <form action="{{ route('admin.actualites.store') }}" method="POST" enctype="multipart/form-data" id="newsForm">
        @csrf
        
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Informations de base -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-edit me-2"></i>
                            Informations de l'actualité
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Titre -->
                        <div class="form-group mb-4">
                            <label for="title" class="form-label fw-bold">
                                Titre de l'actualité <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   id="title" 
                                   name="title" 
                                   value="{{ old('title') }}" 
                                   placeholder="Saisissez un titre accrocheur et professionnel..."
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                Utilisez un titre clair et informatif pour une meilleure visibilité
                            </small>
                        </div>

                        <!-- Extrait -->
                        <div class="form-group mb-4">
                            <label for="excerpt" class="form-label fw-bold">
                                Extrait / Résumé
                            </label>
                            <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                      id="excerpt" 
                                      name="excerpt" 
                                      rows="3" 
                                      placeholder="Résumé court de l'actualité (optionnel mais recommandé)..."
                                      maxlength="500">{{ old('excerpt') }}</textarea>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Résumé qui apparaîtra dans les listes et aperçus
                                </small>
                                <small class="text-muted">
                                    <span id="excerpt-count">0</span>/500 caractères
                                </small>
                            </div>
                            @error('excerpt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contenu avec éditeur riche -->
                        <div class="form-group mb-4">
                            <label for="content" class="form-label fw-bold">
                                Contenu de l'actualité <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="15" 
                                      required>{{ old('content') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                <i class="fas fa-edit me-1"></i>
                                Utilisez l'éditeur riche pour formater votre contenu professionnellement
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Médias et Documents -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-images me-2"></i>
                            Médias associés (optionnel)
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Image de couverture -->
                        <div class="form-group mb-4">
                            <label for="featured_image" class="form-label fw-bold">
                                🖼️ Image de couverture
                            </label>
                            <div class="upload-area" id="image-upload-area">
                                <div class="upload-content">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Cliquez ou glissez une image ici</p>
                                    <small class="text-muted">Formats: JPEG, PNG, JPG, GIF, WebP (max 5MB)</small>
                                </div>
                                <input type="file" id="featured_image" name="featured_image" accept="image/*" style="display: none;" onchange="previewImage(this)">
                            </div>
                            <div id="image-preview" class="mt-3" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <img id="preview-img" src="" alt="Preview" class="img-thumbnail me-3" style="max-width: 150px; max-height: 150px;">
                                    <div class="flex-grow-1">
                                        <div id="image-info" class="text-muted small"></div>
                                        <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="removeImage()">
                                            <i class="fas fa-trash me-1"></i>Supprimer
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                L'image de couverture sera affichée en premier. Si une vidéo est ajoutée, elle deviendra la couverture principale.
                            </small>
                        </div>

                        <!-- URL de vidéo -->
                        <div class="form-group mb-4">
                            <label for="youtube_url" class="form-label fw-bold">
                                🎥 Lien vidéo (YouTube, Vimeo, etc.)
                            </label>
                            <input type="url" 
                                   class="form-control @error('youtube_url') is-invalid @enderror" 
                                   id="youtube_url" 
                                   name="youtube_url" 
                                   placeholder="https://www.youtube.com/watch?v=..." 
                                   value="{{ old('youtube_url') }}">
                            @error('youtube_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Choix de la couverture -->
                        <div class="form-group mb-4">
                            <label class="form-label fw-bold">
                                🎯 Choix de la couverture
                            </label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cover_choice" id="cover_auto" value="auto" checked>
                                <label class="form-check-label" for="cover_auto">
                                    <strong>Automatique</strong> - Vidéo en priorité, sinon image
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cover_choice" id="cover_video" value="video">
                                <label class="form-check-label" for="cover_video">
                                    <strong>Vidéo</strong> - Toujours utiliser la vidéo comme couverture
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cover_choice" id="cover_image" value="image">
                                <label class="form-check-label" for="cover_image">
                                    <strong>Image</strong> - Toujours utiliser l'image comme couverture
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Ce choix détermine ce qui sera affiché en premier sur la page de l'actualité
                            </small>
                        </div>

                        <!-- Document associé -->
                        <div class="form-group mb-4">
                            <label for="document_file" class="form-label fw-bold">
                                📎 Document associé
                            </label>
                            <div class="upload-area" id="document-upload-area">
                                <div class="upload-content">
                                    <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Cliquez ou glissez un document ici</p>
                                    <small class="text-muted">Formats: PDF, DOC, DOCX, PPT, PPTX (max 50MB)</small>
                                </div>
                                <input type="file" id="document_file" name="document_file" accept=".pdf,.doc,.docx,.ppt,.pptx" style="display: none;" onchange="previewDocument(this)">
                            </div>
                            <div id="document-preview" class="mt-3" style="display: none;">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <i class="fas fa-file-pdf fa-2x text-danger me-3"></i>
                                    <div class="flex-grow-1">
                                        <div id="document-name" class="fw-bold"></div>
                                        <small class="text-muted" id="document-size"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDocument()">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Titre du document -->
                            <div class="mt-3">
                                <label for="document_title" class="form-label">Titre du document</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="document_title" 
                                       name="document_title" 
                                       placeholder="Titre personnalisé pour le document (optionnel)"
                                       value="{{ old('document_title') }}">
                                <small class="text-muted">
                                    Si non renseigné, le nom du fichier sera utilisé
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image de couverture du document -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-image me-2"></i>
                            Image de couverture du document (optionnel)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="document_cover_image" class="form-label">
                                🖼️ Image de couverture personnalisée
                            </label>
                            <div class="upload-area" id="document-cover-upload-area">
                                <div class="upload-content">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="mb-2">Cliquez ou glissez une image ici</p>
                                    <small class="text-muted">Formats: JPEG, PNG, JPG, GIF, WebP (max 5MB)</small>
                                </div>
                                <input type="file" id="document_cover_image" name="document_cover_image" accept="image/*" style="display: none;" onchange="previewDocumentCover(this)">
                            </div>
                            <div id="document-cover-preview" class="mt-3" style="display: none;">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <img id="document-cover-thumbnail" src="" alt="Aperçu" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                    <div class="flex-grow-1">
                                        <div id="document-cover-name" class="fw-bold"></div>
                                        <small class="text-muted" id="document-cover-size"></small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDocumentCover()">
                                        <i class="fas fa-trash me-1"></i>Supprimer
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Cette image sera affichée comme couverture avant l'ouverture du document
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Métadonnées SEO -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-search me-2"></i>
                            Métadonnées SEO (optionnel)
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="meta_title" class="form-label">Titre SEO</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="meta_title" 
                                           name="meta_title" 
                                           placeholder="Titre pour les moteurs de recherche"
                                           value="{{ old('meta_title') }}"
                                           maxlength="255">
                                    <small class="text-muted">Recommandé: 50-60 caractères</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tags" class="form-label">Mots-clés</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tags" 
                                           name="tags" 
                                           placeholder="csar, actualité, sénégal (séparés par des virgules)"
                                           value="{{ old('tags') }}">
                                    <small class="text-muted">Séparez les mots-clés par des virgules</small>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="meta_description" class="form-label">Description SEO</label>
                            <textarea class="form-control" 
                                      id="meta_description" 
                                      name="meta_description" 
                                      rows="3" 
                                      placeholder="Description pour les moteurs de recherche"
                                      maxlength="500">{{ old('meta_description') }}</textarea>
                            <small class="text-muted">Recommandé: 150-160 caractères</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="col-lg-4">
                <!-- Options de publication -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Options de publication
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Catégorie -->
                        <div class="form-group mb-3">
                            <label for="category" class="form-label fw-bold">
                                Catégorie <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" 
                                    name="category" 
                                    required>
                                <option value="">Sélectionner une catégorie</option>
                                <option value="actualite" {{ old('category') == 'actualite' ? 'selected' : '' }}>Actualité</option>
                                <option value="mission" {{ old('category') == 'mission' ? 'selected' : '' }}>Mission</option>
                                <option value="communication" {{ old('category') == 'communication' ? 'selected' : '' }}>Communication</option>
                                <option value="formation" {{ old('category') == 'formation' ? 'selected' : '' }}>Formation</option>
                                <option value="evenement" {{ old('category') == 'evenement' ? 'selected' : '' }}>Événement</option>
                                <option value="publication" {{ old('category') == 'publication' ? 'selected' : '' }}>Publication</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Statut -->
                        <div class="form-group mb-3">
                            <label for="status" class="form-label fw-bold">
                                Statut <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="">Sélectionner un statut</option>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publié</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Publication programmée -->
                        <div class="form-group mb-3" id="scheduled-section" style="display: none;">
                            <label for="scheduled_at" class="form-label">Publication programmée</label>
                            <input type="datetime-local" 
                                   class="form-control" 
                                   id="scheduled_at" 
                                   name="scheduled_at" 
                                   value="{{ old('scheduled_at') }}">
                            <small class="text-muted">L'actualité sera publiée automatiquement à cette date</small>
                        </div>

                        <!-- Options -->
                        <div class="form-group mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_featured">
                                    <i class="fas fa-star text-warning me-1"></i>
                                    Mettre en avant (À la une)
                                </label>
                                <small class="text-muted d-block">L'actualité apparaîtra en premier sur la page d'accueil</small>
                            </div>
                            
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_public">
                                    <i class="fas fa-globe text-success me-1"></i>
                                    Rendre public immédiatement
                                </label>
                                <small class="text-muted d-block">L'actualité sera visible par tous les visiteurs</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Aide et conseils -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-lightbulb me-2"></i>
                            Conseils de rédaction
                        </h6>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Catégories disponibles :</h6>
                        <ul class="list-unstyled small">
                            <li><span class="badge bg-primary me-1">Actualité</span> - Nouvelles générales</li>
                            <li><span class="badge bg-success me-1">Mission</span> - Missions du CSAR</li>
                            <li><span class="badge bg-info me-1">Communication</span> - Communications officielles</li>
                            <li><span class="badge bg-warning me-1">Formation</span> - Sessions de formation</li>
                            <li><span class="badge bg-danger me-1">Événement</span> - Événements spéciaux</li>
                            <li><span class="badge bg-dark me-1">Publication</span> - Publications officielles</li>
                        </ul>
                        
                        <hr>
                        
                        <h6 class="text-success">Bonnes pratiques :</h6>
                        <ul class="small">
                            <li>Utilisez un titre clair et informatif</li>
                            <li>Rédigez un contenu structuré et professionnel</li>
                            <li>Vérifiez l'orthographe avant publication</li>
                            <li>Ajoutez des médias pour enrichir le contenu</li>
                            <li>Les actualités "À la une" apparaissent en premier</li>
                        </ul>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Créer l'actualité
                            </button>
                            <a href="{{ route('admin.actualites.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Annuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('styles')
<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 40px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    position: relative;
}

.upload-area:hover {
    border-color: #0d6efd;
    background: #e3f2fd;
    transform: translateY(-2px);
}

.upload-area.dragover {
    border-color: #198754;
    background: #d1e7dd;
    transform: scale(1.02);
}

.upload-content {
    pointer-events: none;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1) !important;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

#image-preview img {
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.75rem;
}

/* Animation pour les uploads */
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.upload-area.dragover {
    animation: pulse 0.6s ease-in-out;
}

/* Responsive */
@media (max-width: 768px) {
    .upload-area {
        padding: 20px 10px;
    }
    
    .card-body {
        padding: 1rem;
    }
}
</style>
@endsection

@section('scripts')
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<script>
// Configuration TinyMCE
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
        editor.on('change', function () {
            editor.save();
        });
    }
});

// Compteur de caractères pour l'extrait
document.getElementById('excerpt').addEventListener('input', function() {
    const count = this.value.length;
    document.getElementById('excerpt-count').textContent = count;
    
    if (count > 450) {
        document.getElementById('excerpt-count').style.color = '#dc3545';
    } else if (count > 400) {
        document.getElementById('excerpt-count').style.color = '#ffc107';
    } else {
        document.getElementById('excerpt-count').style.color = '#6c757d';
    }
});

// Gestion du statut et de la publication programmée
document.getElementById('status').addEventListener('change', function() {
    const scheduledSection = document.getElementById('scheduled-section');
    if (this.value === 'published') {
        scheduledSection.style.display = 'block';
    } else {
        scheduledSection.style.display = 'none';
    }
});

// Fonctions d'upload et de prévisualisation
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-info').innerHTML = `
                <strong>${file.name}</strong><br>
                <small>Taille: ${formatFileSize(file.size)}</small>
            `;
            document.getElementById('image-preview').style.display = 'block';
        };
        
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    document.getElementById('featured_image').value = '';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('preview-img').src = '';
}

function previewDocument(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('document-name').textContent = file.name;
        document.getElementById('document-size').textContent = formatFileSize(file.size);
        document.getElementById('document-preview').style.display = 'block';
        
        // Auto-remplir le titre du document
        if (!document.getElementById('document_title').value) {
            const fileName = file.name.replace(/\.[^/.]+$/, ""); // Enlever l'extension
            document.getElementById('document_title').value = fileName;
        }
    }
}

function removeDocument() {
    document.getElementById('document_file').value = '';
    document.getElementById('document-preview').style.display = 'none';
    document.getElementById('document_title').value = '';
}

function previewDocumentCover(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('document-cover-name').textContent = file.name;
        document.getElementById('document-cover-size').textContent = formatFileSize(file.size);
        
        // Créer un aperçu de l'image
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('document-cover-thumbnail').src = e.target.result;
        };
        reader.readAsDataURL(file);
        
        document.getElementById('document-cover-preview').style.display = 'block';
    }
}

function removeDocumentCover() {
    document.getElementById('document_cover_image').value = '';
    document.getElementById('document-cover-preview').style.display = 'none';
    document.getElementById('document-cover-thumbnail').src = '';
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Drag and drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const imageUploadArea = document.getElementById('image-upload-area');
    const documentUploadArea = document.getElementById('document-upload-area');
    
    // Image upload area
    imageUploadArea.addEventListener('click', () => document.getElementById('featured_image').click());
    imageUploadArea.addEventListener('dragover', handleDragOver);
    imageUploadArea.addEventListener('dragleave', handleDragLeave);
    imageUploadArea.addEventListener('drop', (e) => handleDrop(e, 'featured_image'));
    
    // Document upload area
    documentUploadArea.addEventListener('click', () => document.getElementById('document_file').click());
    documentUploadArea.addEventListener('dragover', handleDragOver);
    documentUploadArea.addEventListener('dragleave', handleDragLeave);
    documentUploadArea.addEventListener('drop', (e) => handleDrop(e, 'document_file'));
    
    // Document cover image upload area
    const documentCoverUploadArea = document.getElementById('document-cover-upload-area');
    documentCoverUploadArea.addEventListener('click', () => document.getElementById('document_cover_image').click());
    documentCoverUploadArea.addEventListener('dragover', handleDragOver);
    documentCoverUploadArea.addEventListener('dragleave', handleDragLeave);
    documentCoverUploadArea.addEventListener('drop', (e) => handleDrop(e, 'document_cover_image'));
    
    function handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('dragover');
    }
    
    function handleDragLeave(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
    }
    
    function handleDrop(e, type) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const input = document.getElementById(type);
            input.files = files;
            if (type === 'featured_image') {
                previewImage(input);
            } else if (type === 'document_file') {
                previewDocument(input);
            } else if (type === 'document_cover_image') {
                previewDocumentCover(input);
            }
        }
    }
});

// Validation du formulaire
document.getElementById('newsForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const content = tinymce.get('content').getContent();
    
    if (!title) {
        e.preventDefault();
        alert('Veuillez saisir un titre pour l\'actualité.');
        document.getElementById('title').focus();
        return;
    }
    
    if (!content || content.trim() === '') {
        e.preventDefault();
        alert('Veuillez saisir le contenu de l\'actualité.');
        tinymce.get('content').focus();
        return;
    }
    
    // Afficher un indicateur de chargement
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Création en cours...';
    submitBtn.disabled = true;
});
</script>
@endsection
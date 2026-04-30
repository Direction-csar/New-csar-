@extends('layouts.ctc')

@section('title', 'Nouvelle Actualité - CTC CSAR')

@section('content')
<div class="container-fluid">
    <!-- Header CTC -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-white">
                <i class="fas fa-newspaper me-2"></i>
                Nouvelle Actualité
            </h1>
            <p class="text-white-50 mb-0">Créer une publication pour le CTC</p>
        </div>
        <div>
            <a href="{{ route('ctc.actualites.index') }}" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-2"></i>Retour à la liste
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <form action="{{ route('ctc.actualites.store') }}" method="POST" enctype="multipart/form-data" id="newsForm">
        @csrf
        
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Informations de base -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header" style="background: var(--ctc-primary); color: white;">
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
                                   placeholder="Saisissez un titre accrocheur..."
                                   required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                      placeholder="Résumé court..."
                                      maxlength="500">{{ old('excerpt') }}</textarea>
                            <div class="d-flex justify-content-between">
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
                                Utilisez l'éditeur riche pour formater votre contenu et ajouter des images
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Médias -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header" style="background: #198754; color: white;">
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
                            <label class="form-label fw-bold">🎯 Choix de la couverture</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cover_choice" id="cover_auto" value="auto" checked>
                                <label class="form-check-label" for="cover_auto">Automatique - Vidéo en priorité, sinon image</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cover_choice" id="cover_video" value="video">
                                <label class="form-check-label" for="cover_video">Vidéo - Toujours utiliser la vidéo</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="cover_choice" id="cover_image" value="image">
                                <label class="form-check-label" for="cover_image">Image - Toujours utiliser l'image</label>
                            </div>
                        </div>

                        <!-- Document associé -->
                        <div class="form-group mb-4">
                            <label for="document_file" class="form-label fw-bold">📎 Document associé</label>
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
                            <div class="mt-3">
                                <label for="document_title" class="form-label">Titre du document</label>
                                <input type="text" class="form-control" id="document_title" name="document_title" placeholder="Titre personnalisé" value="{{ old('document_title') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Métadonnées SEO -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header" style="background: #0dcaf0; color: white;">
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
                                    <input type="text" class="form-control" id="meta_title" name="meta_title" placeholder="Titre pour les moteurs de recherche" value="{{ old('meta_title') }}" maxlength="255">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="tags" class="form-label">Mots-clés</label>
                                    <input type="text" class="form-control" id="tags" name="tags" placeholder="csar, actualité, sénégal" value="{{ old('tags') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="meta_description" class="form-label">Description SEO</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3" placeholder="Description pour les moteurs de recherche" maxlength="500">{{ old('meta_description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale -->
            <div class="col-lg-4">
                <!-- Options de publication -->
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header" style="background: #ffc107; color: #212529;">
                        <h6 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Options de publication
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Catégorie -->
                        <div class="form-group mb-3">
                            <label for="category" class="form-label fw-bold">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
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
                            <label for="status" class="form-label fw-bold">Statut <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="">Sélectionner un statut</option>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Publié</option>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Options -->
                        <div class="form-group mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_featured">
                                    <i class="fas fa-star text-warning me-1"></i> Mettre en avant (À la une)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_public">
                                    <i class="fas fa-globe text-success me-1"></i> Rendre public immédiatement
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-save me-2"></i>Créer l'actualité
                            </button>
                            <a href="{{ route('ctc.actualites.index') }}" class="btn btn-outline-secondary">
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
}
.upload-area:hover {
    border-color: var(--ctc-primary);
    background: #e3f2fd;
}
.upload-area.dragover {
    border-color: #198754;
    background: #d1e7dd;
}
.upload-content {
    pointer-events: none;
}
</style>
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

// Compteur de caractères
document.getElementById('excerpt').addEventListener('input', function() {
    document.getElementById('excerpt-count').textContent = this.value.length;
});

// Upload functions
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-info').innerHTML = `<strong>${file.name}</strong><br><small>Taille: ${formatFileSize(file.size)}</small>`;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
function removeImage() {
    document.getElementById('featured_image').value = '';
    document.getElementById('image-preview').style.display = 'none';
}
function previewDocument(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        document.getElementById('document-name').textContent = file.name;
        document.getElementById('document-size').textContent = formatFileSize(file.size);
        document.getElementById('document-preview').style.display = 'block';
    }
}
function removeDocument() {
    document.getElementById('document_file').value = '';
    document.getElementById('document-preview').style.display = 'none';
}
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
// Drag and drop
document.addEventListener('DOMContentLoaded', function() {
    ['image-upload-area', 'document-upload-area'].forEach(id => {
        const area = document.getElementById(id);
        const inputId = id === 'image-upload-area' ? 'featured_image' : 'document_file';
        area.addEventListener('click', () => document.getElementById(inputId).click());
        area.addEventListener('dragover', e => { e.preventDefault(); area.classList.add('dragover'); });
        area.addEventListener('dragleave', () => area.classList.remove('dragover'));
        area.addEventListener('drop', e => {
            e.preventDefault();
            area.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                document.getElementById(inputId).files = e.dataTransfer.files;
                if (inputId === 'featured_image') previewImage(document.getElementById(inputId));
                else previewDocument(document.getElementById(inputId));
            }
        });
    });
});
</script>
@endsection

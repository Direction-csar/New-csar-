@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Modifier le Rapport SIM')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-edit me-2"></i>Modifier le Rapport SIM
                    </h1>
                    <p class="text-muted mb-0">Modifiez les informations du rapport</p>
                </div>
                <div>
                    <a href="{{ route('admin.sim-reports.index') }}" class="btn btn-secondary-modern btn-modern">
                        <i class="fas fa-arrow-left me-2"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire d'édition -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-body">
                    <form action="{{ route('admin.sim-reports.update', $report->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group mb-3">
                                    <label for="title" class="form-label">Titre du rapport *</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $report->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $report->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="summary" class="form-label">Résumé</label>
                                    <textarea class="form-control @error('summary') is-invalid @enderror" 
                                              id="summary" name="summary" rows="4">{{ old('summary', $report->summary) }}</textarea>
                                    @error('summary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="report_type" class="form-label">Type de rapport *</label>
                                    <select class="form-select @error('report_type') is-invalid @enderror" 
                                            id="report_type" name="report_type" required>
                                        <option value="">Sélectionner un type</option>
                                        <option value="financial" {{ old('report_type', $report->report_type) == 'financial' ? 'selected' : '' }}>Financier</option>
                                        <option value="operational" {{ old('report_type', $report->report_type) == 'operational' ? 'selected' : '' }}>Opérationnel</option>
                                        <option value="inventory" {{ old('report_type', $report->report_type) == 'inventory' ? 'selected' : '' }}>Inventaire</option>
                                        <option value="personnel" {{ old('report_type', $report->report_type) == 'personnel' ? 'selected' : '' }}>Personnel</option>
                                        <option value="general" {{ old('report_type', $report->report_type) == 'general' ? 'selected' : '' }}>Général</option>
                                    </select>
                                    @error('report_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Statut *</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $report->status) == 'draft' ? 'selected' : '' }}>Brouillon</option>
                                        <option value="generating" {{ old('status', $report->status) == 'generating' ? 'selected' : '' }}>En génération</option>
                                        <option value="completed" {{ old('status', $report->status) == 'completed' ? 'selected' : '' }}>Terminé</option>
                                        <option value="published" {{ old('status', $report->status) == 'published' ? 'selected' : '' }}>Publié</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" 
                                               value="1" {{ old('is_public', $report->is_public) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_public">
                                            Rendre public
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Les rapports publics sont visibles sur la plateforme publique
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.sim-reports.index') }}" class="btn btn-secondary-modern btn-modern">
                                        <i class="fas fa-times me-2"></i>Annuler
                                    </a>
                                    <button type="submit" class="btn btn-primary-modern btn-modern">
                                        <i class="fas fa-save me-2"></i>Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mise à jour automatique de la date de publication
    const statusSelect = document.getElementById('status');
    const isPublicCheckbox = document.getElementById('is_public');
    
    statusSelect.addEventListener('change', function() {
        if (this.value === 'published') {
            isPublicCheckbox.checked = true;
        }
    });
    
    isPublicCheckbox.addEventListener('change', function() {
        if (this.checked && statusSelect.value !== 'published') {
            if (confirm('Voulez-vous publier ce rapport pour le rendre public ?')) {
                statusSelect.value = 'published';
            }
        }
    });
});
</script>
@endsection

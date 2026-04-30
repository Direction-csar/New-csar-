@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Rapports SIM')

@section('content')
<div class="container-fluid">
    <!-- En-tête -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Rapports SIM
                    </h1>
                    <p class="text-muted mb-0">Générez et consultez les rapports du système d'information de gestion</p>
                </div>
                <div>
                    <button class="btn btn-success-modern btn-modern" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="fas fa-upload me-2"></i>Uploader Document
                    </button>
                    <button class="btn btn-primary-modern btn-modern" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                        <i class="fas fa-plus me-2"></i>Générer Rapport
                    </button>
                    <button class="btn btn-info-modern btn-modern" onclick="exportAllReports()">
                        <i class="fas fa-download me-2"></i>Exporter Tout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-primary);">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3 class="mb-1" id="total-reports">{{ $stats['total_reports'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Total Rapports</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-success);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="mb-1" id="completed-reports">{{ $stats['completed_reports'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Terminés</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-warning);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="mb-1" id="pending-reports">{{ $stats['pending_reports'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">En Cours</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-modern">
                <div class="card-body text-center">
                    <div class="icon-3d mb-3" style="background: var(--gradient-info);">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <h3 class="mb-1" id="scheduled-reports">{{ $stats['scheduled_reports'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Programmés</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et recherche -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Recherche</label>
                                <input type="text" class="form-control" id="search-input" placeholder="Nom, type, description...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Type</label>
                                <select class="form-select" id="type-filter">
                                    <option value="">Tous</option>
                                    <option value="financial">Financier</option>
                                    <option value="operational">Opérationnel</option>
                                    <option value="inventory">Inventaire</option>
                                    <option value="personnel">Personnel</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Statut</label>
                                <select class="form-select" id="status-filter">
                                    <option value="">Tous</option>
                                    <option value="completed">Terminé</option>
                                    <option value="pending">En cours</option>
                                    <option value="scheduled">Programmé</option>
                                    <option value="failed">Échoué</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Période</label>
                                <select class="form-select" id="period-filter">
                                    <option value="">Toutes</option>
                                    <option value="today">Aujourd'hui</option>
                                    <option value="week">Cette semaine</option>
                                    <option value="month">Ce mois</option>
                                    <option value="year">Cette année</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-primary-modern btn-modern" onclick="applyFilters()">
                                        <i class="fas fa-search me-2"></i>Filtrer
                                    </button>
                                    <button class="btn btn-secondary-modern btn-modern" onclick="clearFilters()">
                                        <i class="fas fa-times me-2"></i>Effacer
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des rapports -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Liste des Rapports
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                                <i class="fas fa-check-square me-1"></i>Tout sélectionner
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="downloadSelected()">
                                <i class="fas fa-download me-1"></i>Télécharger
                            </button>
                            <button class="btn btn-sm btn-outline-info" onclick="scheduleSelected()">
                                <i class="fas fa-clock me-1"></i>Programmer
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteSelected()">
                                <i class="fas fa-trash me-1"></i>Supprimer
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="reports-list">
                        @forelse($reports ?? [] as $report)
                        <div class="report-item p-3 border-bottom" data-report-id="{{ $report->id }}">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="icon-3d" style="background: var(--gradient-{{ $report->report_type ?? 'primary' }}); width: 50px; height: 50px;">
                                        <i class="fas fa-{{ $report->report_type === 'financial' ? 'dollar-sign' : ($report->report_type === 'operational' ? 'cogs' : ($report->report_type === 'inventory' ? 'boxes' : 'users')) }}"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 fw-bold">{{ $report->title ?? 'Rapport SIM' }}</h6>
                                            <p class="mb-1 text-muted small">{{ $report->description ?? 'Description non disponible' }}</p>
                                            <div class="d-flex gap-3 small text-muted">
                                                <span><i class="fas fa-tag me-1"></i>{{ ucfirst($report->report_type ?? 'Général') }}</span>
                                                <span><i class="fas fa-calendar me-1"></i>{{ $report->created_at->format('d/m/Y') }}</span>
                                                <span><i class="fas fa-download me-1"></i>{{ $report->download_count ?? 0 }} téléchargements</span>
                                            </div>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <span class="badge bg-{{ $report->status === 'completed' ? 'success' : ($report->status === 'generating' ? 'warning' : 'info') }}">
                                                {{ ucfirst($report->status ?? 'Inconnu') }}
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('admin.sim-reports.show', $report->id) }}">
                                                        <i class="fas fa-eye me-2"></i>Voir
                                                    </a></li>
                                                    <li><a class="dropdown-item" href="{{ route('admin.sim-reports.edit', $report->id) }}">
                                                        <i class="fas fa-edit me-2"></i>Modifier
                                                    </a></li>
                                                    @if($report->status === 'completed' && $report->document_file)
                                                    <li><a class="dropdown-item" href="{{ route('admin.sim-reports.download', $report->id) }}">
                                                        <i class="fas fa-download me-2"></i>Télécharger
                                                    </a></li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteReport({{ $report->id }})">
                                                        <i class="fas fa-trash me-2"></i>Supprimer
                                                    </a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="icon-3d mb-3" style="background: var(--gradient-secondary); width: 80px; height: 80px; margin: 0 auto;">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <h5 class="text-muted">Aucun rapport</h5>
                            <p class="text-muted">Générez votre premier rapport SIM.</p>
                            <button class="btn btn-primary-modern btn-modern" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                                <i class="fas fa-plus me-2"></i>Générer un rapport
                            </button>
                        </div>
                        @endforelse
                        
                        @if($reports && $reports->hasPages())
                        <div class="p-3">
                            {{ $reports->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Uploader Document -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentModalLabel">
                    <i class="fas fa-upload me-2"></i>Uploader un Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="uploadDocumentForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Formats acceptés :</strong> PDF, PowerPoint (PPT/PPTX), Word (DOC/DOCX), Excel (XLS/XLSX)<br>
                        <strong>Taille maximale :</strong> 50 Mo par document
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="upload_title" class="form-label">Titre du document <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="upload_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="upload_report_type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="upload_report_type" name="report_type" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="financial">Financier</option>
                                    <option value="operational">Opérationnel</option>
                                    <option value="inventory">Inventaire</option>
                                    <option value="personnel">Personnel</option>
                                    <option value="general">Général</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="upload_description" class="form-label">Description</label>
                        <textarea class="form-control" id="upload_description" name="description" rows="3" placeholder="Description du document..."></textarea>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="document_file" class="form-label">Document <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="document_file" name="document" required accept=".pdf,.ppt,.pptx,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Sélectionnez un fichier (PDF, PowerPoint, Word, Excel) - Max 50 Mo</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="cover_image" class="form-label">Image de couverture (optionnel)</label>
                        <input type="file" class="form-control" id="cover_image" name="cover_image" accept="image/*">
                        <div class="form-text">Image de couverture pour le document - Max 10 Mo</div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1">
                            <label class="form-check-label" for="is_public">
                                <strong>Rendre public</strong> - Le document sera accessible sur la plateforme publique
                            </label>
                        </div>
                    </div>
                    
                    <!-- Zone de prévisualisation -->
                    <div id="file-preview" class="mt-3" style="display: none;">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Aperçu du fichier</h6>
                            </div>
                            <div class="card-body">
                                <div id="preview-content"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success-modern" id="upload-submit-btn">
                        <i class="fas fa-upload me-2"></i>Uploader
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Générer Rapport -->
<div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generateReportModalLabel">
                    <i class="fas fa-plus me-2"></i>Générer un Rapport
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reportForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="report_name" class="form-label">Nom du rapport <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="report_name" name="report_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="report_type" class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="report_type" name="report_type" required>
                                    <option value="">Sélectionner un type</option>
                                    <option value="financial">Financier</option>
                                    <option value="operational">Opérationnel</option>
                                    <option value="inventory">Inventaire</option>
                                    <option value="personnel">Personnel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label for="report_description" class="form-label">Description</label>
                        <textarea class="form-control" id="report_description" name="report_description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="date_from" class="form-label">Date de début <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_from" name="date_from" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="date_to" class="form-label">Date de fin <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="date_to" name="date_to" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="report_format" class="form-label">Format</label>
                                <select class="form-select" id="report_format" name="report_format">
                                    <option value="pdf">PDF</option>
                                    <option value="excel">Excel</option>
                                    <option value="csv">CSV</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="report_schedule" class="form-label">Programmation</label>
                                <select class="form-select" id="report_schedule" name="report_schedule">
                                    <option value="now">Générer maintenant</option>
                                    <option value="schedule">Programmer</option>
                                    <option value="recurring">Récurrent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3" id="schedule-options" style="display: none;">
                        <label class="form-label">Options de programmation</label>
                        <div class="row">
                            <div class="col-md-6">
                                <input type="datetime-local" class="form-control" id="schedule_date" name="schedule_date">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="recurrence" name="recurrence">
                                    <option value="daily">Quotidien</option>
                                    <option value="weekly">Hebdomadaire</option>
                                    <option value="monthly">Mensuel</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary-modern">
                        <i class="fas fa-play me-2"></i>Générer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation des compteurs
    animateCounters();
    
    // Gestion du formulaire d'upload
    document.getElementById('uploadDocumentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        uploadDocument();
    });
    
    // Gestion du formulaire de génération
    document.getElementById('reportForm').addEventListener('submit', function(e) {
        e.preventDefault();
        generateReport();
    });
    
    // Gestion de la prévisualisation des fichiers
    document.getElementById('document_file').addEventListener('change', function(e) {
        previewFile(e.target.files[0]);
    });
    
    // Gestion de la programmation
    document.getElementById('report_schedule').addEventListener('change', function() {
        const scheduleOptions = document.getElementById('schedule-options');
        scheduleOptions.style.display = (this.value === 'schedule' || this.value === 'recurring') ? 'block' : 'none';
    });
    
    // Gestion des filtres
    document.getElementById('search-input').addEventListener('input', debounce(applyFilters, 300));
    document.getElementById('type-filter').addEventListener('change', applyFilters);
    document.getElementById('status-filter').addEventListener('change', applyFilters);
    document.getElementById('period-filter').addEventListener('change', applyFilters);
});

function animateCounters() {
    // Animation des compteurs avec les vraies données
    const totalReports = {{ $stats['total_reports'] ?? 0 }};
    const completedReports = {{ $stats['completed_reports'] ?? 0 }};
    const pendingReports = {{ $stats['pending_reports'] ?? 0 }};
    const scheduledReports = {{ $stats['scheduled_reports'] ?? 0 }};
    
    animateValue(document.getElementById('total-reports'), 0, totalReports, 1000);
    animateValue(document.getElementById('completed-reports'), 0, completedReports, 1000);
    animateValue(document.getElementById('pending-reports'), 0, pendingReports, 1000);
    animateValue(document.getElementById('scheduled-reports'), 0, scheduledReports, 1000);
}

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function uploadDocument() {
    const formData = new FormData(document.getElementById('uploadDocumentForm'));
    const submitBtn = document.getElementById('upload-submit-btn');
    const originalText = submitBtn.innerHTML;
    
    // Désactiver le bouton et afficher le loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Upload en cours...';
    
    // Requête AJAX vers le contrôleur
    fetch('{{ route("admin.sim-reports.upload") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('uploadDocumentForm').reset();
            document.getElementById('file-preview').style.display = 'none';
            bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal')).hide();
            location.reload();
        } else {
            showToast(data.message || 'Erreur lors de l\'upload', 'error');
            if (data.errors) {
                // Afficher les erreurs de validation
                Object.keys(data.errors).forEach(field => {
                    const errorMsg = data.errors[field][0];
                    showToast(`${field}: ${errorMsg}`, 'error');
                });
            }
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de l\'upload du document', 'error');
    })
    .finally(() => {
        // Réactiver le bouton
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
}

function previewFile(file) {
    if (!file) {
        document.getElementById('file-preview').style.display = 'none';
        return;
    }
    
    const preview = document.getElementById('preview-content');
    const previewContainer = document.getElementById('file-preview');
    
    // Vérifier la taille du fichier (50 MB max)
    const maxSize = 50 * 1024 * 1024; // 50 MB en bytes
    if (file.size > maxSize) {
        showToast('Le fichier est trop volumineux. Taille maximale: 50 Mo', 'error');
        document.getElementById('document_file').value = '';
        previewContainer.style.display = 'none';
        return;
    }
    
    // Vérifier le type de fichier
    const allowedTypes = [
        'application/pdf',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];
    
    if (!allowedTypes.includes(file.type)) {
        showToast('Type de fichier non supporté. Formats acceptés: PDF, PowerPoint, Word, Excel', 'error');
        document.getElementById('document_file').value = '';
        previewContainer.style.display = 'none';
        return;
    }
    
    // Afficher les informations du fichier
    const fileSize = (file.size / (1024 * 1024)).toFixed(2);
    const fileType = getFileTypeLabel(file.type);
    
    preview.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="icon-3d me-3" style="background: var(--gradient-primary); width: 60px; height: 60px;">
                <i class="fas fa-${getFileIcon(file.type)}"></i>
            </div>
            <div>
                <h6 class="mb-1">${file.name}</h6>
                <p class="mb-1 text-muted small">Type: ${fileType}</p>
                <p class="mb-0 text-muted small">Taille: ${fileSize} Mo</p>
            </div>
        </div>
    `;
    
    previewContainer.style.display = 'block';
}

function getFileTypeLabel(mimeType) {
    const types = {
        'application/pdf': 'PDF',
        'application/vnd.ms-powerpoint': 'PowerPoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'PowerPoint',
        'application/msword': 'Word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'Word',
        'application/vnd.ms-excel': 'Excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'Excel'
    };
    return types[mimeType] || 'Document';
}

function getFileIcon(mimeType) {
    const icons = {
        'application/pdf': 'file-pdf',
        'application/vnd.ms-powerpoint': 'file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation': 'file-powerpoint',
        'application/msword': 'file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'file-word',
        'application/vnd.ms-excel': 'file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'file-excel'
    };
    return icons[mimeType] || 'file';
}

function generateReport() {
    const formData = new FormData(document.getElementById('reportForm'));
    
    // Requête AJAX réelle vers le contrôleur
    fetch('{{ route("admin.sim-reports.generate") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('reportForm').reset();
            bootstrap.Modal.getInstance(document.getElementById('generateReportModal')).hide();
            location.reload();
        } else {
            showToast(data.message || 'Erreur lors de la génération', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showToast('Erreur lors de la génération du rapport', 'error');
    });
}

function applyFilters() {
    // Simulation de filtrage (à remplacer par une vraie requête AJAX)
    showToast('Filtres appliqués', 'info');
}

function clearFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('type-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('period-filter').value = '';
    applyFilters();
}

function loadReports() {
    // Simulation de chargement (à remplacer par une vraie requête AJAX)
    // Pour l'instant, la liste reste vide
}

function selectAll() {
    showToast('Tous les rapports sélectionnés', 'info');
}

function downloadSelected() {
    showToast('Téléchargement des rapports sélectionnés...', 'info');
}

function scheduleSelected() {
    showToast('Programmation des rapports sélectionnés...', 'info');
}

function deleteSelected() {
    if (confirm('Êtes-vous sûr de vouloir supprimer les rapports sélectionnés ?')) {
        showToast('Rapports supprimés', 'success');
    }
}

function deleteReport(reportId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rapport ?')) {
        // Récupérer le token CSRF
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                         document.querySelector('input[name="_token"]')?.value;
        
        if (!csrfToken) {
            showToast('Token CSRF non trouvé', 'error');
            return;
        }
        
        fetch(`/admin/sim-reports/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast('Rapport supprimé avec succès', 'success');
                // Supprimer l'élément de la liste sans recharger la page
                const reportElement = document.querySelector(`[data-report-id="${reportId}"]`);
                if (reportElement) {
                    reportElement.remove();
                } else {
                    location.reload();
                }
            } else {
                showToast(data.message || 'Erreur lors de la suppression', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showToast('Erreur lors de la suppression: ' + error.message, 'error');
        });
    }
}

function exportAllReports() {
    showToast('Export de tous les rapports en cours...', 'info');
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endpush
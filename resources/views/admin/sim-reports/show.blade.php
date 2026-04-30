@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Détails Rapport SIM')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line me-2"></i>
                {{ $report['title'] }}
            </h1>
            <p class="text-muted">Détails du rapport SIM</p>
        </div>
        <div>
            @if($report['status'] == 'completed')
                <a href="{{ route('admin.sim-reports.download', $report['id']) }}" class="btn btn-success me-2">
                    <i class="fas fa-download me-2"></i>Télécharger
                </a>
            @endif
            <a href="{{ route('admin.sim-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Contenu du rapport -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contenu du rapport</h6>
                </div>
                <div class="card-body">
                    @if($report['status'] == 'completed')
                        <div class="report-content">
                            {!! $report['content'] !!}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="sr-only">Génération en cours...</span>
                            </div>
                            <h5>Génération du rapport en cours...</h5>
                            <p class="text-muted">Veuillez patienter, le rapport sera disponible dans quelques instants.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Informations du rapport -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Type :</strong></td>
                            <td>
                                @if($report['type'] == 'monthly')
                                    <span class="badge badge-primary">Mensuel</span>
                                @else
                                    <span class="badge badge-warning">Annuel</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Statut :</strong></td>
                            <td>
                                @if($report['status'] == 'completed')
                                    <span class="badge badge-success">Terminé</span>
                                @else
                                    <span class="badge badge-warning">En cours</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Généré par :</strong></td>
                            <td>{{ $report['generated_by'] }}</td>
                        </tr>
                        <tr>
                            <td><strong>Créé le :</strong></td>
                            <td>{{ $report['created_at']->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Taille :</strong></td>
                            <td>{{ $report['formatted_file_size'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Téléchargements :</strong></td>
                            <td>{{ $report['download_count'] ?? 0 }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($report['status'] == 'completed')
                            <a href="{{ route('admin.sim-reports.download', $report['id']) }}" class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Télécharger PDF
                            </a>
                            
                            <button class="btn btn-info" onclick="previewReport()">
                                <i class="fas fa-eye me-2"></i>Aperçu
                            </button>
                            
                            <button class="btn btn-primary" onclick="shareReport()">
                                <i class="fas fa-share me-2"></i>Partager
                            </button>
                        @else
                            <button class="btn btn-warning" disabled>
                                <i class="fas fa-clock me-2"></i>Génération en cours...
                            </button>
                        @endif
                        
                        <button class="btn btn-danger" onclick="deleteReport()">
                            <i class="fas fa-trash me-2"></i>Supprimer
                        </button>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary">{{ $report['downloads'] }}</div>
                            <small class="text-muted">Téléchargements</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-info">{{ $report['created_at']->diffForHumans() }}</div>
                            <small class="text-muted">Créé</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function previewReport() {
    // Ouvrir l'aperçu dans une nouvelle fenêtre
    window.open('/admin/sim-reports/{{ $report["id"] }}/preview', '_blank');
}

function shareReport() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $report["title"] }}',
            text: 'Rapport SIM - {{ $report["title"] }}',
            url: window.location.href
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas l'API Share
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            alert('Lien copié dans le presse-papiers');
        });
    }
}

function deleteReport() {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce rapport ?')) {
        // Logique de suppression
        alert('Rapport supprimé avec succès');
        window.location.href = '/admin/sim-reports';
    }
}

// Actualiser la page si le rapport est en cours de génération
@if($report['status'] != 'completed')
setTimeout(function() {
    location.reload();
}, 5000);
@endif
</script>
@endsection




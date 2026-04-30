@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Détails Newsletter')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-newspaper me-2"></i>
                {{ $newsletter['title'] }}
            </h1>
            <p class="text-muted">Détails de la newsletter</p>
        </div>
        <div>
            <a href="{{ route('admin.newsletter.edit', $newsletter['id']) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Contenu de la newsletter -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contenu</h6>
                </div>
                <div class="card-body">
                    <h4>{{ $newsletter['subject'] }}</h4>
                    <hr>
                    <div class="newsletter-content">
                        {!! $newsletter['content'] !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Statistiques -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-primary">{{ number_format($newsletter['recipients_count']) }}</div>
                            <small class="text-muted">Destinataires</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-success">{{ number_format($newsletter['opened_count']) }}</div>
                            <small class="text-muted">Ouvertures</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 text-info">{{ number_format($newsletter['clicked_count']) }}</div>
                            <small class="text-muted">Clics</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-warning">{{ number_format(($newsletter['opened_count'] / max($newsletter['recipients_count'], 1)) * 100, 1) }}%</div>
                            <small class="text-muted">Taux d'ouverture</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informations -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Statut :</strong></td>
                            <td>
                                @if($newsletter['status'] == 'sent')
                                    <span class="badge badge-success">Envoyée</span>
                                @elseif($newsletter['status'] == 'draft')
                                    <span class="badge badge-warning">Brouillon</span>
                                @else
                                    <span class="badge badge-info">Programmée</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Créée le :</strong></td>
                            <td>{{ $newsletter['created_at']->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Dernière modification :</strong></td>
                            <td>{{ $newsletter['created_at']->format('d/m/Y H:i') }}</td>
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
                        <a href="{{ route('admin.newsletter.edit', $newsletter['id']) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier
                        </a>
                        
                        @if($newsletter['status'] == 'draft')
                            <button class="btn btn-success" onclick="sendNewsletter({{ $newsletter['id'] }})">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer
                            </button>
                        @endif
                        
                        <button class="btn btn-info" onclick="previewNewsletter()">
                            <i class="fas fa-eye me-2"></i>Aperçu
                        </button>
                        
                        <button class="btn btn-primary" onclick="duplicateNewsletter()">
                            <i class="fas fa-copy me-2"></i>Dupliquer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function sendNewsletter(newsletterId) {
    if (confirm('Êtes-vous sûr de vouloir envoyer cette newsletter ?')) {
        fetch(`/admin/newsletter/${newsletterId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
}

function previewNewsletter() {
    // Ouvrir l'aperçu dans une nouvelle fenêtre
    window.open('/admin/newsletter/{{ $newsletter["id"] }}/preview', '_blank');
}

function duplicateNewsletter() {
    if (confirm('Dupliquer cette newsletter ?')) {
        // Logique de duplication
        alert('Newsletter dupliquée avec succès');
    }
}
</script>
@endsection




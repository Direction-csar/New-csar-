@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Détails de l\'Image')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye me-2"></i>
                Détails de l'Image
            </h1>
            <p class="text-muted">Informations détaillées de l'image</p>
        </div>
        <div>
            <a href="{{ route('admin.galerie.edit', $image->id) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-2"></i>Modifier
            </a>
            <a href="{{ route('admin.galerie.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour à la galerie
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Image -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Image</h6>
                </div>
                <div class="card-body text-center">
                    <img src="{{ $image->image_url }}" 
                         alt="{{ $image->title }}" 
                         class="img-fluid rounded shadow"
                         style="max-height: 500px; object-fit: contain;">
                </div>
            </div>

            <!-- Informations détaillées -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Informations détaillées</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Titre :</strong></td>
                                    <td>{{ $image->title ?: 'Sans titre' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Catégorie :</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ $image->category }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Statut :</strong></td>
                                    <td>
                                        <span class="badge {{ $image->status == 'active' ? 'badge-success' : 'badge-secondary' }}">
                                            {{ ucfirst($image->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>En vedette :</strong></td>
                                    <td>
                                        @if($image->is_featured)
                                            <i class="fas fa-star text-warning"></i> Oui
                                        @else
                                            <i class="fas fa-star text-muted"></i> Non
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nom du fichier :</strong></td>
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
                                    <td><strong>Date d'ajout :</strong></td>
                                    <td>{{ $image->created_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Dernière modification :</strong></td>
                                    <td>{{ $image->updated_at->format('d/m/Y à H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($image->description)
                    <div class="mt-4">
                        <h6><strong>Description :</strong></h6>
                        <p class="text-muted">{{ $image->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.galerie.edit', $image->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-2"></i>Modifier l'image
                        </a>
                        
                        <button class="btn btn-{{ $image->status == 'active' ? 'secondary' : 'success' }}" 
                                onclick="toggleImageStatus({{ $image->id }})">
                            <i class="fas fa-{{ $image->status == 'active' ? 'eye-slash' : 'eye' }} me-2"></i>
                            {{ $image->status == 'active' ? 'Désactiver' : 'Activer' }}
                        </button>

                        <a href="{{ $image->image_url }}" class="btn btn-info" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>Voir en grand
                        </a>

                        <form action="{{ route('admin.galerie.destroy', $image->id) }}" method="POST" 
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Supprimer l'image
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Statistiques</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $image->order }}</h4>
                                <small class="text-muted">Ordre</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $image->created_at->diffForHumans() }}</h4>
                            <small class="text-muted">Ajoutée</small>
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
function toggleImageStatus(imageId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cette image ?')) {
        fetch(`/admin/galerie/${imageId}/toggle-status`, {
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
            } else {
                alert('Erreur lors de la mise à jour du statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la mise à jour du statut');
        });
    }
}
</script>
@endsection

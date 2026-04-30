@extends(request()->routeIs('ctc.*') ? 'layouts.ctc' : 'layouts.admin')

@section('title', 'Modifier Newsletter')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>
                Modifier Newsletter
            </h1>
            <p class="text-muted">Modifier : {{ $newsletter['title'] }}</p>
        </div>
        <div>
            <a href="{{ route('admin.newsletter.show', $newsletter['id']) }}" class="btn btn-info me-2">
                <i class="fas fa-eye me-2"></i>Voir
            </a>
            <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Retour
            </a>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contenu de la newsletter</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.newsletter.update', $newsletter['id']) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Titre de la newsletter *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title', $newsletter['title']) }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="subject" class="form-label">Sujet de l'email *</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   id="subject" name="subject" value="{{ old('subject', $newsletter['subject']) }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="content" class="form-label">Contenu *</label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" name="content" rows="15" required>{{ old('content', $newsletter['content']) }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="recipients" class="form-label">Destinataires *</label>
                            <select class="form-control @error('recipients') is-invalid @enderror" id="recipients" name="recipients[]" multiple required>
                                <option value="all" {{ in_array('all', old('recipients', [])) ? 'selected' : '' }}>Tous les abonnés</option>
                                <option value="admins" {{ in_array('admins', old('recipients', [])) ? 'selected' : '' }}>Administrateurs</option>
                                <option value="responsables" {{ in_array('responsables', old('recipients', [])) ? 'selected' : '' }}>Responsables</option>
                                <option value="agents" {{ in_array('agents', old('recipients', [])) ? 'selected' : '' }}>Agents</option>
                            </select>
                            @error('recipients')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="schedule" name="schedule" value="1" {{ old('schedule') ? 'checked' : '' }}>
                                <label class="form-check-label" for="schedule">
                                    Programmer l'envoi
                                </label>
                            </div>
                        </div>

                        <div id="schedule-datetime" class="form-group mb-3" style="display: none;">
                            <label for="scheduled_at" class="form-label">Date et heure d'envoi</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.newsletter.index') }}" class="btn btn-secondary me-2">Annuler</a>
                            <button type="submit" class="btn btn-primary">
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
                    <h6 class="m-0 font-weight-bold text-primary">Aperçu</h6>
                </div>
                <div class="card-body">
                    <div id="preview-content" class="border p-3" style="min-height: 200px;">
                        {!! $newsletter['content'] !!}
                    </div>
                </div>
            </div>

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
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('schedule').addEventListener('change', function() {
    const scheduleDatetime = document.getElementById('schedule-datetime');
    if (this.checked) {
        scheduleDatetime.style.display = 'block';
    } else {
        scheduleDatetime.style.display = 'none';
    }
});

// Aperçu en temps réel
document.getElementById('content').addEventListener('input', function() {
    const preview = document.getElementById('preview-content');
    preview.innerHTML = this.value || '<p class="text-muted">L\'aperçu apparaîtra ici...</p>';
});
</script>
@endsection




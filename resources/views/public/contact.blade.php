@extends('layouts.public')

@section('title', __('pages.contact'))

@push('styles')
<style>
.map-container {
    position: relative;
    margin: 1rem 0;
}

.map-container iframe {
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.map-container iframe:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.text-primary:hover {
    color: #0d6efd !important;
    text-decoration: underline !important;
}

.btn-outline-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
                <h1 class="display-4 fw-bold text-primary mb-3">
                    <i class="fas fa-envelope me-3"></i>{{ __('messages.contact.title') }}
                </h1>
                <p class="lead text-muted">{{ __('messages.contact.subtitle') }}</p>
            </div>
        </div>

        <div class="row">
        <!-- Informations de contact -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt me-2"></i>Nos coordonnées
                    </h3>
                    
                    <div class="mb-3">
                        <strong>{{ __('messages.contact.address') }}</strong><br>
                        <a href="https://maps.google.com/?q=Rue+El+Hadji+Amadou+Assane+Ndoye+29+Dakar+Senegal" 
                           target="_blank" 
                           class="text-decoration-none text-primary">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            Rue El Hadji Amadou Assane Ndoye, 29<br>
                            Dakar, Sénégal
                        </a>
                        <br>
                        <small class="text-muted">
                            <i class="fas fa-external-link-alt me-1"></i>
                            Cliquez pour ouvrir dans Google Maps
                        </small>
                        </div>
                        
                    <div class="mb-3">
                        <strong>{{ __('messages.contact.phone') }}</strong><br>
                        <a href="tel:+221331234567" class="text-decoration-none">+221 33 123 45 67</a>
                        </div>
                        
                    <div class="mb-3">
                        <strong>{{ __('messages.contact.email') }}</strong><br>
                        <a href="mailto:contact@csar.sn" class="text-decoration-none">contact@csar.sn</a>
                        </div>
                        
                    <div class="mb-3">
                        <strong>Site web</strong><br>
                        <a href="https://www.csar.sn" target="_blank" class="text-decoration-none">www.csar.sn</a>
                </div>

                    <div class="mb-3">
                        <strong>Horaires d'ouverture</strong><br>
                        <small class="text-muted">
                            Plateforme en ligne: 24h/24 - 7j/7<br>
                            Bureau administratif: Lundi - Vendredi: 8h00 - 17h00
                        </small>
                    </div>
                    </div>
                </div>
            </div>
            
        <!-- Formulaire de contact -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('messages.contact.form_title') }}
                    </h3>
                    
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('contact.submit', ['locale' => app()->getLocale()]) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                                <label for="name" class="form-label">Nom complet *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                        <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        
                        <div class="mb-3">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}">
                                @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                        <div class="mb-3">
                                <label for="subject" class="form-label">Sujet *</label>
                                <select class="form-select @error('subject') is-invalid @enderror" 
                                        id="subject" name="subject" required onchange="toggleAutreSubject(this)">
                                    <option value="">Sélectionnez un sujet</option>
                                    <option value="Demande d'information" {{ old('subject') == 'Demande d\'information' ? 'selected' : '' }}>Demande d'information</option>
                                    <option value="Demande d'assistance" {{ old('subject') == 'Demande d\'assistance' ? 'selected' : '' }}>Demande d'assistance</option>
                                    <option value="Partenariat" {{ old('subject') == 'Partenariat' ? 'selected' : '' }}>Partenariat</option>
                                    <option value="Don / Soutien" {{ old('subject', request('sujet')) == 'Don / Soutien' || request('sujet') == 'don' ? 'selected' : '' }}>Don / Soutien</option>
                                    <option value="Presse / Interview / Accréditation" {{ old('subject') == 'Presse / Interview / Accréditation' ? 'selected' : '' }}>Presse / Interview / Accréditation</option>
                                    <option value="Média" {{ old('subject') == 'Média' ? 'selected' : '' }}>Média</option>
                                    <option value="Autre" {{ old('subject') == 'Autre' ? 'selected' : '' }}>Autre</option>
                                </select>
                                @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                        </div>
                        <div class="mb-3" id="autre-subject-div" style="display:{{ old('subject') == 'Autre' ? 'block' : 'none' }}">
                            <label for="subject_autre" class="form-label">Précisez votre sujet *</label>
                            <input type="text" class="form-control" id="subject_autre" name="subject_autre"
                                   value="{{ old('subject_autre') }}"
                                   placeholder="Décrivez brièvement votre sujet..."
                                   {{ old('subject') == 'Autre' ? 'required' : '' }}>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="5" 
                                      placeholder="Décrivez votre demande en détail..." required>{{ old('message') }}</textarea>
                            @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                                <label class="form-check-label" for="privacy">
                                    J'accepte que mes données personnelles soient utilisées pour traiter ma demande *
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Envoyer le message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
                </div>
            </div>

    <!-- Section Presse -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <h4 class="card-title">
                        <i class="fas fa-microphone me-2"></i>{{ __('messages.contact.press') }}
                    </h4>
                    <p class="card-text mb-2">Demande d'interview, accréditation ou dossier de presse : utilisez le formulaire ci-dessus en choisissant le sujet <strong>« Presse / Interview / Accréditation »</strong> ou écrivez à <a href="mailto:contact@csar.sn">contact@csar.sn</a>.</p>
                    <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm">
                        <i class="fab fa-linkedin-in me-1"></i>Suivez-nous sur LinkedIn
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Section urgences -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h4 class="card-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ __('messages.contact.emergencies') }}
                    </h4>
                    <p class="card-text">
                        {{ __('messages.contact.emergencies_desc') }}
                    </p>
                    <a href="tel:+221776459242" class="btn btn-danger btn-lg">
                        <i class="fas fa-phone me-2"></i>{{ __('messages.contact.call_now') }}: +221 77 645 92 42
                            </a>
                            </div>
                            </div>
                </div>
            </div>

                <!-- Carte de localisation -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        <i class="fas fa-map me-2"></i>Notre localisation
                    </h4>
                    <p class="card-text">Trouvez-nous facilement avec cette carte interactive</p>
                    <p class="text-muted small mb-3">
                        <strong>CSAR</strong> - Commissariat à la Sécurité Alimentaire et à la Résilience
                    </p>
                    
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps?q=Rue+El+Hadji+Amadou+Assane+Ndoye+29,+Dakar,+Sénégal&output=embed&zoom=17"
                            width="100%" 
                            height="450" 
                            style="border:0; border-radius: 10px;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    
                    <div class="text-center mt-3">
                            <a href="https://maps.google.com/?q=Rue+El+Hadji+Amadou+Assane+Ndoye+29+Dakar+Senegal" 
                               target="_blank" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-external-link-alt me-2"></i>
                                Ouvrir dans Google Maps
                            </a>
                            </div>
                            </div>
                        </div>
        </div>
                        </div>
                        
    <!-- Section réseaux sociaux -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h4 class="card-title">
                        <i class="fas fa-share-alt me-2"></i>Suivez-nous
                    </h4>
                    <p class="card-text">Restez connecté avec le CSAR</p>
                    
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="https://www.instagram.com/csar.sn/?igsh=MWcxbTJnNzBnZGo5Mg%3D%3D&utm_source=qr#" target="_blank" class="btn btn-outline-primary">
                            <i class="fab fa-instagram me-2"></i>Instagram
                        </a>
                        <a href="https://x.com/csar_sn?s=21" target="_blank" class="btn btn-outline-info">
                            <i class="fab fa-twitter me-2"></i>Twitter
                        </a>
                        <a href="https://www.facebook.com/people/Commissariat-%C3%A0-la-S%C3%A9curit%C3%A9-Alimentaire-et-%C3%A0-la-R%C3%A9silience/61562947586356/?mibextid=wwXIfr&rdid=rdi0HoJAMnm5SUWB&share_url=https%3A%2F%2Fwww.facebook.com%2Fshare%2F1A15LpvcqT%2F%3Fmibextid%3DwwXIfr" target="_blank" class="btn btn-outline-primary">
                            <i class="fab fa-facebook-f me-2"></i>Facebook
                        </a>
                        <a href="https://www.linkedin.com/company/commissariat-%C3%A0-la-s%C3%A9curit%C3%A9-alimentaire-et-%C3%A0-la-r%C3%A9silience/" target="_blank" class="btn btn-outline-info">
                            <i class="fab fa-linkedin-in me-2"></i>LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
function toggleAutreSubject(select) {
    const div = document.getElementById('autre-subject-div');
    const input = document.getElementById('subject_autre');
    if (select.value === 'Autre') {
        div.style.display = 'block';
        input.required = true;
    } else {
        div.style.display = 'none';
        input.required = false;
        input.value = '';
    }
}
</script>
@endpush
@endsection
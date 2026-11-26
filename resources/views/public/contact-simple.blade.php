@extends('layouts.public')

@section('title', 'Contact')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12 text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="fas fa-envelope me-3"></i>Contactez-nous
            </h1>
            <p class="lead text-muted">Nous sommes là pour vous aider et répondre à vos questions</p>
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
                        <strong>Adresse</strong><br>
                        Rue El Hadji Amadou Assane Ndoye, 29<br>
                        Dakar, Sénégal
                    </div>
                    
                    <div class="mb-3">
                        <strong>Téléphone</strong><br>
                        <a href="tel:+221776459242" class="text-decoration-none">+221 77 645 92 42</a>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Email</strong><br>
                        <a href="mailto:contact@csar.sn" class="text-decoration-none">contact@csar.sn</a>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Site web</strong><br>
                        <a href="https://www.csar.sn" target="_blank" class="text-decoration-none">www.csar.sn</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulaire de contact -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="card-title">
                        <i class="fas fa-paper-plane me-2"></i>Envoyez-nous un message
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

                    <form action="{{ route('test.contact.submit') }}" method="POST" id="contactForm">
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
                                    id="subject" name="subject" required>
                                <option value="">Sélectionnez un sujet</option>
                                <option value="Demande d'information" {{ old('subject') == 'Demande d\'information' ? 'selected' : '' }}>Demande d'information</option>
                                <option value="Demande d'assistance" {{ old('subject') == 'Demande d\'assistance' ? 'selected' : '' }}>Demande d'assistance</option>
                                <option value="Partenariat" {{ old('subject') == 'Partenariat' ? 'selected' : '' }}>Partenariat</option>
                                <option value="Média" {{ old('subject') == 'Média' ? 'selected' : '' }}>Média</option>
                                <option value="Autre" {{ old('subject') == 'Autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
</div>
@endsection
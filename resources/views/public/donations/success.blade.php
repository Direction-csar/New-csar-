@extends('layouts.public')

@section('title', 'Don effectué avec succès')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <div class="success-icon-wrapper animate__animated animate__bounceIn">
                            <i class="fas fa-heart fa-4x text-danger"></i>
                        </div>
                    </div>

                    <h1 class="h2 mb-3">Merci pour votre générosité !</h1>

                    <p class="lead text-muted mb-4">
                        Grâce à vous, la Croix-Rouge Sénégalaise peut continuer à venir en aide
                        aux populations vulnérables partout dans le pays.
                    </p>

                    <div class="bg-light rounded p-4 mb-4">
                        <p class="mb-1 text-muted">Montant du don</p>
                        <p class="h3 text-success fw-bold mb-2">
                            {{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency }}
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $donation->created_at->format('d/m/Y à H:i') }}
                        </p>
                    </div>

                    <div class="alert alert-success mb-4">
                        <h5 class="alert-heading">
                            <i class="fas fa-receipt me-2"></i>Code de suivi : {{ $donation->id }}
                        </h5>
                        <p class="mb-0">Conservez ce numéro pour suivre l'utilisation de votre don.</p>
                    </div>

                    <p class="text-muted mb-4">
                        Un email de confirmation a été envoyé à <strong>{{ $donation->email }}</strong>.
                    </p>

                    <hr class="my-4">

                    <p class="fw-bold mb-3">Partagez votre geste et encouragez d'autres dons :</p>
                    <div class="d-flex justify-content-center gap-2 mb-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                            <i class="fab fa-facebook-f me-1"></i>Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode('Je viens de faire un don à la Croix-Rouge Sénégalaise ! Rejoignez le mouvement solidaire.') }}&url={{ urlencode(url()->current()) }}"
                           class="btn btn-outline-info btn-sm" target="_blank" rel="noopener">
                            <i class="fab fa-twitter me-1"></i>Twitter
                        </a>
                        <a href="https://wa.me/?text={{ urlencode('Je viens de faire un don à la Croix-Rouge Sénégalaise ! Rejoignez le mouvement solidaire.') }}"
                           class="btn btn-outline-success btn-sm" target="_blank" rel="noopener">
                            <i class="fab fa-whatsapp me-1"></i>WhatsApp
                        </a>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                        <a href="{{ route('donations.track') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Suivre mon don
                        </a>
                        <a href="{{ route('donations.index') }}" class="btn btn-outline-danger btn-lg">
                            <i class="fas fa-heart me-2"></i>Faire un autre don
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.success-icon-wrapper {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fee 0%, #ffe5e5 100%);
}
</style>
@endsection

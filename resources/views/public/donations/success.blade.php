@extends('layouts.public')

@section('title', 'Don effectué avec succès')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    <h1 class="h2 mb-3">Merci pour votre don !</h1>
                    <p class="text-muted mb-4">
                        Votre contribution de <strong>{{ number_format($donation->amount, 0, ',', ' ') }} {{ $donation->currency }}</strong>
                        a été reçue avec succès.
                    </p>

                    <div class="alert alert-success mb-4">
                        <h5 class="alert-heading">Code de suivi : {{ $donation->id }}</h5>
                        <p class="mb-0">Conservez ce numéro pour suivre l'utilisation de votre don.</p>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i>Retour à l'accueil
                        </a>
                        <a href="{{ route('donations.track') }}" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Suivre mon don
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

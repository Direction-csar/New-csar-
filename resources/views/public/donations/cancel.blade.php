@extends('layouts.public')

@section('title', 'Paiement annulé')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle fa-5x text-warning"></i>
                    </div>
                    <h1 class="h2 mb-3">Paiement annulé</h1>
                    <p class="text-muted mb-4">
                        Le paiement a été annulé ou n'a pas pu être complété.
                        Aucun montant n'a été débité.
                    </p>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="{{ route('donations.index') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-redo me-2"></i>Réessayer
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                            <i class="fas fa-home me-2"></i>Accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

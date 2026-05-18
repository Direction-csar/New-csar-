@extends('layouts.public')

@section('title', 'Merci pour votre participation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 text-center">
            <div class="card shadow-sm border-0 p-5">
                <div class="mb-4">
                    <div class="bg-success bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 110px; height: 110px;">
                        <i class="fas fa-check-circle text-success" style="font-size: 4.5rem;"></i>
                    </div>
                </div>
                <h2 class="fw-bold mb-3">Merci !</h2>
                <p class="lead text-muted">
                    Votre questionnaire a bien été enregistré. Votre contribution nous aidera à améliorer l'assurance maladie du personnel CSAR.
                </p>
                <a href="{{ url('/') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

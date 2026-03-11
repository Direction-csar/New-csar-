@extends('layouts.public')

@section('title', 'SIM CSAR - Demande d\'accès aux données')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h1 class="mb-2">Demande d'accès aux données SIM</h1>
                    <p class="text-muted mb-4">Les données détaillées sont diffusées uniquement sur autorisation de l'administration centrale du CSAR.</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form method="POST" action="{{ route('sim.request-access.store') }}">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom du demandeur</label>
                                <input type="text" name="requester_name" class="form-control" value="{{ old('requester_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Organisation</label>
                                <input type="text" name="organization" class="form-control" value="{{ old('organization') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fonction</label>
                                <input type="text" name="role_title" class="form-control" value="{{ old('role_title') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Objet</label>
                                <input type="text" name="request_subject" class="form-control" value="{{ old('request_subject') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Portée demandée</label>
                                <select name="requested_scope" class="form-select" required>
                                    <option value="aggregated">Statistiques agrégées</option>
                                    <option value="regional">Données régionales</option>
                                    <option value="detailed">Données détaillées</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Types de données</label>
                                <select name="requested_data_types[]" class="form-select" multiple>
                                    <option value="prices">Prix</option>
                                    <option value="stocks">Stocks</option>
                                    <option value="offers">Offres</option>
                                    <option value="markets">Marchés</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Période début</label>
                                <input type="date" name="period_start" class="form-control" value="{{ old('period_start') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Période fin</label>
                                <input type="date" name="period_end" class="form-control" value="{{ old('period_end') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Usage prévu / justification</label>
                                <textarea name="purpose" class="form-control" rows="5" required>{{ old('purpose') }}</textarea>
                            </div>
                        </div>
                        <div class="mt-4 d-flex gap-2">
                            <button class="btn btn-success">Envoyer la demande</button>
                            <a href="{{ route('sim.dashboard') }}" class="btn btn-outline-secondary">Retour</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'SIM — Détail demande d\'accès')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Demande d'accès — {{ $simDataAccessRequest->requester_name }}</h1>
        <a href="{{ route('admin.sim.access-requests') }}" class="btn btn-outline-secondary">Retour</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-3">
                <div class="card-header">Informations du demandeur</div>
                <div class="card-body">
                    <p><strong>Nom :</strong> {{ $simDataAccessRequest->requester_name }}</p>
                    <p><strong>Organisation :</strong> {{ $simDataAccessRequest->organization }}</p>
                    <p><strong>Fonction :</strong> {{ $simDataAccessRequest->role_title }}</p>
                    <p><strong>Email :</strong> {{ $simDataAccessRequest->email }}</p>
                    <p><strong>Téléphone :</strong> {{ $simDataAccessRequest->phone }}</p>
                    <p><strong>Sujet :</strong> {{ $simDataAccessRequest->request_subject }}</p>
                    <p><strong>Périmètre demandé :</strong> {{ $simDataAccessRequest->requested_scope }}</p>
                    <p><strong>Types de données :</strong> {{ is_array($simDataAccessRequest->requested_data_types) ? implode(', ', $simDataAccessRequest->requested_data_types) : $simDataAccessRequest->requested_data_types }}</p>
                    <p><strong>Période :</strong> {{ $simDataAccessRequest->period_start?->format('d/m/Y') ?? '-' }} — {{ $simDataAccessRequest->period_end?->format('d/m/Y') ?? '-' }}</p>
                    <p><strong>Objectif :</strong> {{ $simDataAccessRequest->purpose }}</p>
                    <p><strong>Statut :</strong> <span class="badge bg-secondary">{{ $simDataAccessRequest->status }}</span></p>
                    @if($simDataAccessRequest->admin_notes)
                        <p><strong>Notes admin :</strong> {{ $simDataAccessRequest->admin_notes }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @if($simDataAccessRequest->status === 'pending')
                <div class="card shadow">
                    <div class="card-header">Décision</div>
                    <div class="card-body">
                        <form action="{{ route('admin.sim.access-requests.decision', $simDataAccessRequest) }}" method="post">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Statut *</label>
                                <select name="status" class="form-select" required>
                                    <option value="approved">Approuver</option>
                                    <option value="rejected">Rejeter</option>
                                    <option value="restricted">Accès restreint</option>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Notes</label>
                                <textarea name="admin_notes" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Expiration (optionnel)</label>
                                <input type="date" name="expires_at" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-primary">Enregistrer la décision</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

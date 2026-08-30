@extends('layouts.admin')

@section('title', 'Modifier l\'Utilisateur')
@section('page-title', 'Modifier l\'Utilisateur')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1 text-dark fw-bold">✏️ Modifier l'Utilisateur</h1>
                        <p class="text-muted mb-0 small">{{ $user->name ?? 'Utilisateur' }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye me-1"></i>Voir
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulaire -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <!-- Informations personnelles -->
                        <div class="col-lg-6 mb-3">
                            <h6 class="fw-bold mb-3">👤 Informations Personnelles</h6>

                            <div class="mb-3">
                                <label for="name" class="form-label small fw-bold">Nom complet *</label>
                                <input type="text" class="form-control form-control-sm" id="name" name="name"
                                       value="{{ $user->name ?? '' }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label small fw-bold">Email *</label>
                                <input type="email" class="form-control form-control-sm" id="email" name="email"
                                       value="{{ $user->email ?? '' }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label small fw-bold">Téléphone *</label>
                                <input type="tel" class="form-control form-control-sm" id="phone" name="phone"
                                       value="{{ $user->phone ?? '' }}" required>
                            </div>
                        </div>

                        <!-- Rôle et statut -->
                        <div class="col-lg-6 mb-3">
                            <h6 class="fw-bold mb-3">🔐 Rôle et Accès</h6>

                            <div class="mb-3">
                                <label for="role" class="form-label small fw-bold">Rôle *</label>
                                <select class="form-select form-select-sm" id="role" name="role" required>
                                    <option value="">Sélectionner un rôle</option>
                                    <option value="admin" {{ ($user->role ?? '') === 'admin' ? 'selected' : '' }}>Administrateur</option>
                                    <option value="dg" {{ ($user->role ?? '') === 'dg' ? 'selected' : '' }}>Directeur Général</option>
                                    <option value="responsable" {{ ($user->role ?? '') === 'responsable' ? 'selected' : '' }}>Responsable</option>
                                    <option value="agent" {{ ($user->role ?? '') === 'agent' ? 'selected' : '' }}>Agent</option>
                                    <option value="distributeur" {{ ($user->role ?? '') === 'distributeur' ? 'selected' : '' }}>Distributeur d'aide</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="status" class="form-label small fw-bold">Statut *</label>
                                <select class="form-select form-select-sm" id="status" name="status" required>
                                    <option value="">Sélectionner un statut</option>
                                    <option value="actif" {{ ($user->status ?? '') === 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="inactif" {{ ($user->status ?? '') === 'inactif' ? 'selected' : '' }}>Inactif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Informations de suivi -->
                    <div class="row">
                        <div class="col-12 mb-3">
                            <h6 class="fw-bold mb-3">📊 Informations de Suivi</h6>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Date de création</label>
                                    <p class="mb-0">{{ $user->created_at->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Dernière modification</label>
                                    <p class="mb-0">{{ $user->updated_at->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Dernière connexion</label>
                                    <p class="mb-0">{{ $user->last_login ? \Carbon\Carbon::parse($user->last_login)->format('d/m/Y H:i') : 'Jamais' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-times me-1"></i>Annuler
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-save me-1"></i>Mettre à jour
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
  /* Optimisation de l'espace */
  .container-fluid { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }

  /* Cards plus compactes */
  .card-modern { margin-bottom: 0.5rem !important; }

  /* Réduction des marges */
  .row { margin-bottom: 0.5rem !important; }
  .mb-2 { margin-bottom: 0.5rem !important; }

  /* Textes plus compacts */
  .h4 { font-size: 1.25rem !important; }
  .h6 { font-size: 0.9rem !important; }

  /* Boutons plus petits et cliquables */
  .btn-sm {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.8rem !important;
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 10 !important;
  }

  /* Formulaires compacts */
  .form-control-sm, .form-select-sm {
    font-size: 0.8rem !important;
    padding: 0.25rem 0.5rem !important;
  }

  /* Labels */
  .form-label {
    font-size: 0.8rem !important;
    margin-bottom: 0.25rem !important;
  }
</style>
@endpush

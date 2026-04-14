@extends('layouts.admin')

@section('title', 'Modifier Partenaire')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-edit me-2"></i>Modifier partenaire #{{ $partenaire->id }}</h1>
        <a href="{{ route('admin.partenaires.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-2"></i>Retour</a>
    </div>

    <div class="card shadow">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Formulaire</h6></div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.partenaires.update', $partenaire->id) }}" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nom du partenaire *</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $partenaire->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Organisation</label>
                            <input type="text" name="organization" class="form-control" value="{{ old('organization', $partenaire->organization) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type *</label>
                            <select name="type" class="form-control" required>
                                <option value="ong" {{ old('type', $partenaire->type) == 'ong' ? 'selected' : '' }}>ONG</option>
                                <option value="agency" {{ old('type', $partenaire->type) == 'agency' ? 'selected' : '' }}>Agence</option>
                                <option value="institution" {{ old('type', $partenaire->type) == 'institution' ? 'selected' : '' }}>Institution</option>
                                <option value="private" {{ old('type', $partenaire->type) == 'private' ? 'selected' : '' }}>Privé</option>
                                <option value="government" {{ old('type', $partenaire->type) == 'government' ? 'selected' : '' }}>Gouvernement</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rôle / Description</label>
                            <textarea name="role" class="form-control" rows="3">{{ old('role', $partenaire->role) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $partenaire->email) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $partenaire->phone) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Site web</label>
                            <input type="url" name="website" class="form-control" value="{{ old('website', $partenaire->website) }}" placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type de partenariat</label>
                            <input type="text" name="partnership_type" class="form-control" value="{{ old('partnership_type', $partenaire->partnership_type) }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Début partenariat</label>
                                <input type="date" name="partnership_start_date" class="form-control" value="{{ old('partnership_start_date', $partenaire->partnership_start_date?->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fin partenariat</label>
                                <input type="date" name="partnership_end_date" class="form-control" value="{{ old('partnership_end_date', $partenaire->partnership_end_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Statut *</label>
                            <select name="status" class="form-control" required>
                                <option value="active" {{ old('status', $partenaire->status) == 'active' ? 'selected' : '' }}>Actif</option>
                                <option value="inactive" {{ old('status', $partenaire->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                <option value="pending" {{ old('status', $partenaire->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Logo</label>
                            @if($partenaire->logo)
                                <div class="mb-2"><img src="{{ asset('storage/' . $partenaire->logo) }}" class="img-thumbnail" style="max-height:80px"></div>
                            @endif
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Position</label>
                            <input type="number" name="position" class="form-control" value="{{ old('position', $partenaire->position) }}" min="0">
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ old('is_featured', $partenaire->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">Mettre en avant</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3"><i class="fas fa-save me-2"></i>Mettre à jour</button>
            </form>
        </div>
    </div>
</div>
@endsection

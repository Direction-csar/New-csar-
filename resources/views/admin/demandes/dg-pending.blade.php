@extends('layouts.admin')

@section('title', 'Validation DG - Demandes en attente')
@section('page-title', 'Validation DG - Demandes en attente')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3 bg-warning bg-opacity-10 border-warning">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-3d me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-stamp" style="font-size: 1.2rem;"></i>
                        </div>
                        <div>
                            <h1 class="h4 mb-1 text-dark fw-bold">Validation Direction Générale</h1>
                            <p class="text-muted mb-0 small">Demandes scannées en attente de validation par le DG</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Toutes les demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Compteur -->
    <div class="row mb-2">
        <div class="col-md-4">
            <div class="card-modern p-3 bg-primary bg-opacity-10">
                <div class="text-center">
                    <h2 class="fw-bold text-primary">{{ $pendingCount }}</h2>
                    <p class="text-muted mb-0">Demandes à valider</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern p-3 bg-success bg-opacity-10">
                <div class="text-center">
                    <h2 class="fw-bold text-success">{{ $validatedToday }}</h2>
                    <p class="text-muted mb-0">Validées aujourd'hui</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card-modern p-3 bg-danger bg-opacity-10">
                <div class="text-center">
                    <h2 class="fw-bold text-danger">{{ $rejectedToday }}</h2>
                    <p class="text-muted mb-0">Rejetées aujourd'hui</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des demandes à valider -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <h6 class="fw-bold mb-3"><i class="fas fa-list me-2 text-warning"></i>Demandes en attente de validation DG</h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Demandeur</th>
                                <th>Objet</th>
                                <th>Type</th>
                                <th>Scan</th>
                                <th>Signé</th>
                                <th>Soumis le</th>
                                <th width="180">Actions DG</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($demandes as $demande)
                            <tr>
                                <td><strong class="text-primary">{{ $demande->tracking_code }}</strong></td>
                                <td>
                                    <strong>{{ $demande->full_name }}</strong>
                                    <br><small class="text-muted">{{ $demande->phone }}</small>
                                </td>
                                <td>{{ Str::limit($demande->subject, 40) }}</td>
                                <td><span class="badge bg-info">{{ ucfirst($demande->type ?? 'N/A') }}</span></td>
                                <td>
                                    @if($demande->scan_file)
                                    <a href="{{ asset('storage/' . $demande->scan_file) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-file-pdf"></i> Voir
                                    </a>
                                    @else
                                    <span class="badge bg-secondary">Non scanné</span>
                                    @endif
                                </td>
                                <td>
                                    @if($demande->dg_signature_file)
                                    <a href="{{ asset('storage/' . $demande->dg_signature_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-signature"></i> Voir
                                    </a>
                                    @else
                                    <span class="badge bg-secondary">Non signé</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</small>
                                    <br><small class="text-muted">{{ \Carbon\Carbon::parse($demande->created_at)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <form action="{{ route('admin.demandes.dg-validate', $demande->id) }}" method="POST" class="d-flex gap-1">
                                        @csrf
                                        <button type="submit" name="dg_approved" value="1" class="btn btn-success btn-sm flex-fill" title="Valider">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="submit" name="dg_approved" value="0" class="btn btn-danger btn-sm flex-fill" title="Rejeter">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <a href="{{ route('admin.demandes.show', $demande->id) }}" class="btn btn-info btn-sm" title="Détails">
                                            <i class="fas fa-eye text-white"></i>
                                        </a>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                                        <h5>Aucune demande en attente</h5>
                                        <p>Toutes les demandes ont été traitées par la Direction Générale.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($demandes->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $demandes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('page-title', 'Tableau de bord Workflow')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3 bg-gradient" style="background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-3d me-3" style="width: 55px; height: 55px; background: rgba(255,255,255,0.2);">
                            <i class="fas fa-tasks" style="font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <h1 class="h4 mb-1 fw-bold">Tableau de bord Workflow</h1>
                            <p class="mb-0 small opacity-75">
                                Rôle : <span class="badge bg-light text-dark">{{ ucfirst($role) }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.demandes.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-list me-1"></i>Toutes les demandes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats rapides -->
    <div class="row mb-3">
        @if($canSign)
        <div class="col-md-4 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-warning">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-signature" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-warning">{{ $signCount }}</h3>
                        <p class="text-muted mb-0 small">À signer</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($canScan)
        <div class="col-md-4 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #0dcaf0, #0a93b8);">
                        <i class="fas fa-file-pdf" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-info">{{ $scanCount }}</h3>
                        <p class="text-muted mb-0 small">À scanner</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($canValidate)
        <div class="col-md-4 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <i class="fas fa-stamp" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-success">{{ $validateCount }}</h3>
                        <p class="text-muted mb-0 small">À valider DG</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Section À signer -->
    @if($canSign && $signCount > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-3d me-2" style="width: 36px; height: 36px; background: linear-gradient(135deg, #f59e0b, #d97706);">
                        <i class="fas fa-signature" style="font-size: 0.8rem;"></i>
                    </div>
                    <h5 class="mb-0 fw-bold">Demandes à signer <span class="badge bg-warning">{{ $signCount }}</span></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Code suivi</th>
                                <th>Demandeur</th>
                                <th>Objet</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($toSign as $req)
                            <tr>
                                <td><span class="badge bg-primary">{{ $req->tracking_code }}</span></td>
                                <td>{{ $req->full_name }}</td>
                                <td>{{ Str::limit($req->subject, 40) }}</td>
                                <td><small>{{ $req->created_at->format('d/m/Y') }}</small></td>
                                <td>
                                    <a href="{{ route('admin.demandes.edit', $req->id) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-signature me-1"></i>Signer
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section À scanner -->
    @if($canScan && $scanCount > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-3d me-2" style="width: 36px; height: 36px; background: linear-gradient(135deg, #0dcaf0, #0a93b8);">
                        <i class="fas fa-file-pdf" style="font-size: 0.8rem;"></i>
                    </div>
                    <h5 class="mb-0 fw-bold">Demandes à scanner <span class="badge bg-info">{{ $scanCount }}</span></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Code suivi</th>
                                <th>Demandeur</th>
                                <th>Objet</th>
                                <th>Signé le</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($toScan as $req)
                            <tr>
                                <td><span class="badge bg-primary">{{ $req->tracking_code }}</span></td>
                                <td>{{ $req->full_name }}</td>
                                <td>{{ Str::limit($req->subject, 40) }}</td>
                                <td><small>{{ $req->updated_at->format('d/m/Y') }}</small></td>
                                <td>
                                    @if($req->dg_signature_file)
                                    <a href="{{ asset('storage/' . $req->dg_signature_file) }}" target="_blank" class="btn btn-outline-primary btn-sm me-1">
                                        <i class="fas fa-eye me-1"></i>Voir doc
                                    </a>
                                    @endif
                                    <a href="{{ route('admin.demandes.edit', $req->id) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-file-pdf me-1"></i>Scanner
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section À valider DG -->
    @if($canValidate && $validateCount > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-3d me-2" style="width: 36px; height: 36px; background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <i class="fas fa-stamp" style="font-size: 0.8rem;"></i>
                    </div>
                    <h5 class="mb-0 fw-bold">Demandes à valider DG <span class="badge bg-success">{{ $validateCount }}</span></h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Code suivi</th>
                                <th>Demandeur</th>
                                <th>Objet</th>
                                <th>Scan</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($toValidate as $req)
                            <tr>
                                <td><span class="badge bg-primary">{{ $req->tracking_code }}</span></td>
                                <td>{{ $req->full_name }}</td>
                                <td>{{ Str::limit($req->subject, 40) }}</td>
                                <td>
                                    @if($req->scan_file)
                                    <a href="{{ asset('storage/' . $req->scan_file) }}" target="_blank" class="btn btn-outline-info btn-xs">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @else
                                    <span class="badge bg-secondary">Aucun</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.demandes.edit', $req->id) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-stamp me-1"></i>Valider
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(($canSign && $signCount == 0) && ($canScan && $scanCount == 0) && ($canValidate && $validateCount == 0))
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-5 text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5 class="fw-bold">Tout est à jour !</h5>
                <p class="text-muted mb-0">Aucune demande n'attend votre action pour le moment.</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

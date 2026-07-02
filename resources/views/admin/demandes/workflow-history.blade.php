@extends('layouts.admin')

@section('page-title', 'Historique des actions Workflow')

@section('content')
<div class="container-fluid px-3">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3 bg-gradient" style="background: linear-gradient(135deg, #1e3a5f, #2c5282); color: white;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="icon-3d me-3" style="width: 55px; height: 55px; background: rgba(255,255,255,0.2);">
                            <i class="fas fa-history" style="font-size: 1.4rem;"></i>
                        </div>
                        <div>
                            <h1 class="h4 mb-1 fw-bold">Historique des Actions</h1>
                            <p class="mb-0 small opacity-75">Journal d'audit du workflow des demandes</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.demandes.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Retour
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats rapides -->
    <div class="row mb-3">
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-primary">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #0d6efd, #0a58ca);">
                        <i class="fas fa-signature" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-primary">{{ $totalSignatures }}</h3>
                        <p class="text-muted mb-0 small">Documents signés</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-info">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #0dcaf0, #0a93b8);">
                        <i class="fas fa-file-pdf" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-info">{{ $totalScans }}</h3>
                        <p class="text-muted mb-0 small">Documents scannés</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-success">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #22c55e, #16a34a);">
                        <i class="fas fa-stamp" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-success">{{ $totalValidated }}</h3>
                        <p class="text-muted mb-0 small">Validées DG</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card-modern p-3 h-100 border-start border-4 border-danger">
                <div class="d-flex align-items-center">
                    <div class="icon-3d me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #ef4444, #dc2626);">
                        <i class="fas fa-times-circle" style="font-size: 1rem;"></i>
                    </div>
                    <div>
                        <h3 class="mb-0 fw-bold text-danger">{{ $totalRejected }}</h3>
                        <p class="text-muted mb-0 small">Rejetées</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-2">
                <form method="GET" action="{{ route('admin.demandes.workflow-history') }}" class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">Code de suivi</label>
                        <input type="text" name="tracking_code" class="form-control form-control-sm" value="{{ request('tracking_code') }}" placeholder="CSAR-XXXX">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">Date début</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="form-label small fw-bold">Date fin</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i>Filtrer
                            </button>
                            <a href="{{ route('admin.demandes.workflow-history') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tableau d'historique -->
    <div class="row">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-stream me-2 text-primary"></i>Journal d'audit</h6>
                    <span class="badge bg-secondary">{{ $total }} entrée(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Date/Heure</th>
                                <th>Action</th>
                                <th>Demande</th>
                                <th>Demandeur</th>
                                <th>Commentaire</th>
                                <th>Documents</th>
                                <th>Statut actuel</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($timeline as $entry)
                            @php
                                $actionLower = strtolower($entry['action']);
                                $rowClass = '';
                                if (str_contains($actionLower, 'signature')) $rowClass = 'table-primary';
                                elseif (str_contains($actionLower, 'scan')) $rowClass = 'table-info';
                                elseif (str_contains($actionLower, 'valid') || str_contains($actionLower, 'approuv')) $rowClass = 'table-success';
                                elseif (str_contains($actionLower, 'rejet')) $rowClass = 'table-danger';
                                elseif (str_contains($actionLower, 'créée')) $rowClass = '';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($entry['timestamp'])->format('d/m/Y') }}</small>
                                    <br><strong>{{ \Carbon\Carbon::parse($entry['timestamp'])->format('H:i') }}</strong>
                                </td>
                                <td>
                                    @if(str_contains($actionLower, 'signature'))
                                    <span class="badge bg-primary"><i class="fas fa-signature me-1"></i>{{ $entry['action'] }}</span>
                                    @elseif(str_contains($actionLower, 'scan'))
                                    <span class="badge bg-info"><i class="fas fa-file-pdf me-1"></i>{{ $entry['action'] }}</span>
                                    @elseif(str_contains($actionLower, 'valid') || str_contains($actionLower, 'approuv'))
                                    <span class="badge bg-success"><i class="fas fa-stamp me-1"></i>{{ $entry['action'] }}</span>
                                    @elseif(str_contains($actionLower, 'rejet'))
                                    <span class="badge bg-danger"><i class="fas fa-times me-1"></i>{{ $entry['action'] }}</span>
                                    @elseif(str_contains($actionLower, 'transition') || str_contains($actionLower, 'en masse'))
                                    <span class="badge bg-warning text-dark"><i class="fas fa-forward me-1"></i>{{ $entry['action'] }}</span>
                                    @else
                                    <span class="badge bg-secondary">{{ $entry['action'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.demandes.show', $entry['request_id']) }}" class="text-primary fw-bold small">
                                        {{ $entry['tracking_code'] }}
                                    </a>
                                </td>
                                <td><small>{{ $entry['full_name'] }}</small></td>
                                <td><small class="text-muted">{{ $entry['comment'] }}</small></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        @if($entry['has_signature'])
                                        <span class="badge bg-primary" title="Document signé"><i class="fas fa-signature"></i></span>
                                        @endif
                                        @if($entry['has_scan'])
                                        <span class="badge bg-info" title="Document scanné"><i class="fas fa-file-pdf"></i></span>
                                        @endif
                                        @if($entry['dg_approved_at'])
                                        <span class="badge bg-success" title="Validée DG le {{ \Carbon\Carbon::parse($entry['dg_approved_at'])->format('d/m/Y') }}"><i class="fas fa-stamp"></i></span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $req = \App\Models\PublicRequest::find($entry['request_id']);
                                    @endphp
                                    @if($req)
                                    {!! $req->workflow_status_badge !!}
                                    @else
                                    <span class="badge bg-light text-dark">-</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x text-gray-300 mb-2"></i>
                                    <p class="text-muted mb-0">Aucune action enregistrée</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination simple -->
                @if($total > $perPage)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <small class="text-muted">Page {{ $page }} sur {{ ceil($total / $perPage) }}</small>
                    <div class="btn-group btn-group-sm">
                        @if($page > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}" class="btn btn-outline-primary">← Précédent</a>
                        @endif
                        @if($page < ceil($total / $perPage))
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}" class="btn btn-outline-primary">Suivant →</a>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

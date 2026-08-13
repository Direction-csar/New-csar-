@extends('layouts.admin')

@section('title', 'Exports')
@section('page-title', 'Exports de données')

@section('content')
<div class="container-fluid py-4">
    <h2 class="h4 mb-4">Télécharger les exports</h2>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h6 class="card-title">Campagnes</h6>
                    <a href="{{ route('admin.distribution.exports.campaigns') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h6 class="card-title">Plannings</h6>
                    <a href="{{ route('admin.distribution.exports.plannings') }}" class="btn btn-info mt-2">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h6 class="card-title">Bénéficiaires</h6>
                    <a href="{{ route('admin.distribution.exports.beneficiaires') }}" class="btn btn-success mt-2">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h6 class="card-title">Bons-matière</h6>
                    <a href="{{ route('admin.distribution.exports.bon-matieres') }}" class="btn btn-warning mt-2">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between">
                    <h6 class="card-title">Tickets</h6>
                    <a href="{{ route('admin.distribution.exports.tickets') }}" class="btn btn-secondary mt-2">
                        <i class="fas fa-file-csv me-2"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

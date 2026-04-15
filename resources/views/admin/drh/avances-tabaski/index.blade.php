@extends('layouts.admin')

@section('title', 'Avances Tabaski 2026')
@section('page-title', '🕌 Avances Tabaski 2026')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Stats cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body p-3">
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Inscrits</div>
                    <div class="h3 fw-bold mb-0 text-success">{{ $stats['total'] }}</div>
                    <div class="small text-muted">sur {{ $stats['agents'] }} agents ({{ $stats['taux'] }}%)</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body p-3">
                    <div class="text-xs text-muted text-uppercase fw-bold mb-1">Montant global</div>
                    <div class="h5 fw-bold mb-0 text-primary">{{ $stats['montant_global'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #8b5cf6 !important;">
                <div class="card-body p-3">
                    <div class="text-xs text-muted fw-bold mb-1">100 000</div>
                    <div class="h4 fw-bold mb-0 text-purple">{{ $stats['total_100'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body p-3">
                    <div class="text-xs text-muted fw-bold mb-1">150 000</div>
                    <div class="h4 fw-bold mb-0 text-warning">{{ $stats['total_150'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-4 col-md-2">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body p-3">
                    <div class="text-xs text-muted fw-bold mb-1">200 000</div>
                    <div class="h4 fw-bold mb-0 text-danger">{{ $stats['total_200'] }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Barre de progression --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-bold text-muted">Taux de participation</span>
                <span class="small fw-bold text-success">{{ $stats['taux'] }}%</span>
            </div>
            <div class="progress" style="height: 10px; border-radius: 10px;">
                <div class="progress-bar bg-success" style="width: {{ $stats['taux'] }}%; border-radius: 10px;"></div>
            </div>
        </div>
    </div>

    {{-- Filtres + Export --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.drh.tabaski.index') }}" class="row g-2 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold mb-1">Recherche</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom ou prénom..." class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Direction</label>
                    <select name="direction" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($directions as $d)
                            <option value="{{ $d }}" {{ request('direction') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Région</label>
                    <select name="region" class="form-select form-select-sm">
                        <option value="">Toutes</option>
                        @foreach($regions as $r)
                            <option value="{{ $r }}" {{ request('region') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-bold mb-1">Montant</label>
                    <select name="montant" class="form-select form-select-sm">
                        <option value="">Tous</option>
                        <option value="100000" {{ request('montant') == '100000' ? 'selected' : '' }}>100 000 FCFA</option>
                        <option value="150000" {{ request('montant') == '150000' ? 'selected' : '' }}>150 000 FCFA</option>
                        <option value="200000" {{ request('montant') == '200000' ? 'selected' : '' }}>200 000 FCFA</option>
                    </select>
                </div>
                <div class="col-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-success btn-sm flex-fill">
                        <i class="fas fa-search me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('admin.drh.tabaski.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Boutons export --}}
    <div class="d-flex gap-2 mb-3 flex-wrap">
        <a href="{{ route('admin.drh.tabaski.export-csv', request()->query()) }}" class="btn btn-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Exporter CSV (Excel)
        </a>
        <a href="{{ route('admin.drh.tabaski.print', request()->query()) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-print me-1"></i> Imprimer / PDF
        </a>
        <a href="{{ url('/avance-tabaski') }}" target="_blank" class="btn btn-outline-success btn-sm ms-auto">
            <i class="fas fa-external-link-alt me-1"></i> Voir le formulaire agent
        </a>
    </div>

    {{-- Tableau --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">N°</th>
                            <th>Prénom & Nom</th>
                            <th class="d-none d-md-table-cell">Poste</th>
                            <th>Direction</th>
                            <th class="d-none d-md-table-cell">Région</th>
                            <th>Montant</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inscriptions as $i => $ins)
                        <tr>
                            <td class="ps-4 text-muted small">{{ $inscriptions->firstItem() + $i }}</td>
                            <td>
                                <div class="fw-bold">{{ $ins->agent->prenom ?? '—' }} {{ $ins->agent->nom ?? '' }}</div>
                            </td>
                            <td class="small text-muted d-none d-md-table-cell" style="max-width:200px;">{{ $ins->agent->poste ?? '—' }}</td>
                            <td class="small">{{ $ins->agent->direction ?? '—' }}</td>
                            <td class="small d-none d-md-table-cell">
                                <span class="badge bg-light text-dark border">{{ $ins->agent->region ?? '—' }}</span>
                            </td>
                            <td>
                                @php $m = (int) $ins->montant; @endphp
                                <span class="badge {{ $m == 200000 ? 'bg-danger' : ($m == 150000 ? 'bg-warning text-dark' : 'bg-primary') }} px-2 py-1">
                                    {{ number_format($m, 0, ',', ' ') }}
                                </span>
                            </td>
                            <td class="small text-muted">{{ $ins->date_inscription->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <div class="mb-2" style="font-size:2rem;">📋</div>
                                Aucune inscription pour le moment.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($inscriptions->hasPages())
        <div class="card-footer border-0 bg-transparent d-flex justify-content-between align-items-center px-4 py-3">
            <div class="small text-muted">
                {{ $inscriptions->firstItem() }} – {{ $inscriptions->lastItem() }} sur {{ $inscriptions->total() }} inscrits
            </div>
            {{ $inscriptions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

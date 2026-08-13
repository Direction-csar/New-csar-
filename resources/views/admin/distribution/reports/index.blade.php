@extends('layouts.admin')

@section('title', 'Rapports')
@section('page-title', 'Rapport de campagne')

@section('content')
<div class="container-fluid py-4">
    <h2 class="h4 mb-4">Compte rendu par campagne</h2>

    <form method="GET" action="{{ route('admin.distribution.reports.index') }}" class="mb-4">
        <div class="input-group">
            <select name="campaign_id" class="form-select" required>
                <option value="">— Choisir une campagne —</option>
                @foreach($campaigns as $c)
                <option value="{{ $c->id }}" {{ (request('campaign_id') == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Générer</button>
        </div>
    </form>

    @isset($campaign)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">{{ $campaign->name }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="border p-3 rounded text-center">
                        <h4 class="text-primary">{{ number_format($report['total_planned_kg'], 0, ',', ' ') }} <small>kg</small></h4>
                        <small class="text-muted">Quota planifié</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border p-3 rounded text-center">
                        <h4 class="text-success">{{ number_format($report['total_executed_kg'], 0, ',', ' ') }} <small>kg</small></h4>
                        <small class="text-muted">Exécuté</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border p-3 rounded text-center">
                        <h4 class="text-info">{{ $report['total_beneficiaires'] }}</h4>
                        <small class="text-muted">Bénéficiaires</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border p-3 rounded text-center">
                        <h4 class="text-secondary">{{ $report['total_bons_livres'] }}</h4>
                        <small class="text-muted">Bons livrés</small>
                    </div>
                </div>
            </div>

            <h6 class="mb-3">Détail par planning</h6>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Planning</th>
                        <th>Catégorie</th>
                        <th>Planifié (kg)</th>
                        <th>Exécuté (kg)</th>
                        <th>Bénéficiaires</th>
                        <th>Livré</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['plannings'] as $p)
                    <tr>
                        <td>{{ $p['name'] }}</td>
                        <td>{{ $p['category'] }}</td>
                        <td>{{ number_format($p['planned'], 2, ',', ' ') }}</td>
                        <td>{{ number_format($p['executed'], 2, ',', ' ') }}</td>
                        <td>{{ $p['beneficiaires'] }}</td>
                        <td>{{ $p['livres'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Imprimer / PDF
            </button>
        </div>
    </div>
    @endisset
</div>
@endsection

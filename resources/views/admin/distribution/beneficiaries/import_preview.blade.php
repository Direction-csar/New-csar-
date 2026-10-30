@extends('layouts.admin')

@section('title', 'Aperçu de l\'import')
@section('page-title', 'Aperçu de l\'import')

@section('content')
@php
    $c = $analysis['counts'];
    $labels = ['name' => 'Nom', 'firstname' => 'Prénom', 'phone' => 'Téléphone', 'cni' => 'CNI', 'quantity' => 'Quantité', 'category' => 'Catégorie', 'address' => 'Adresse'];
    $shown = array_slice($analysis['rows'], 0, 500);
@endphp
<div class="container-fluid px-3">
    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3 d-flex flex-row justify-content-between align-items-center">
                <div>
                    <h1 class="h5 mb-1 fw-bold">Aperçu : {{ $sourceName }}</h1>
                    <div class="text-muted small">Planning : <strong>{{ $planning->name }}</strong> ({{ $planning->event?->name ?? 'sans événement' }}) — aucune donnée n'est encore enregistrée.</div>
                </div>
                <a href="{{ route('admin.distribution.beneficiaries.import', ['planning_id' => $planning->id]) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Modifier le fichier</a>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-3 col-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Lignes lues</div><div class="h4 fw-bold mb-0">{{ $c['total'] }}</div></div></div>
        <div class="col-md-3 col-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">À importer</div><div class="h4 fw-bold text-success mb-0">{{ $c['ok'] }}</div></div></div>
        <div class="col-md-3 col-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Doublons ignorés</div><div class="h4 fw-bold text-warning mb-0">{{ $c['duplicate'] }}</div></div></div>
        <div class="col-md-3 col-6 mb-2"><div class="card-modern p-3 text-center"><div class="text-muted small">Erreurs</div><div class="h4 fw-bold text-danger mb-0">{{ $c['error'] }}</div></div></div>
    </div>

    <div class="row mb-2">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="small mb-2"><strong>Colonnes détectées :</strong>
                    @if($analysis['header_detected'])
                        @foreach($analysis['mapping'] as $field => $idx)
                            <span class="badge bg-light text-dark border me-1">{{ $labels[$field] ?? $field }} → colonne {{ $idx + 1 }}</span>
                        @endforeach
                    @else
                        <span class="text-muted">lecture par position (nom ; téléphone ; CNI ; quantité)</span>
                    @endif
                </div>
                @foreach($analysis['warnings'] as $w)
                    <div class="alert alert-warning small py-1 mb-1"><i class="fas fa-exclamation-triangle me-1"></i>{{ $w }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card-modern p-3">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead><tr><th>Ligne</th><th>Nom</th><th>Téléphone</th><th>CNI</th><th>Qté (kg)</th><th>Catégorie</th><th>Résultat</th></tr></thead>
                        <tbody>
                        @foreach($shown as $r)
                            <tr class="{{ $r['status'] === 'error' ? 'table-danger' : ($r['status'] === 'duplicate' ? 'table-warning' : '') }}">
                                <td>{{ $r['line'] }}</td>
                                <td>{{ $r['name'] }}</td>
                                <td>{{ $r['phone'] ?? '—' }}</td>
                                <td>{{ $r['cni'] ?? '—' }}</td>
                                <td>{{ $r['quantity_kg'] }}</td>
                                <td>{{ $r['category'] }}</td>
                                <td>
                                    @if($r['status'] === 'ok') <span class="badge bg-success">OK</span>
                                    @elseif($r['status'] === 'duplicate') <span class="badge bg-warning text-dark">Doublon</span>
                                    @else <span class="badge bg-danger">Erreur</span>
                                    @endif
                                    @foreach($r['messages'] as $m)<div class="small text-muted">{{ $m }}</div>@endforeach
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @if(count($analysis['rows']) > count($shown))
                    <div class="small text-muted mt-2">Affichage des {{ count($shown) }} premières lignes sur {{ count($analysis['rows']) }}. Toutes les lignes valides seront importées.</div>
                @endif
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-end gap-2">
            @if($token)
                <form method="POST" action="{{ route('admin.distribution.beneficiaries.import.confirm') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i>Confirmer l'import de {{ $c['ok'] }} bénéficiaire(s)</button>
                </form>
            @else
                <div class="alert alert-danger small py-2 mb-0">Aucune ligne valide à importer. Corrigez le fichier puis relancez l'analyse.</div>
            @endif
        </div>
    </div>
</div>
@endsection

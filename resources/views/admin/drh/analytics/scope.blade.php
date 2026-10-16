@extends('layouts.drh-portal')

@php
    $kindLabel = $kind === 'departement' ? 'Département' : 'Poste / fonction';
@endphp

@section('title', $kindLabel . ' : ' . $nom)
@section('page-title', $kindLabel . ' : ' . $nom)

@section('styles')
    @include('admin.drh.analytics._styles')
@endsection

@section('content')
@php
    $base   = $kind === 'departement' ? route('admin.drh.analytics.departement', $nom) : route('admin.drh.analytics.service', $nom);
    $scopeQ = [$kind => $nom];
    $link   = fn (string $metric, array $extra = []) => route('admin.drh.analytics.show', $metric) . '?' . http_build_query($scopeQ + $extra);
    $gTotal = max(1, array_sum($genres));
    $den    = max(1, $total);
@endphp

<div class="an-header">
    <div>
        <h2><i class="fas {{ $kind === 'departement' ? 'fa-building' : 'fa-briefcase' }}"></i> {{ $nom }}</h2>
        <div class="an-sub">{{ $kindLabel }} — {{ $total }} agent(s) rattaché(s)</div>
    </div>
    <a href="{{ route('admin.drh.dashboard') }}" class="an-back"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
</div>

@if($kind === 'service')
    <div class="an-notice info mb-3">
        Les « services » affichés sur le tableau de bord correspondent au champ <strong>poste actuel</strong> des fiches agents : il s'agit de fonctions et non de services organisationnels. Le rattachement à un département est indiqué ci-dessous.
    </div>
@endif

{{-- KPI --}}
<div class="an-card mb-3">
    <div class="an-kpis">
        <a href="{{ $link('genre') }}" class="an-kpi"><div class="v">{{ $total }}</div><div class="l">Agents</div></a>
        <a href="{{ $link('genre', ['sexe' => 'Féminin']) }}" class="an-kpi"><div class="v" style="color:#ec407a">{{ $genres['Féminin'] }}</div><div class="l">Femmes ({{ round($genres['Féminin'] / $gTotal * 100) }} %)</div></a>
        <a href="{{ $link('genre', ['sexe' => 'Masculin']) }}" class="an-kpi"><div class="v" style="color:#0d9488">{{ $genres['Masculin'] }}</div><div class="l">Hommes ({{ round($genres['Masculin'] / $gTotal * 100) }} %)</div></a>
        @if($genres['nr'] > 0)
            <a href="{{ $link('genre', ['sexe' => 'nr']) }}" class="an-kpi"><div class="v" style="color:#94a3b8">{{ $genres['nr'] }}</div><div class="l">Sexe non renseigné</div></a>
        @endif
        <a href="{{ $link('fins-contrats') }}" class="an-kpi"><div class="v" style="color:#f59e0b">{{ $nbFinsContrats }}</div><div class="l">Fins de contrat (90 j)</div></a>
        <a href="{{ $link('retraites') }}" class="an-kpi"><div class="v" style="color:#ef4444">{{ $nbRetraites }}</div><div class="l">Retraites prévisionnelles (24 mois)</div></a>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Types de contrat --}}
    <div class="col-lg-4">
        <div class="an-card h-100">
            <div class="an-card-title"><i class="fas fa-file-contract"></i> Répartition par type de contrat</div>
            @foreach($contrats as $t => $n)
                <a href="{{ $t === 'Non renseigné' ? $link('genre') : $link('genre', ['type_contrat' => $t]) }}" class="an-bar">
                    <span>{{ $t }}</span>
                    <span class="track"><span class="seg-m" style="width: {{ $total ? round($n / $den * 100) : 0 }}%"></span></span>
                    <span class="n">{{ $n }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Ancienneté --}}
    <div class="col-lg-4">
        <div class="an-card h-100">
            <div class="an-card-title"><i class="fas fa-hourglass-half"></i> Ancienneté par genre</div>
            <div class="an-bars">
                @foreach($anciennete as $k => $b)
                    <a href="{{ $link('anciennete', ['tranche' => $k]) }}" class="an-bar" title="Voir les agents">
                        <span>{{ $b['label'] }}</span>
                        <span class="track">
                            @if($b['total'])
                                <span class="seg-f" style="width:{{ $b['Féminin'] / $den * 100 }}%"></span>
                                <span class="seg-m" style="width:{{ $b['Masculin'] / $den * 100 }}%"></span>
                                <span class="seg-nr" style="width:{{ $b['nr'] / $den * 100 }}%"></span>
                            @endif
                        </span>
                        <span class="n">{{ $b['total'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="an-legend"><span class="lf">Femmes</span><span class="lm">Hommes</span><span class="lnr">Non renseigné</span></div>
        </div>
    </div>

    {{-- Âges --}}
    <div class="col-lg-4">
        <div class="an-card h-100">
            <div class="an-card-title"><i class="fas fa-birthday-cake"></i> Tranches d'âge par genre</div>
            <div class="an-bars">
                @foreach($ages as $k => $b)
                    <a href="{{ $link('age', ['tranche' => $k]) }}" class="an-bar" title="Voir les agents">
                        <span>{{ $b['label'] }}</span>
                        <span class="track">
                            @if($b['total'])
                                <span class="seg-f" style="width:{{ $b['Féminin'] / $den * 100 }}%"></span>
                                <span class="seg-m" style="width:{{ $b['Masculin'] / $den * 100 }}%"></span>
                                <span class="seg-nr" style="width:{{ $b['nr'] / $den * 100 }}%"></span>
                            @endif
                        </span>
                        <span class="n">{{ $b['total'] }}</span>
                    </a>
                @endforeach
            </div>
            <div class="an-legend"><span class="lf">Femmes</span><span class="lm">Hommes</span><span class="lnr">Non renseigné</span></div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Postes / départements rattachés --}}
    <div class="col-lg-6">
        <div class="an-card h-100">
            @if($kind === 'departement')
                <div class="an-card-title"><i class="fas fa-sitemap"></i> Postes / fonctions occupés ({{ $postes->count() }})</div>
                @forelse($postes as $p)
                    <a href="{{ route('admin.drh.analytics.service', $p->poste) }}" class="an-chip">{{ $p->poste }} <b>{{ $p->c }}</b></a>
                @empty
                    <div class="an-empty">Aucun poste renseigné</div>
                @endforelse
                <div class="small text-muted mt-2">Les services organisationnels ne sont pas modélisés dans la base : seul le poste de chaque agent est enregistré.</div>
            @else
                <div class="an-card-title"><i class="fas fa-building"></i> Département(s) de rattachement</div>
                @forelse($departementsDuPoste as $d)
                    <a href="{{ route('admin.drh.analytics.departement', $d->nom) }}" class="an-chip">{{ $d->nom }} <b>{{ $d->c }}</b></a>
                @empty
                    <div class="an-empty">Aucun département renseigné</div>
                @endforelse
            @endif
        </div>
    </div>

    {{-- Échéances --}}
    <div class="col-lg-6">
        <div class="an-card h-100">
            <div class="an-card-title"><i class="fas fa-calendar-check"></i> Échéances à venir</div>
            <div class="small fw-bold text-muted mb-1">Fins de contrat (90 jours) — {{ $nbFinsContrats }}</div>
            @forelse($finsContrats as $r)
                <div class="d-flex justify-content-between small py-1 border-bottom">
                    <a href="{{ route('admin.drh.personnel.show', $r['_id']) }}" class="text-decoration-none">{{ $r['nom'] }}</a>
                    <span>{{ $r['type_contrat'] }} · {{ $r['date_fin_contrat'] }} <span class="an-pill {{ $r['jours_restants'] <= 30 ? 'an-pill-danger' : 'an-pill-warn' }}">{{ $r['jours_restants'] }} j</span></span>
                </div>
            @empty
                <div class="small text-muted fst-italic py-1">Aucune fin de contrat renseignée dans la période.</div>
            @endforelse
            @if($nbFinsContrats > $finsContrats->count())
                <a href="{{ $link('fins-contrats') }}" class="small d-block mt-1">Voir les {{ $nbFinsContrats }} contrats →</a>
            @endif

            <div class="small fw-bold text-muted mb-1 mt-3">Départs à la retraite prévisionnels (24 mois) — {{ $nbRetraites }}</div>
            @forelse($retraites as $r)
                <div class="d-flex justify-content-between small py-1 border-bottom">
                    <a href="{{ route('admin.drh.personnel.show', $r['_id']) }}" class="text-decoration-none">{{ $r['nom'] }}</a>
                    <span>{{ $r['age'] }} · 60 ans le {{ $r['date_retraite'] }}</span>
                </div>
            @empty
                <div class="small text-muted fst-italic py-1">Aucun départ prévisionnel dans la période.</div>
            @endforelse
            @if($nbRetraites > $retraites->count())
                <a href="{{ $link('retraites') }}" class="small d-block mt-1">Voir les {{ $nbRetraites }} agents →</a>
            @endif
        </div>
    </div>
</div>

{{-- Liste des agents --}}
<div class="an-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="an-count"><strong>{{ $paginator->total() }}</strong> agent(s)</div>
        <form method="get" action="{{ $base }}" class="an-filters" style="flex:1 1 300px; justify-content:flex-end">
            <div class="form-group" style="flex:0 1 260px">
                <label for="f-q">Recherche</label>
                <input type="search" class="form-control" id="f-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom ou matricule…">
            </div>
            <div class="form-group" style="flex:0 1 140px">
                <label for="f-sexe">Sexe</label>
                <select class="form-select" id="f-sexe" name="sexe" onchange="this.form.submit()">
                    <option value="">Tous</option>
                    @foreach($genderLabels as $k => $v)
                        <option value="{{ $k }}" {{ ($filters['sexe'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="an-actions">
                <button class="an-btn an-btn-primary" type="submit"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.drh.analytics.export', 'genre') . '?' . http_build_query($scopeQ + collect($filters)->except('page')->all()) }}" class="an-btn an-btn-light"><i class="fas fa-file-csv"></i> CSV</a>
                <a href="{{ route('admin.drh.analytics.print', 'genre') . '?' . http_build_query($scopeQ + collect($filters)->except('page')->all()) }}" target="_blank" class="an-btn an-btn-light"><i class="fas fa-print"></i></a>
            </div>
        </form>
    </div>

    @include('admin.drh.analytics._table', ['sortBase' => $base])
</div>
@endsection

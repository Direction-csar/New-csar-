@extends('layouts.drh-portal')

@section('title', $def['title'] . ' - Analyse RH')
@section('page-title', $def['title'])

@section('styles')
    @include('admin.drh.analytics._styles')
@endsection

@section('content')
@php
    $base       = route('admin.drh.analytics.show', $metric);
    $qsAll      = collect($filters)->except(['page'])->all();
    $exportUrl  = route('admin.drh.analytics.export', $metric) . ($qsAll ? '?' . http_build_query($qsAll) : '');
    $printUrl   = route('admin.drh.analytics.print', $metric) . ($qsAll ? '?' . http_build_query($qsAll) : '');
    $backUrl    = route('admin.drh.dashboard');
    $hasData    = $def['columns'] !== [];
@endphp

<div class="an-header">
    <div>
        <h2><i class="fas {{ $def['icon'] }}"></i> {{ $def['title'] }}</h2>
        <div class="an-sub">{{ $def['subtitle'] }}</div>
    </div>
    <a href="{{ $backUrl }}" class="an-back"><i class="fas fa-arrow-left"></i> Retour au tableau de bord</a>
</div>

@if($metric === 'accidents-travail')
    <div class="an-card">
        <div class="an-empty">
            <i class="fas fa-kit-medical"></i>
            <div class="fw-semibold text-dark mb-1">Aucune donnée disponible</div>
            <div>La plateforme ne dispose pas de module d'enregistrement des accidents de travail.</div>
        </div>
        <div class="an-notice mt-3">
            <strong>Champs nécessaires pour activer cet indicateur :</strong>
            <ul>
                <li>Agent concerné (lien vers la fiche personnel)</li>
                <li>Date et lieu de l'accident, description</li>
                <li>Nombre de jours d'arrêt (début / fin)</li>
                <li>Statut du dossier (déclaré, reconnu, clôturé)</li>
            </ul>
        </div>
    </div>
@else

{{-- Filtres --}}
<div class="an-card mb-3">
    <form method="get" action="{{ $base }}" class="an-filters" id="an-filter-form">
        @if(isset($filters['sort']))<input type="hidden" name="sort" value="{{ $filters['sort'] }}">@endif
        @if(isset($filters['dir']))<input type="hidden" name="dir" value="{{ $filters['dir'] }}">@endif

        <div class="form-group" style="flex:2 1 220px">
            <label for="f-q">Recherche</label>
            <input type="search" class="form-control" id="f-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Nom ou matricule…">
        </div>

        <div class="form-group">
            <label for="f-dep">Département</label>
            <select class="form-select" id="f-dep" name="departement">
                <option value="">Tous</option>
                @foreach($departements as $d)
                    <option value="{{ $d->nom }}" {{ ($filters['departement'] ?? '') === $d->nom ? 'selected' : '' }}>{{ $d->nom }} ({{ $d->c }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="f-svc">Poste / fonction</label>
            <select class="form-select" id="f-svc" name="service">
                <option value="">Tous</option>
                @foreach($services as $s)
                    <option value="{{ $s->nom }}" {{ ($filters['service'] ?? '') === $s->nom ? 'selected' : '' }}>{{ $s->nom }} ({{ $s->c }})</option>
                @endforeach
            </select>
        </div>

        @if(!in_array($metric, ['conges', 'recuperation']))
        <div class="form-group" style="flex:0 1 140px">
            <label for="f-sexe">Sexe</label>
            <select class="form-select" id="f-sexe" name="sexe">
                <option value="">Tous</option>
                @foreach($genders as $k => $v)
                    <option value="{{ $k }}" {{ ($filters['sexe'] ?? '') === $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
                <option value="nr" {{ ($filters['sexe'] ?? '') === 'nr' ? 'selected' : '' }}>Non renseigné</option>
            </select>
        </div>
        @endif

        @if($metric === 'retraites')
            <div class="form-group" style="flex:0 1 160px">
                <label for="f-horizon">Horizon</label>
                <select class="form-select" id="f-horizon" name="horizon">
                    @foreach([6 => '6 mois', 12 => '12 mois', 24 => '24 mois', 36 => '36 mois', 60 => '5 ans'] as $k => $v)
                        <option value="{{ $k }}" {{ (int) ($filters['horizon'] ?? 24) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="flex:0 1 160px">
                <label for="f-periode">Période</label>
                <select class="form-select" id="f-periode" name="periode">
                    <option value="">Toutes</option>
                    <option value="a_venir" {{ ($filters['periode'] ?? '') === 'a_venir' ? 'selected' : '' }}>À venir</option>
                    <option value="atteint" {{ ($filters['periode'] ?? '') === 'atteint' ? 'selected' : '' }}>Âge déjà atteint</option>
                </select>
            </div>
        @endif

        @if($metric === 'anciennete')
            <div class="form-group">
                <label for="f-tranche">Tranche d'ancienneté</label>
                <select class="form-select" id="f-tranche" name="tranche">
                    <option value="">Toutes</option>
                    @foreach($seniority as $k => $b)
                        <option value="{{ $k }}" {{ ($filters['tranche'] ?? '') === $k ? 'selected' : '' }}>{{ $b['label'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($metric === 'age')
            <div class="form-group">
                <label for="f-tranche">Tranche d'âge</label>
                <select class="form-select" id="f-tranche" name="tranche">
                    <option value="">Toutes</option>
                    @foreach($ageBands as $k => $b)
                        <option value="{{ $k }}" {{ ($filters['tranche'] ?? '') === $k ? 'selected' : '' }}>{{ $b['label'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if(in_array($metric, ['fins-contrats', 'genre', 'age', 'anciennete']))
            <div class="form-group" style="flex:0 1 150px">
                <label for="f-tc">Type de contrat</label>
                <select class="form-select" id="f-tc" name="type_contrat">
                    <option value="">Tous</option>
                    @foreach(['CDI', 'CDD', 'Stage'] as $t)
                        <option value="{{ $t }}" {{ ($filters['type_contrat'] ?? '') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if($metric === 'fins-contrats')
            <div class="form-group" style="flex:0 1 150px">
                <label for="f-horizon">Échéance dans</label>
                <select class="form-select" id="f-horizon" name="horizon">
                    @foreach([30 => '30 jours', 60 => '60 jours', 90 => '90 jours', 180 => '6 mois', 365 => '12 mois'] as $k => $v)
                        <option value="{{ $k }}" {{ (int) ($filters['horizon'] ?? 90) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        @if(in_array($metric, ['conges', 'recuperation']))
            <div class="form-group" style="flex:0 1 130px">
                <label for="f-annee">Année de référence</label>
                <select class="form-select" id="f-annee" name="annee">
                    @for($y = (int) date('Y'); $y >= (int) date('Y') - 5; $y--)
                        <option value="{{ $y }}" {{ (int) ($filters['annee'] ?? date('Y')) === $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="form-group" style="flex:0 1 150px">
                <label for="f-solde">Jours enregistrés</label>
                <select class="form-select" id="f-solde" name="solde">
                    <option value="">Tous</option>
                    <option value="positif" {{ ($filters['solde'] ?? '') === 'positif' ? 'selected' : '' }}>Supérieurs à 0</option>
                    <option value="nul" {{ ($filters['solde'] ?? '') === 'nul' ? 'selected' : '' }}>Égaux à 0</option>
                </select>
            </div>
        @endif

        <div class="an-actions">
            <button type="submit" class="an-btn an-btn-primary"><i class="fas fa-filter"></i> Filtrer</button>
            <a href="{{ $base }}" class="an-btn an-btn-light"><i class="fas fa-eraser"></i> Réinitialiser</a>
        </div>
    </form>
</div>

{{-- Avertissements métier --}}
@if($metric === 'retraites')
    <div class="an-notice info mb-3">
        La <strong>date théorique</strong> correspond au 60<sup>e</sup> anniversaire calculé à partir de la date de naissance enregistrée. Elle ne constitue pas la date administrative effective de départ à la retraite, qui dépend du statut et des règles applicables à chaque agent.
    </div>
@elseif($metric === 'conges')
    <div class="an-notice mb-3">
        <strong>Données partielles.</strong> Les jours affichés proviennent des <em>décisions de congé</em> enregistrées dans Documents RH (champ « durée »). Les droits acquis, les jours en attente de validation et le solde ne sont pas calculables : aucun module de gestion des congés n'est disponible.
        <ul>
            <li>Champs nécessaires : droits annuels par agent, demandes avec statut (en attente / validée / refusée), report de solde.</li>
        </ul>
    </div>
@elseif($metric === 'recuperation')
    <div class="an-notice mb-3">
        <strong>Données partielles.</strong> Les jours affichés proviennent des <em>demandes de récupération</em> enregistrées dans Documents RH (champ « durée »). Les jours acquis et le solde disponible ne sont pas calculables en l'absence d'un module de suivi des heures supplémentaires / récupération.
    </div>
@elseif($metric === 'fins-contrats' && !empty($extra['non_reportes']) && $extra['non_reportes']->count())
    <div class="an-notice mb-3">
        <strong>{{ $extra['non_reportes']->count() }} contrat(s)</strong> généré(s) via Documents RH ont une date de fin dans la période mais ne sont pas reportés sur la fiche agent (champ « Fin du contrat »). Ils ne figurent pas dans la liste ci-dessous :
        <ul>
            @foreach($extra['non_reportes'] as $doc)
                <li><a href="{{ route('admin.drh.personnel.edit', $doc->personnel_id) }}">{{ $doc->personnel->prenoms_nom }}</a> — {{ $doc->label }} — fin le {{ \Carbon\Carbon::parse($doc->data['date_fin'])->format('d/m/Y') }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Résultats --}}
<div class="an-card">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="an-count"><strong>{{ $paginator->total() }}</strong> résultat(s)
            @if(collect($filters)->except(['sort','dir'])->isNotEmpty())<span class="text-muted">— filtres actifs</span>@endif
        </div>
        <div class="an-actions">
            <a href="{{ $exportUrl }}" class="an-btn an-btn-light"><i class="fas fa-file-csv"></i> Export Excel (CSV)</a>
            <a href="{{ $printUrl }}" target="_blank" class="an-btn an-btn-light"><i class="fas fa-file-pdf"></i> PDF / Imprimer</a>
        </div>
    </div>

    @include('admin.drh.analytics._table', [
        'columns'  => $def['columns'],
        'sortBase' => $base,
    ])
</div>
@endif
@endsection

@section('scripts')
<script>
    (function () {
        const form = document.getElementById('an-filter-form');
        if (!form) return;
        form.querySelectorAll('select').forEach(function (el) {
            el.addEventListener('change', function () {
                form.classList.add('is-loading');
                form.submit();
            });
        });
    })();
</script>
@endsection

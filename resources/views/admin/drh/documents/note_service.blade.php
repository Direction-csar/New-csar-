@extends('admin.drh.documents._layout')

@section('doc-title', 'Note de service')
@section('reference', $reference ?? '_________')
@section('titre', 'Note de service')

@section('corps')
    <p style="margin-bottom: 8px;"><strong>Objet :</strong> {{ $objet ?? '_____________________________' }}</p>
    <p style="margin-bottom: 12px;"><strong>Référence :</strong> N° {{ $reference ?? '____' }}/MFS/CSAR/SG/DRH/ du {{ $date_reference ?? now()->translatedFormat('d F Y') }}</p>

    <p style="text-align: justify; margin-bottom: 8px;">
        <strong>{{ $civilite ?? 'Madame' }} {{ $nom_beneficiaire ?? '____________________' }}</strong>,
        {{ $poste_beneficiaire ?? '____________________' }},
        est autorisée à bénéficier d'une tranche de congé administratif de
        <strong>{{ $duree_conge ?? '__' }} jours ouvrables</strong>.
        Cette tranche de congé est déductible de son reliquat à congé de
        <strong>{{ $reliquat ?? '__' }} jours ouvrables</strong> acquis pendant la période allant du
        <strong>{{ $debut_periode ?? '__/__/____' }}</strong> au <strong>{{ $fin_periode ?? '__/__/____' }}</strong>.
    </p>

    <p style="text-align: justify; margin-top: 12px;">
        <strong>Période considérée :</strong> du <strong>{{ $date_debut_conge ?? '__/__/____' }}</strong> au <strong>{{ $date_fin_conge ?? '__/__/____' }}</strong>.
    </p>
@endsection

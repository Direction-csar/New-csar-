@extends('admin.drh.documents._layout')

@section('doc-title', 'Attestation de travail et de salaire')
@section('reference', $reference ?? '_________')
@section('titre', 'Attestation de Travail et de Salaire')

@section('corps')
    <p>Je soussignée, <strong>Madame Marième Soda NDIAYE</strong>, Directeur général du
        Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR), atteste que
        <strong>Monsieur / Madame {{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        né(e) le <strong>{{ optional($personnel?->date_naissance)->format('d/m/Y') ?? '__________' }}</strong>
        à <strong>{{ $personnel?->lieu_naissance ?? '__________' }}</strong>,
        travaille au sein du CSAR depuis le
        <strong>{{ optional($personnel?->date_prise_service_csar ?? $personnel?->date_recrutement_csar)->format('d/m/Y') ?? '__________' }}</strong>
        en qualité de <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>.</p>

    <p>Il/Elle perçoit un salaire net mensuel de
        <strong>{{ isset($salaire_net) ? number_format($salaire_net, 0, ',', ' ') . ' FCFA' : '____ FCFA' }}</strong>
        pour la période de <strong>{{ now()->format('m/Y') }}</strong>.</p>

    <p>Cette attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>
@endsection

@extends('admin.drh.documents._layout')

@section('doc-title', 'Certificat de travail')
@section('reference', $reference ?? '_________')
@section('titre', 'Certificat de Travail')

@section('corps')
    <p>Je soussignée, <strong>Madame Marième Soda NDIAYE</strong>, Directeur général du
        Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR), atteste que
        <strong>Monsieur / Madame {{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        né(e) le <strong>{{ optional($personnel?->date_naissance)->format('d/m/Y') ?? '__________' }}</strong>
        à <strong>{{ $personnel?->lieu_naissance ?? '__________' }}</strong>,
        a travaillé au CSAR en qualité de <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>
        pendant la période du <strong>{{ optional($personnel?->date_prise_service_csar ?? $personnel?->date_recrutement_csar)->format('d/m/Y') ?? '__________' }}</strong>
        au <strong>{{ optional($date_fin)->format('d/m/Y') ?? '__________' }}</strong>.</p>

    <p>Il a quitté le CSAR et est libre de tout engagement.</p>

    <p>En foi de quoi, le présent certificat de travail lui est délivré pour servir et valoir ce que de droit.</p>
@endsection

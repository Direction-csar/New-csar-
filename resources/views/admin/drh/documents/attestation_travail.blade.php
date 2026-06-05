@extends('admin.drh.documents._layout')

@section('doc-title', 'Attestation de Travail')
@section('reference', $reference ?? '_________')
@section('titre', 'Attestation de Travail')

@section('corps')
    <p>Je soussigné, le Directeur Général du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR),
        atteste par la présente que :</p>

    <p><strong>{{ $personnel->prenoms_nom ?? '____________________' }}</strong>,
        né(e) le <strong>{{ optional($personnel->date_naissance)->format('d/m/Y') ?? '__________' }}</strong>
        à <strong>{{ $personnel->lieu_naissance ?? '__________' }}</strong>,
        titulaire du matricule <strong>{{ $personnel->matricule ?? '________' }}</strong>,
        est employé(e) au sein de notre institution.</p>

    <p>L'intéressé(e) exerce les fonctions de
        <strong>{{ $personnel->poste_actuel ?? '____________________' }}</strong>
        au sein de la <strong>{{ $personnel->direction_service ?? '____________________' }}</strong>,
        depuis le <strong>{{ optional($personnel->date_prise_service_csar ?? $personnel->date_recrutement_csar)->format('d/m/Y') ?? '__________' }}</strong>,
        en qualité de <strong>{{ $personnel->statut ?? 'agent' }}</strong>.</p>

    <p>La présente attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>
@endsection

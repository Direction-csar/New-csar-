@extends('admin.drh.documents._layout')

@section('doc-title', 'Décision de congé')
@section('reference', $reference ?? '_________')
@section('titre', 'Décision de Congé')

@section('corps')
    <p>Le Directeur Général du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR),</p>

    <p>Vu le Code du Travail ; vu le règlement intérieur ; vu la demande de l'intéressé(e),</p>

    <p><strong>DÉCIDE :</strong></p>

    <p><strong>Article 1 :</strong> Un congé
        <strong>{{ $type_conge ?? 'annuel' }}</strong> de
        <strong>{{ $nombre_jours ?? '____' }}</strong> jour(s) est accordé à
        Monsieur/Madame <strong>{{ $personnel->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel->poste_actuel ?? '__________' }}</strong>
        à la <strong>{{ $personnel->direction_service ?? '__________' }}</strong>.</p>

    <p><strong>Article 2 :</strong> Ce congé court du
        <strong>{{ isset($date_debut) ? \Carbon\Carbon::parse($date_debut)->format('d/m/Y') : '__________' }}</strong>
        au <strong>{{ isset($date_fin) ? \Carbon\Carbon::parse($date_fin)->format('d/m/Y') : '__________' }}</strong> inclus.</p>

    <p><strong>Article 3 :</strong> L'intéressé(e) reprendra son service le
        <strong>{{ isset($date_reprise) ? \Carbon\Carbon::parse($date_reprise)->format('d/m/Y') : '__________' }}</strong>.</p>

    <p><strong>Article 4 :</strong> La présente décision sera notifiée à l'intéressé(e) et versée à son dossier.</p>
@endsection

@extends('admin.drh.documents._layout')

@section('doc-title', 'Autorisation d\'absence')
@section('reference', $reference ?? '_________')
@section('titre', 'Autorisation d\'Absence')

@section('corps')
    <p>Objet : <strong>Autorisation d'absence</strong></p>

    <p>Le Directeur Général du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR),</p>

    <p>Vu la demande de Monsieur/Madame
        <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,</p>

    <p><strong>Accorde à l'intéressé(e) une autorisation d'absence</strong>
        du <strong>{{ isset($date_debut) ? \Carbon\Carbon::parse($date_debut)->format('d/m/Y') : '__________' }}</strong>
        au <strong>{{ isset($date_fin) ? \Carbon\Carbon::parse($date_fin)->format('d/m/Y') : '__________' }}</strong>,
        soit <strong>{{ $nombre_jours ?? '____' }}</strong> jour(s), pour le motif suivant :</p>

    <p><strong>Motif :</strong> {{ $motif ?? '____________________________________________' }}</p>

    <p><strong>Nature :</strong> {{ $nature ?? '_______________' }} (avec/sans solde)<br>
        <strong>Reprise de service :</strong>
        <strong>{{ isset($date_reprise) ? \Carbon\Carbon::parse($date_reprise)->format('d/m/Y') : '__________' }}</strong></p>

    <p>L'intéressé(e) est invité(e) à transmettre toute pièce justificative relative à cette absence
        dans les meilleurs délais.</p>

    <p>La présente autorisation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>
@endsection

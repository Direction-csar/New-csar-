@extends('admin.drh.documents._layout')

@section('doc-title', 'Avance sur salaire')
@section('reference', $reference ?? '_________')
@section('titre', 'Décision d\'Avance sur Salaire')

@section('corps')
    <p>Le Directeur Général du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR),</p>

    <p>Vu la demande introduite par Monsieur/Madame
        <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>
        à la <strong>{{ $personnel?->direction_service ?? '__________' }}</strong>,</p>

    <p><strong>DÉCIDE :</strong></p>

    <p><strong>Article 1 :</strong> Il est accordé à l'intéressé(e) une avance sur salaire d'un montant de
        <strong>{{ isset($montant) ? number_format($montant, 0, ',', ' ') . ' FCFA' : '____________ FCFA' }}</strong>
        ({{ $montant_lettres ?? '________________________' }}).</p>

    <p><strong>Article 2 :</strong> Cette avance sera remboursée par retenue sur salaire en
        <strong>{{ $nombre_echeances ?? '____' }}</strong> mensualité(s), à compter du
        <strong>{{ isset($date_debut_remboursement) ? \Carbon\Carbon::parse($date_debut_remboursement)->format('d/m/Y') : '__________' }}</strong>.</p>

    <p><strong>Article 3 :</strong> Le service de la solde et de la comptabilité est chargé de l'exécution
        de la présente décision.</p>
@endsection

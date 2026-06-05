@extends('admin.drh.documents._layout')

@section('doc-title', 'Contrat de prêt')
@section('reference', $reference ?? '_________')
@section('titre', 'Contrat de Prêt')

@section('corps')
    <p>Entre le <strong>Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR)</strong>,
        représenté par son Directeur Général, ci-après dénommé <strong>« le Prêteur »</strong>, d'une part,</p>

    <p>Et Monsieur/Madame <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>,
        ci-après dénommé(e) <strong>« l'Emprunteur »</strong>, d'autre part,</p>

    <p>Il a été convenu ce qui suit :</p>

    <p><strong>Article 1 — Montant.</strong> Le Prêteur consent à l'Emprunteur un prêt d'un montant de
        <strong>{{ isset($montant) ? number_format($montant, 0, ',', ' ') . ' FCFA' : '____________ FCFA' }}</strong>
        ({{ $montant_lettres ?? '________________________' }}).</p>

    <p><strong>Article 2 — Objet.</strong> Ce prêt est destiné à {{ $objet ?? '____________________________' }}.</p>

    <p><strong>Article 3 — Remboursement.</strong> L'Emprunteur s'engage à rembourser ce prêt en
        <strong>{{ $nombre_echeances ?? '____' }}</strong> mensualités de
        <strong>{{ isset($mensualite) ? number_format($mensualite, 0, ',', ' ') . ' FCFA' : '________ FCFA' }}</strong>,
        par retenue à la source sur son salaire, à compter du
        <strong>{{ isset($date_debut_remboursement) ? \Carbon\Carbon::parse($date_debut_remboursement)->format('d/m/Y') : '__________' }}</strong>.</p>

    <p><strong>Article 4 — Départ de l'institution.</strong> En cas de cessation de la relation de travail,
        le solde restant dû devient immédiatement exigible et sera prélevé sur les sommes dues à l'Emprunteur.</p>

    <p>Fait à Dakar, en deux (02) exemplaires, le {{ now()->format('d/m/Y') }}.</p>
@endsection

@section('signataire-role', 'L\'Emprunteur                                          Le Directeur Général')

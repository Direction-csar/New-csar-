@extends('admin.drh.documents._layout')

@section('doc-title', 'Domiciliation de salaire')
@section('reference', $reference ?? '_________')
@section('titre', 'Attestation de Domiciliation de Salaire')

@section('corps')
    <p>Le Directeur Général du Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR),</p>

    <p>Atteste que le salaire de Monsieur/Madame
        <strong>{{ $personnel->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel->matricule ?? '________' }}</strong>,
        est domicilié à la banque suivante :</p>

    <p><strong>Banque / Établissement :</strong> {{ $banque ?? '________________________________________' }}<br>
        <strong>Agence :</strong> {{ $agence ?? '________________________________________' }}<br>
        <strong>N° de compte :</strong> {{ $numero_compte ?? '________________________________________' }}<br>
        <strong>Clé :</strong> {{ $cle ?? '________' }}</p>

    <p>La présente attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>
@endsection

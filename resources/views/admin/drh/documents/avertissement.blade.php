@extends('admin.drh.documents._layout')

@section('doc-title', 'Lettre d\'avertissement')
@section('reference', $reference ?? '_________')
@section('titre', 'Lettre d\'Avertissement')

@section('corps')
    <p>Objet : <strong>Avertissement disciplinaire</strong></p>

    <p>Monsieur/Madame <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>
        à la <strong>{{ $personnel?->direction_service ?? '__________' }}</strong>,</p>

    <p>Nous sommes au regret de vous adresser le présent avertissement à la suite des faits suivants,
        survenus le <strong>{{ isset($date_faits) ? \Carbon\Carbon::parse($date_faits)->format('d/m/Y') : '__________' }}</strong> :</p>

    <p style="margin-left: 18px;">{{ $motif ?? '________________________________________________________________________' }}</p>

    <p>Ce comportement constitue un manquement à vos obligations professionnelles et au règlement intérieur
        de l'institution. Nous vous demandons de veiller à ce que de tels faits ne se reproduisent plus.</p>

    <p>Nous attirons votre attention sur le fait qu'en cas de récidive, des sanctions plus sévères pourront
        être prises à votre encontre.</p>

    <p>Cet avertissement sera versé à votre dossier individuel.</p>

    <p>Nous vous prions d'agréer l'expression de nos salutations distinguées.</p>
@endsection

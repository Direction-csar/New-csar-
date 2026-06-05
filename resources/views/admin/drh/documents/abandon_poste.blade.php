@extends('admin.drh.documents._layout')

@section('doc-title', 'Notification d\'abandon de poste')
@section('reference', $reference ?? '_________')
@section('titre', 'Notification d\'Abandon de Poste')

@section('corps')
    <p>Objet : <strong>Mise en demeure pour abandon de poste</strong></p>

    <p>Monsieur/Madame <strong>{{ $personnel->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel->matricule ?? '________' }}</strong>,
        exerçant les fonctions de <strong>{{ $personnel->poste_actuel ?? '__________' }}</strong>
        au sein de la <strong>{{ $personnel->direction_service ?? '__________' }}</strong>,</p>

    <p>Nous constatons que vous êtes absent(e) de votre poste de travail sans justification ni autorisation
        préalable depuis le <strong>{{ isset($date_debut_absence) ? \Carbon\Carbon::parse($date_debut_absence)->format('d/m/Y') : '__________' }}</strong>.</p>

    <p>Par la présente, nous vous mettons en demeure de reprendre votre service ou de justifier votre absence
        dans un délai de <strong>{{ $delai ?? 'quarante-huit (48) heures' }}</strong> à compter de la réception
        de cette notification.</p>

    <p>À défaut, nous nous verrons contraints d'engager à votre encontre la procédure de licenciement pour
        abandon de poste, conformément aux dispositions du Code du Travail et du règlement intérieur en vigueur.</p>

    <p>Nous vous prions d'agréer l'expression de nos salutations distinguées.</p>
@endsection

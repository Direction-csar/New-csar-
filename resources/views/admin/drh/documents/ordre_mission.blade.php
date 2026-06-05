@extends('admin.drh.documents._layout')

@section('doc-title', 'Ordre de mission')
@section('reference', $reference ?? '_________')
@section('titre', 'Ordre de Mission')

@section('corps')
    <p>Objet : <strong>Déplacement en mission</strong></p>

    <p>Par la présente, le Directeur Général du CSAR ordonne à
        Monsieur/Madame <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>
        à la <strong>{{ $personnel?->direction_service ?? '__________' }}</strong>,
        de se rendre en mission à <strong>{{ $destination ?? '__________' }}</strong>,
        du <strong>{{ isset($date_depart) ? \Carbon\Carbon::parse($date_depart)->format('d/m/Y') : '__________' }}</strong>
        au <strong>{{ isset($date_retour) ? \Carbon\Carbon::parse($date_retour)->format('d/m/Y') : '__________' }}</strong>.</p>

    <p><strong>Objet de la mission :</strong> {{ $objet_mission ?? '____________________________________________' }}</p>

    <p><strong>Moyen de transport :</strong> {{ $moyen_transport ?? '________________________' }}<br>
        <strong>Frais de mission :</strong> {{ $frais_mission ?? '________________________' }}</p>

    <p>L'intéressé(e) est tenu(e) de dresser un rapport à l'issue de la mission et de le soumettre
        dans un délai de <strong>{{ $delai_rapport ?? 'trois (03) jours' }}</strong>.</p>

    <p>La présente ordre de mission est délivrée à l'intéressé(e) pour servir et valoir ce que de droit.</p>
@endsection

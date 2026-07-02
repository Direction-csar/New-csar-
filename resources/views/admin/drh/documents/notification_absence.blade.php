@extends('admin.drh.documents._layout')

@section('doc-title', 'Notification d\'absence injustifiée')
@section('reference', $reference ?? '_________')
@section('titre', 'Notification d\'Absence Injustifiée')

@section('corps')
    <p>Objet : <strong>Constat d'absence injustifiée</strong></p>

    <p>Monsieur/Madame <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>
        à la <strong>{{ $personnel?->direction_service ?? '__________' }}</strong>,</p>

    <p>Nous avons constaté votre absence non autorisée et non justifiée à votre poste de travail
        @if(isset($date_debut_absence) && isset($date_fin_absence))
            du <strong>{{ \Carbon\Carbon::parse($date_debut_absence)->format('d/m/Y') }}</strong>
            au <strong>{{ \Carbon\Carbon::parse($date_fin_absence)->format('d/m/Y') }}</strong>,
        @else
            le <strong>{{ isset($date_debut_absence) ? \Carbon\Carbon::parse($date_debut_absence)->format('d/m/Y') : '__________' }}</strong>,
        @endif
        soit <strong>{{ $nombre_jours ?? '____' }}</strong> jour(s) d'absence.</p>

    <p>Cette absence, contraire à vos obligations professionnelles, perturbe le bon fonctionnement du service.
        Nous vous invitons à fournir, dans les meilleurs délais, toute pièce justificative motivant votre absence.</p>

    <p>À défaut de justification valable, les jours d'absence concernés feront l'objet d'une retenue sur salaire
        et pourront donner lieu à une sanction disciplinaire conformément au règlement intérieur.</p>

    <p>Nous vous prions d'agréer l'expression de nos salutations distinguées.</p>
@endsection

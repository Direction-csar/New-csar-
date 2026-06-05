@extends('admin.drh.documents._layout')

@section('doc-title', 'Bon de sortie')
@section('reference', $reference ?? '_________')
@section('titre', 'Bon de Sortie')

@section('corps')
    <p>Objet : <strong>Autorisation de sortie exceptionnelle</strong></p>

    <p>Monsieur/Madame <strong>{{ $personnel?->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel?->matricule ?? '________' }}</strong>,
        <strong>{{ $personnel?->poste_actuel ?? '__________' }}</strong>
        à la <strong>{{ $personnel?->direction_service ?? '__________' }}</strong>,</p>

    <p>est autorisé(e) à quitter le service le
        <strong>{{ isset($date_sortie) ? \Carbon\Carbon::parse($date_sortie)->format('d/m/Y') : '__________' }}</strong>,
        à partir de <strong>{{ $heure_sortie ?? '__h__' }}</strong>,</p>

    <p><strong>Motif :</strong> {{ $motif ?? '____________________________________________' }}<br>
        <strong>Destination :</strong> {{ $destination ?? '________________________' }}</p>

    <p>L'intéressé(e) devra être de retour au plus tard à
        <strong>{{ $heure_retour ?? '__h__' }}</strong>.</p>

    <p>Par conséquent, ce bon de sortie est délivré à l'intéressé(e) pour servir et valoir ce que de droit.</p>

    <table style="width:100%; margin-top:20px; border-collapse:collapse; font-size:10pt;">
        <tr>
            <td style="width:33%; text-align:center; vertical-align:top;">
                <div style="font-weight:bold;">Le Demandeur</div>
                <div style="height:45px;"></div>
                <div>{{ $personnel?->prenoms_nom ?? '' }}</div>
            </td>
            <td style="width:33%; text-align:center; vertical-align:top;">
                <div style="font-weight:bold;">Visa Chef de Service</div>
                <div style="height:45px;"></div>
                <div style="font-size:8pt; color:#666;">(Signature et tampon)</div>
            </td>
            <td style="width:33%; text-align:center; vertical-align:top;">
                <div style="font-weight:bold;">Visa Sécurité</div>
                <div style="height:45px;"></div>
                <div style="font-size:8pt; color:#666;">(Heure de retour)</div>
            </td>
        </tr>
    </table>
@endsection

@section('signataire-role', '')
@section('signataire-nom', '')

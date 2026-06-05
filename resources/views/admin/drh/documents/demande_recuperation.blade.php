@extends('admin.drh.documents._layout')

@section('doc-title', 'Demande de récupération')
@section('reference', $reference ?? '_________')
@section('titre', 'Demande de Récupération')

@section('corps')
    <p>Je soussigné(e) <strong>{{ $personnel->prenoms_nom ?? '____________________' }}</strong>,
        matricule <strong>{{ $personnel->matricule ?? '________' }}</strong>,
        exerçant les fonctions de <strong>{{ $personnel->poste_actuel ?? '__________' }}</strong>
        au sein de la <strong>{{ $personnel->direction_service ?? '__________' }}</strong>,</p>

    <p>ai l'honneur de solliciter de votre haute bienveillance l'autorisation de récupérer
        <strong>{{ $nombre_jours ?? '____' }}</strong> jour(s) au titre des heures supplémentaires effectuées
        @if(isset($date_heures_sup)) le <strong>{{ \Carbon\Carbon::parse($date_heures_sup)->format('d/m/Y') }}</strong> @endif.</p>

    <p>Cette récupération est sollicitée
        du <strong>{{ isset($date_debut) ? \Carbon\Carbon::parse($date_debut)->format('d/m/Y') : '__________' }}</strong>
        au <strong>{{ isset($date_fin) ? \Carbon\Carbon::parse($date_fin)->format('d/m/Y') : '__________' }}</strong>.</p>

    <p>Motif : {{ $motif ?? '____________________________________________' }}</p>

    <p>Dans l'attente d'une suite favorable, je vous prie d'agréer l'expression de ma haute considération.</p>

    <table style="width:100%; margin-top:24px; border-collapse:collapse; font-size:10pt;">
        <tr>
            <td style="width:50%; text-align:center; vertical-align:top;">
                <div style="font-weight:bold;">Le Demandeur</div>
                <div style="height:55px;"></div>
                <div style="font-weight:bold;">{{ $personnel->prenoms_nom ?? '' }}</div>
            </td>
            <td style="width:50%; text-align:center; vertical-align:top;">
                <div style="font-weight:bold;">Avis du Supérieur Hiérarchique</div>
                <div style="height:55px;"></div>
                <div style="font-size:8.5pt; color:#666;">(Favorable / Défavorable)</div>
            </td>
        </tr>
    </table>
@endsection

@section('signataire-role', '')
@section('signataire-nom', '')

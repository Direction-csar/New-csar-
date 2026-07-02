<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Demande {{ $request->tracking_code }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { border-bottom: 2px solid #22c55e; padding-bottom: 10px; margin-bottom: 20px; }
        .title { font-size: 18px; font-weight: bold; }
        .section { margin-bottom: 15px; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">FICHE DE DEMANDE CSAR</div>
        <div>Code de suivi : {{ $request->tracking_code }}</div>
    </div>
    <div class="section">
        <div class="label">Demandeur :</div>
        <div>{{ $request->full_name ?? 'Non renseigné' }}</div>
    </div>
    <div class="section">
        <div class="label">Email :</div>
        <div>{{ $request->email ?? 'Non renseigné' }}</div>
    </div>
    <div class="section">
        <div class="label">Téléphone :</div>
        <div>{{ $request->phone ?? 'Non renseigné' }}</div>
    </div>
    <div class="section">
        <div class="label">Description :</div>
        <div>{{ $request->description ?? 'Aucune' }}</div>
    </div>
    <div class="section">
        <div class="label">Statut :</div>
        <div>{{ $request->workflow_status_label ?? $request->status }}</div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>{{ $title }}</title>
<style>
    body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:28px}
    .head{border-bottom:3px solid #0B1F3A;padding-bottom:12px;margin-bottom:16px}
    .head table{width:100%}
    .inst{font-size:13px;font-weight:bold;color:#0B1F3A}
    .csar{font-size:10px;color:#666}
    h1{font-size:16px;color:#0B1F3A;margin:6px 0}
    .meta{font-size:10px;color:#555;margin-top:4px}
    .filters{background:#F4F6FA;border:1px solid #E2E8F0;border-radius:6px;padding:8px 12px;font-size:10px;margin-bottom:14px}
    table{width:100%;border-collapse:collapse}
    th{background:#0B1F3A;color:#fff;font-size:10px;text-transform:uppercase;padding:7px 8px;text-align:left}
    td{padding:6px 8px;border-bottom:1px solid #E5E7EB;font-size:10.5px}
    tr:nth-child(even) td{background:#FAFBFD}
    .foot{margin-top:22px;padding-top:10px;border-top:1px solid #E5E7EB;font-size:9.5px;color:#666;text-align:center}
    .summary{font-size:10.5px;margin-bottom:10px}
</style>
</head>
<body>
<div class="head">
    <table><tr>
        <td>
            <div class="inst">Commissariat à la Sécurité Alimentaire et à la Résilience</div>
            <div class="csar">République du Sénégal — {{ $portalLabel }}</div>
        </td>
        <td style="text-align:right">
            <div class="csar">Réf : {{ $reference }}</div>
            <div class="csar">Généré le {{ $generatedAt->format('d/m/Y H:i') }}</div>
        </td>
    </tr></table>
    <h1>{{ $title }}</h1>
    <div class="meta">Document généré automatiquement — données issues de la base de production du système CSAR Stock.</div>
</div>

@if($filters)
<div class="filters">
    <b>Filtres appliqués :</b>
    @foreach($filters as $k => $v)
        <span style="margin-right:12px"><b>{{ $k }} :</b> {{ is_array($v) ? json_encode($v, JSON_UNESCAPED_UNICODE) : $v }}</span>
    @endforeach
</div>
@endif

<div class="summary"><b>Synthèse :</b> {{ count($rows) }} ligne(s) de données détaillées ci-dessous.</div>

<table>
    <thead><tr>
        @foreach($columns as $c)<th>{{ $c }}</th>@endforeach
    </tr></thead>
    <tbody>
    @forelse($rows as $r)
        <tr>
            @foreach($r as $cell)<td>{{ $cell }}</td>@endforeach
        </tr>
    @empty
        <tr><td colspan="{{ count($columns) }}" style="text-align:center;padding:20px;color:#888">Aucune donnée pour les filtres sélectionnés.</td></tr>
    @endforelse
    </tbody>
</table>

<div class="foot">
    Commissariat à la Sécurité Alimentaire et à la Résilience (CSAR) — République du Sénégal<br>
    {{ $title }} — Référence {{ $reference }} — Document confidentiel, réservé à l'usage institutionnel.
</div>
</body>
</html>

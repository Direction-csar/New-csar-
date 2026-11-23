<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
    h1 { font-size: 15px; margin-bottom: 2px; }
    .meta { color: #666; font-size: 9px; margin-bottom: 12px; }
    h2 { font-size: 12px; margin: 14px 0 6px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #bbb; padding: 3px 5px; text-align: left; }
    th { background: #f0f0f0; }
    .num { text-align: right; }
    .totaux td { font-weight: bold; }
</style>
</head>
<body>
<h1>CSAR — Situation des stocks des magasins</h1>
<div class="meta">Édité le {{ $s['generated_at'] }} — stocks officiels (mouvements vérifiés)</div>

<h2>Stocks par magasin</h2>
<table>
    <thead><tr><th>Magasin</th><th>Région</th><th>Produit</th><th>Format</th><th>Unité</th><th class="num">Contenants</th><th class="num">Quantité</th></tr></thead>
    <tbody>
    @foreach($s['warehouses'] as $w)
        @forelse($w['products'] as $p)
        <tr>
            <td>{{ $w['name'] }}</td><td>{{ $w['region'] ?? '' }}</td>
            <td>{{ $p['name'] }}</td><td>{{ $p['format'] }}</td><td>{{ $p['unit'] }}</td>
            <td class="num">{{ number_format($p['contenants'], 2, ',', ' ') }}</td>
            <td class="num">{{ number_format($p['quantite'], 2, ',', ' ') }}</td>
        </tr>
        @empty
        <tr><td>{{ $w['name'] }}</td><td>{{ $w['region'] ?? '' }}</td><td colspan="5" class="meta">Aucun stock</td></tr>
        @endforelse
    @endforeach
    </tbody>
</table>

<h2>Totaux par produit</h2>
<table class="totaux">
    <thead><tr><th>Produit</th><th>Unité</th><th class="num">Contenants</th><th class="num">Quantité</th></tr></thead>
    <tbody>
    @foreach($s['totals'] as $t)
    <tr><td>{{ $t['name'] }}</td><td>{{ $t['unit'] }}</td>
        <td class="num">{{ number_format($t['contenants'], 2, ',', ' ') }}</td>
        <td class="num">{{ number_format($t['quantite'], 2, ',', ' ') }}</td></tr>
    @endforeach
    </tbody>
</table>

@if(!empty($s['operations']) && ($s['operations']['en_cours'] > 0 || $s['operations']['mouvements_en_attente'] > 0))
<h2>En attente de vérification</h2>
<div class="meta">Opérations en cours : {{ $s['operations']['en_cours'] }} — Mouvements en attente : {{ $s['operations']['mouvements_en_attente'] }}</div>
<table>
    <thead><tr><th>Date</th><th>Magasin</th><th>Opération</th><th>Libellé</th><th class="num">Contenants</th></tr></thead>
    <tbody>
    @foreach($s['operations']['derniers_en_attente'] as $m)
    <tr><td>{{ $m['date'] }}</td><td>{{ $m['warehouse'] }}</td><td>{{ $m['operation'] ?? '—' }}</td>
        <td>{{ $m['reason'] }}</td><td class="num">{{ number_format($m['quantity'], 2, ',', ' ') }}</td></tr>
    @endforeach
    </tbody>
</table>
@endif

<h2>Derniers mouvements</h2>
<table>
    <thead><tr><th>Date</th><th>Magasin</th><th>Type</th><th>Libellé</th><th class="num">Contenants</th></tr></thead>
    <tbody>
    @foreach($s['recent'] as $m)
    <tr><td>{{ $m['date'] }}</td><td>{{ $m['warehouse'] }}</td><td>{{ $m['type'] }}</td>
        <td>{{ $m['reason'] }}</td><td class="num">{{ number_format($m['quantity'], 2, ',', ' ') }}</td></tr>
    @endforeach
    </tbody>
</table>
</body>
</html>

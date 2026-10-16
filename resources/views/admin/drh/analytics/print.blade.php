<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $def['title'] }} - CSAR DRH</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px; color: #111827; margin: 20px; }
        .head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #5a9e3d; padding-bottom: 10px; margin-bottom: 14px; }
        .head img { height: 54px; }
        .head .org { font-size: 10px; text-transform: uppercase; color: #374151; line-height: 1.3; }
        h1 { font-size: 16px; margin: 0 0 2px; color: #1e293b; }
        .sub { color: #6b7280; font-size: 10px; }
        .meta { display: flex; gap: 18px; flex-wrap: wrap; margin: 10px 0 12px; font-size: 10px; color: #374151; }
        .meta b { color: #111827; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-size: 9.5px; text-transform: uppercase; letter-spacing: .03em; }
        tr:nth-child(even) td { background: #fafafa; }
        .foot { margin-top: 14px; display: flex; justify-content: space-between; color: #6b7280; font-size: 9px; }
        .toolbar { position: fixed; top: 10px; right: 10px; display: flex; gap: 8px; }
        .toolbar button { background: #5a9e3d; color: #fff; border: none; border-radius: 6px; padding: 8px 14px; font-weight: 600; cursor: pointer; }
        .toolbar button.sec { background: #6b7280; }
        .empty { text-align: center; padding: 30px; color: #6b7280; font-style: italic; }
        @media print { .toolbar { display: none; } body { margin: 10mm; } @page { size: A4 landscape; margin: 10mm; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">Imprimer / Enregistrer en PDF</button>
        <button class="sec" onclick="window.close()">Fermer</button>
    </div>

    <div class="head">
        <div class="org">
            République du Sénégal<br>
            Ministère de la Famille et des Solidarités<br>
            <strong>Commissariat à la Sécurité Alimentaire et à la Résilience</strong><br>
            Direction des Ressources humaines
        </div>
        <img src="{{ asset('images/csar-logo.png') }}" alt="CSAR">
    </div>

    <h1>{{ $def['title'] }}</h1>
    <div class="sub">{{ $def['subtitle'] }}</div>

    <div class="meta">
        <span><b>Généré le :</b> {{ now()->format('d/m/Y à H:i') }}</span>
        <span><b>Résultats :</b> {{ $rows->count() }}</span>
        @foreach(collect($filters)->except(['sort', 'dir']) as $k => $v)
            <span><b>{{ ucfirst(str_replace('_', ' ', $k)) }} :</b> {{ $v }}</span>
        @endforeach
    </div>

    @if($rows->isEmpty())
        <div class="empty">Aucun agent ne correspond aux critères sélectionnés.</div>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width:28px">#</th>
                    @foreach($columns as $c)<th>{{ $labels[$c] ?? $c }}</th>@endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        @foreach($columns as $c)<td>{{ $r[$c] ?? '—' }}</td>@endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="foot">
        <span>Document interne — données nominatives confidentielles (usage DRH)</span>
        <span>CSAR — {{ now()->format('Y') }}</span>
    </div>
</body>
</html>

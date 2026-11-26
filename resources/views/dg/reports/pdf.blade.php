<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $report['title'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin: 0 0 4px 0; }
        .meta { color: #666; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #e9eef5; font-weight: bold; }
        tr:nth-child(even) td { background: #fafafa; }
    </style>
</head>
<body>
    <h1>{{ $report['title'] }}</h1>
    <div class="meta">
        Plateforme CSAR · Généré le {{ $generatedAt->format('d/m/Y H:i') }} · {{ count($report['rows']) }} ligne(s)
    </div>
    <table>
        <thead>
            <tr>
                @foreach($report['headers'] as $header)
                    <th>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell ?? '—' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($report['headers']) }}">Aucune donnée pour ce rapport.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

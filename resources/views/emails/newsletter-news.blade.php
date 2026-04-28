<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouvelle publication CSAR</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #7c3aed; color: white; padding: 25px 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0 0; font-size: 13px; opacity: 0.85; }
        .content { padding: 30px 20px; background: #f8fafc; }
        .news-card { background: white; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 20px; }
        .news-image { width: 100%; height: 200px; object-fit: cover; }
        .news-body { padding: 20px; }
        .category-badge { display: inline-block; background: #ede9fe; color: #7c3aed; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px; }
        .news-title { font-size: 20px; font-weight: bold; color: #1e293b; margin: 10px 0; }
        .news-excerpt { color: #64748b; font-size: 14px; margin-bottom: 20px; }
        .btn { display: inline-block; background: #7c3aed; color: white !important; padding: 12px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; }
        .footer { background: #64748b; color: white; padding: 15px; text-align: center; font-size: 12px; border-radius: 0 0 8px 8px; }
        .footer a { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📰 CSAR — Nouvelle Publication</h1>
            <p>Commissariat à la Sécurité Alimentaire et à la Résilience</p>
        </div>

        <div class="content">
            <p>Bonjour,</p>
            <p>Une nouvelle publication vient d'être mise en ligne sur le site du CSAR :</p>

            <div class="news-card">
                @if($news->featured_image)
                <img src="{{ asset('storage/' . $news->featured_image) }}" alt="{{ $news->title }}" class="news-image">
                @endif
                <div class="news-body">
                    <span class="category-badge">{{ ucfirst($news->category ?? 'Actualité') }}</span>
                    <div class="news-title">{{ $news->title }}</div>
                    @if($news->excerpt)
                    <p class="news-excerpt">{{ $news->excerpt }}</p>
                    @endif
                    <a href="{{ $newsUrl }}" class="btn">Lire la suite →</a>
                </div>
            </div>

            <p style="font-size: 13px; color: #64748b;">
                Publié le {{ now()->format('d/m/Y à H:i') }}
            </p>

            <p>Cordialement,<br>
            <strong>L'équipe Communication du CSAR</strong></p>
        </div>

        <div class="footer">
            <p>CSAR | 📧 contact@csar.sn | 🌐 www.csar.sn</p>
            <p>
                <a href="{{ url('/newsletter/unsubscribe?email=' . urlencode($unsubscribeEmail)) }}">
                    Se désabonner
                </a>
            </p>
        </div>
    </div>
</body>
</html>

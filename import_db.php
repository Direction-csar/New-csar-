<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MediaEvent;
use App\Models\MediaFile;

$eventId = 2;
$dir = __DIR__.'/storage/app/public/media-share/'.$eventId.'/documents';

$files = [
    'Bilan_Social_CSAR_2025_ version powerpoint 18062026.pptx',
    'Présentation rapport annuel 2025_Matinée de partage (1) (1).pptx',
    'Production Rapport Annuel CSAR 2025 15.04.2026 copy (1).pdf',
];

$count = 0;
foreach ($files as $filename) {
    $path = $dir.'/'.$filename;
    if (!file_exists($path)) {
        echo "SKIP (not found): $filename\n";
        continue;
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $type = in_array($ext, ['pdf','ppt','pptx']) ? 'document' : 'image';
    $size = filesize($path);
    $mime = match($ext) {
        'pdf' => 'application/pdf',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        default => 'application/octet-stream',
    };
    $dbPath = 'media-share/'.$eventId.'/documents/'.$filename;

    // Supprimer l'ancienne entrée si existe
    MediaFile::where('media_event_id', $eventId)
        ->where('file_name', $filename)
        ->delete();

    MediaFile::create([
        'media_event_id' => $eventId,
        'type'           => $type,
        'file_path'      => $dbPath,
        'file_name'      => $filename,
        'file_size'      => $size,
        'mime_type'      => $mime,
    ]);
    echo "OK: $filename ($size bytes, $type)\n";
    $count++;
}

echo "\nTotal importé: $count\n";

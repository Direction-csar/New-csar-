<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$reports = App\Models\SimReport::all();
foreach ($reports as $r) {
    $exists = $r->document_file && Illuminate\Support\Facades\Storage::disk('public')->exists($r->document_file);
    $status = $r->status . ($r->is_public ? '/public' : '/private');
    echo "ID: {$r->id} | Status: {$status} | File: " . ($r->document_file ?: 'null') . " | Exists: " . ($exists ? 'YES' : 'NO') . PHP_EOL;
}

// Also list existing files
echo "---EXISTING FILES---" . PHP_EOL;
$files = Illuminate\Support\Facades\Storage::disk('public')->files('sim-reports/documents');
foreach (array_slice($files, 0, 10) as $f) {
    echo $f . PHP_EOL;
}

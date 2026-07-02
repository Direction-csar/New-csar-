<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$reports = App\Models\SimReport::where('status', 'published')->where('is_public', 1)->get();
foreach ($reports as $r) {
    $exists = $r->document_file && Illuminate\Support\Facades\Storage::disk('public')->exists($r->document_file);
    echo "ID: {$r->id} | File: " . ($r->document_file ?: 'null') . " | Exists: " . ($exists ? 'YES' : 'NO') . PHP_EOL;
}

<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo 'Gallery exists: ' . (Illuminate\Support\Facades\Storage::disk('public')->exists('gallery/TQzbxCFbGQHf7ZS2a8JID374KQsmRAVxKY0m3lfg.jpg') ? 'YES' : 'NO') . PHP_EOL;
echo 'Sim doc exists: ' . (Illuminate\Support\Facades\Storage::disk('public')->exists('sim-reports/documents/9xNfti1k4GzvHY4ZVPj3EpyhdgPfEqP6oSggAIM2.pdf') ? 'YES' : 'NO') . PHP_EOL;
echo 'Root: ' . storage_path('app/public') . PHP_EOL;

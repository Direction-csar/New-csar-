<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$ids = App\Models\SimReport::where('status', 'published')->where('is_public', 1)->pluck('id')->toArray();
echo implode(',', $ids) . PHP_EOL;

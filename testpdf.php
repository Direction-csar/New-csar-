<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PublicRequest;
use Barryvdh\DomPDF\Facade\Pdf;

try {
    $r = PublicRequest::first();
    if ($r) {
        echo "Request: " . $r->tracking_code . "\n";
        $pdf = Pdf::loadView('public.pdf.request', ['request' => $r]);
        echo "PDF OK\n";
    } else {
        echo "No request\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

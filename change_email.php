<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = \App\Models\User::where('email', 'admin@csar.sn')->first();
if ($u) {
    $u->email = 'dsar@csar.sn';
    $u->save();
    echo "OK: email change en dsar@csar.sn\n";
} else {
    echo "Utilisateur non trouve\n";
}

<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('email', 'magasinier@csar.sn')->first();
if (!$user) {
    echo "USER_NOT_FOUND\n";
    exit;
}

$token = \App\Models\User::find($user->id)->createToken('test', ['warehouse'])->plainTextToken;
echo "Token: " . substr($token, 0, 40) . "...\n";

$client = new \GuzzleHttp\Client();
try {
    $res = $client->get('https://www.csar.sn/api/warehouse/v1/products', [
        'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'],
        'verify' => false,
    ]);
    $data = json_decode($res->getBody(), true);
    echo "Status: " . $res->getStatusCode() . "\n";
    echo "Products count: " . count($data['products'] ?? []) . "\n";
    if (!empty($data['products'])) {
        $first = $data['products'][0];
        echo "First product: " . $first['name'] . " - formats: " . implode(',', $first['formats_kg']) . "\n";
    }
} catch (\Exception $e) {
    echo "API ERROR: " . $e->getMessage() . "\n";
}

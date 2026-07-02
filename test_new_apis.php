<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$user = DB::table('users')->where('email', 'magasinier@csar.sn')->first();
if (!$user) { echo "USER_NOT_FOUND\n"; exit; }

$token = \App\Models\User::find($user->id)->createToken('test', ['warehouse'])->plainTextToken;
echo "Token OK\n";

$client = new \GuzzleHttp\Client();
$headers = ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json'];

try {
    // Test alerts
    $res = $client->get('https://www.csar.sn/api/warehouse/v1/alerts', ['headers' => $headers, 'verify' => false]);
    $data = json_decode($res->getBody(), true);
    echo "ALERTS: count=" . $data['count'] . "\n";

    // Test stock-status (need warehouse_id)
    $wh = DB::table('warehouses')->first();
    $res = $client->get('https://www.csar.sn/api/warehouse/v1/stock-status?warehouse_id=' . $wh->id, ['headers' => $headers, 'verify' => false]);
    $data = json_decode($res->getBody(), true);
    echo "STOCK-STATUS: wh=" . $data['warehouse_name'] . " products=" . count($data['products']) . "\n";

    // Test receipt
    $mv = DB::table('stock_movements')->first();
    if ($mv) {
        $res = $client->get('https://www.csar.sn/api/warehouse/v1/receipt/' . $mv->reference, ['headers' => $headers, 'verify' => false]);
        $data = json_decode($res->getBody(), true);
        echo "RECEIPT: ref=" . ($data['receipt']['reference'] ?? 'N/A') . " type=" . ($data['receipt']['type_label'] ?? 'N/A') . "\n";
    }

    echo "ALL OK\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

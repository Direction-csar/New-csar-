<?php
require '/var/www/csar/vendor/autoload.php';
$app = require_once '/var/www/csar/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

/*
 * Structure : region => [ ['ville', is_principal(Inspection Régionale)] ... ]
 */
$data = [
    'Dakar' => [
        ['Dakar', true],
        ['Thiaroye', false],
    ],
    'Thiès' => [
        ['Thiès', true],
        ['Mbour', false],
        ['Tivaouane', false],
        ['Khombole', false],
        ['Méouane', false],
    ],
    'Diourbel' => [
        ['Diourbel', true],
        ['Mbacké', false],
        ['Bambey', false],
    ],
    'Fatick' => [
        ['Fatick', true],
        ['Foundiougne', false],
        ['Gossas', false],
        ['Sokone', false],
    ],
    'Kaolack' => [
        ['Kaolack', true],
        ['Nioro', false],
        ['Wack Ngouna', false],
        ['Keur Madiabel', false],
        ['Médina Sabakh', false],
    ],
    'Kaffrine' => [
        ['Kaffrine', true],
        ['Koungheul', false],
        ['Birkelane', false],
        ['Malem Hodar', false],
    ],
    'Louga' => [
        ['Louga', true],
        ['Linguère', false],
        ['Kebemer', false],
        ['Dahra', false],
    ],
    'Saint-Louis' => [
        ['Saint-Louis', true],
        ['Dagana', false],
        ['Podor', false],
    ],
    'Matam' => [
        ['Matam', true], // Inspection Régionale / Ourossogui
        ['Kanel', false],
        ['Ranérou', false],
    ],
    'Tambacounda' => [
        ['Tambacounda', true],
        ['Koumpentoum', false],
        ['Goudiry', false],
        ['Bakel', false],
    ],
    'Kédougou' => [
        ['Kédougou', true],
        ['Salémata', false],
        ['Saraya', false],
    ],
    'Kolda' => [
        ['Kolda', true],
        ['Vélingara', false],
        ['Medina Yoro Foula', false],
    ],
    'Sédhiou' => [
        ['Sédhiou', true],
        ['Bounkiling', false],
        ['Goudomp', false],
    ],
    'Ziguinchor' => [
        ['Ziguinchor', true],
        ['Bignona', false],
        ['Oussouye', false],
    ],
];

$created = 0;
$skipped = 0;

DB::beginTransaction();
try {
    foreach ($data as $region => $villes) {
        foreach ($villes as [$ville, $isPrincipal]) {
            $name = $isPrincipal
                ? "Magasin CSAR {$ville} (Inspection Régionale)"
                : "Magasin CSAR {$ville}";

            // Éviter les doublons sur name + city
            $exists = DB::table('warehouses')
                ->where('city', $ville)
                ->where('region', $region)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('warehouses')->insert([
                'name'          => $name,
                'description'   => $isPrincipal
                    ? "Inspection Régionale CSAR de {$region}"
                    : "Magasin de stockage CSAR — {$ville}, région de {$region}",
                'address'       => "{$ville}, {$region}, Sénégal",
                'region'        => $region,
                'city'          => $ville,
                'latitude'      => 14.4974,
                'longitude'     => -14.4524,
                'capacity'      => $isPrincipal ? 5000 : 1500,
                'current_stock' => 0,
                'status'        => 'active',
                'is_active'     => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
            $created++;
        }
    }
    DB::commit();
    echo "Magasins créés : {$created}\n";
    echo "Ignorés (déjà existants) : {$skipped}\n";
    echo "Total magasins en base : " . DB::table('warehouses')->count() . "\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERREUR : " . $e->getMessage() . "\n";
}

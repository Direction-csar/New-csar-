<?php

namespace Database\Seeders;

use App\Models\SimCollection;
use App\Models\SimCollectionItem;
use App\Models\SimMarket;
use App\Models\SimProduct;
use App\Models\SimProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimPricesTestSeeder extends Seeder
{
    /**
     * Données de test SIM (catalogue + marchés + collectes + prix).
     * Objectif: permettre de visualiser carte + consultation prix avec des données en base.
     */
    public function run(): void
    {
        // Prérequis : admin
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::firstOrCreate(
                ['email' => 'admin@csar.sn'],
                [
                    'name' => 'Administrateur CSAR',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'role_id' => 1,
                    'is_active' => true,
                ]
            );
        }

        // 1) Géographie + marchés + assignations (seeders existants)
        $this->call([
            SimGeographySeeder::class,
            SimSampleDataSeeder::class,
        ]);

        // 2) Coordonnées marchés (pour la carte)
        $this->ensureMarketCoordinates();

        // 3) Catalogue SIM (catégories + produits)
        $catalog = $this->seedCatalog();

        // 4) Collectes + prix (8 semaines) pour les marchés existants
        $agent = User::where('email', 'agent@csar.sn')->first();
        $supervisor = User::where('email', 'responsable@csar.sn')->first();
        if (!$agent || !$supervisor) {
            $this->command->warn('Agent/superviseur SIM non trouvés (SimSampleDataSeeder).');
            return;
        }

        $markets = SimMarket::where('is_active', true)->whereNotNull('latitude')->whereNotNull('longitude')->limit(6)->get();
        if ($markets->isEmpty()) {
            $this->command->warn('Aucun marché avec coordonnées. Vérifiez sim_markets.');
            return;
        }

        $weeks = 8;
        $products = $catalog['products'];

        DB::transaction(function () use ($weeks, $markets, $products, $agent, $supervisor, $admin) {
            foreach ($markets as $market) {
                for ($w = 0; $w < $weeks; $w++) {
                    $date = now()->startOfWeek()->subWeeks($w)->addDays(2)->toDateString(); // mercredi de la semaine

                    $collection = SimCollection::firstOrCreate(
                        [
                            'sim_market_id' => $market->id,
                            'collector_id' => $agent->id,
                            'collected_on' => $date,
                        ],
                        [
                            'supervisor_id' => $supervisor->id,
                            'validated_by' => $admin->id,
                            'market_day' => $market->market_day,
                            'status' => SimCollection::STATUS_VALIDATED,
                            'started_at' => now()->subHours(6),
                            'arrived_at' => now()->subHours(5),
                            'submitted_at' => now()->subHours(4),
                            'reviewed_at' => now()->subHours(3),
                            'validated_at' => now()->subHours(2),
                            'last_activity_at' => now()->subHours(2),
                            'progress_percentage' => 100,
                            'has_live_tracking' => false,
                        ]
                    );

                    foreach ($products as $idx => $product) {
                        // Prix "réalistes" et légèrement variables selon semaine et marché
                        $base = match (Str::lower($product->name)) {
                            'mil' => 220,
                            'sorgho' => 200,
                            'maïs' => 210,
                            'mais' => 210,
                            'riz local' => 350,
                            'riz importé' => 390,
                            default => 250,
                        };
                        $marketFactor = 1 + (($market->id % 5) * 0.01);
                        $trend = 1 + ((($weeks - 1 - $w) * 0.006)); // hausse légère vers la semaine récente
                        $noise = ((($idx + $market->id + $w) % 7) - 3) * 2; // -6..+6
                        $priceRetail = max(50, round(($base * $marketFactor * $trend) + $noise, 0));

                        SimCollectionItem::updateOrCreate(
                            [
                                'sim_collection_id' => $collection->id,
                                'sim_product_id' => $product->id,
                            ],
                            [
                                'quantity_observed' => 100,
                                'price_retail' => $priceRetail,
                                'price_wholesale' => max(40, $priceRetail - 15),
                                'price_producer' => max(30, $priceRetail - 25),
                                'origin_label' => 'Local',
                            ]
                        );
                    }
                }
            }
        });

        $this->command->info('✅ Données SIM de test ajoutées (catalogue + collectes + prix).');
        $this->command->info('Pages à vérifier :');
        $this->command->info('  - /sim-reports');
        $this->command->info('  - /fr/sim/carte-marches');
        $this->command->info('  - /fr/sim/consultation-prix');
        $this->command->info('  - /dg/sim');
    }

    private function ensureMarketCoordinates(): void
    {
        $coords = [
            'Marché Kermel' => ['lat' => 14.6726, 'lng' => -17.4315, 'commune' => 'Dakar-Plateau'],
            'Marché Tilène' => ['lat' => 14.6898, 'lng' => -17.4553, 'commune' => 'Dakar'],
            'Marché Castors' => ['lat' => 14.7115, 'lng' => -17.4532, 'commune' => 'Dakar'],
        ];

        foreach ($coords as $name => $c) {
            $m = SimMarket::where('name', $name)->first();
            if ($m) {
                $m->update([
                    'latitude' => $m->latitude ?: $c['lat'],
                    'longitude' => $m->longitude ?: $c['lng'],
                    'commune' => $m->commune ?: $c['commune'],
                    'is_active' => true,
                ]);
            }
        }
    }

    private function seedCatalog(): array
    {
        $categories = [
            ['name' => 'Céréales', 'order' => 1],
            ['name' => 'Légumineuses', 'order' => 2],
            ['name' => 'Tubercules', 'order' => 3],
            ['name' => 'Légumes', 'order' => 4],
            ['name' => 'Autres produits alimentaires', 'order' => 5],
        ];

        $catModels = [];
        foreach ($categories as $c) {
            $catModels[$c['name']] = SimProductCategory::firstOrCreate(
                ['name' => $c['name']],
                [
                    'slug' => Str::slug($c['name']),
                    'display_order' => $c['order'],
                    'is_active' => true,
                ]
            );
        }

        $products = [
            ['cat' => 'Céréales', 'name' => 'Mil', 'unit' => 'kg', 'order' => 1],
            ['cat' => 'Céréales', 'name' => 'Sorgho', 'unit' => 'kg', 'order' => 2],
            ['cat' => 'Céréales', 'name' => 'Maïs', 'unit' => 'kg', 'order' => 3],
            ['cat' => 'Céréales', 'name' => 'Riz local', 'unit' => 'kg', 'order' => 4],
            ['cat' => 'Céréales', 'name' => 'Riz importé', 'unit' => 'kg', 'order' => 5],
            ['cat' => 'Légumineuses', 'name' => 'Niébé', 'unit' => 'kg', 'order' => 1],
            ['cat' => 'Tubercules', 'name' => 'Pomme de terre', 'unit' => 'kg', 'order' => 1],
            ['cat' => 'Légumes', 'name' => 'Oignon', 'unit' => 'kg', 'order' => 1],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $cat = $catModels[$p['cat']];
            $productModels[] = SimProduct::firstOrCreate(
                [
                    'sim_product_category_id' => $cat->id,
                    'name' => $p['name'],
                ],
                [
                    'slug' => Str::slug($p['name']),
                    'unit' => $p['unit'],
                    'origin_type' => SimProduct::ORIGIN_BOTH,
                    'display_order' => $p['order'],
                    'is_active' => true,
                ]
            );
        }

        return ['categories' => $catModels, 'products' => collect($productModels)];
    }
}


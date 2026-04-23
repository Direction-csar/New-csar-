<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SimProductsSeeder extends Seeder
{
    public function run(): void
    {
        // Données issues de la FICHE DE COLLECTE HEBDOMADAIRE DES DONNEES CSAR
        $catalogue = [
            [
                'name' => 'Céréales',
                'slug' => 'cereales',
                'display_order' => 1,
                'products' => [
                    ['name' => 'Mil Youna',             'unit' => 'kg', 'origin_type' => 'local',    'order' => 1],
                    ['name' => 'Mil Sanio',             'unit' => 'kg', 'origin_type' => 'local',    'order' => 2],
                    ['name' => 'Sorgho Local',          'unit' => 'kg', 'origin_type' => 'local',    'order' => 3],
                    ['name' => 'Sorgho Importé',        'unit' => 'kg', 'origin_type' => 'imported', 'order' => 4],
                    ['name' => 'Maïs Local Jaune',      'unit' => 'kg', 'origin_type' => 'local',    'order' => 5],
                    ['name' => 'Maïs Local Blanc',      'unit' => 'kg', 'origin_type' => 'local',    'order' => 6],
                    ['name' => 'Maïs Importé Jaune',    'unit' => 'kg', 'origin_type' => 'imported', 'order' => 7],
                    ['name' => 'Maïs Importé Rouge',    'unit' => 'kg', 'origin_type' => 'imported', 'order' => 8],
                    ['name' => 'Maïs Importé Blanc',    'unit' => 'kg', 'origin_type' => 'imported', 'order' => 9],
                    ['name' => 'Riz Ordinaire',         'unit' => 'kg', 'origin_type' => 'both',     'order' => 10],
                    ['name' => 'RAP (Riz Aromatique)',  'unit' => 'kg', 'origin_type' => 'imported', 'order' => 11],
                    ['name' => 'Riz Parfumé Importé',   'unit' => 'kg', 'origin_type' => 'imported', 'order' => 12],
                    ['name' => 'Riz Local Décortiqué',  'unit' => 'kg', 'origin_type' => 'local',    'order' => 13],
                ],
            ],
            [
                'name' => 'Légumineuses',
                'slug' => 'legumineuses',
                'display_order' => 2,
                'products' => [
                    ['name' => 'Niébé 1ère qualité',       'unit' => 'kg', 'origin_type' => 'local', 'order' => 1],
                    ['name' => 'Niébé 2ème qualité',       'unit' => 'kg', 'origin_type' => 'local', 'order' => 2],
                    ['name' => 'Arachide Coque',           'unit' => 'kg', 'origin_type' => 'local', 'order' => 3],
                    ['name' => 'Arachide Décortiquée',     'unit' => 'kg', 'origin_type' => 'local', 'order' => 4],
                ],
            ],
            [
                'name' => 'Légumes',
                'slug' => 'legumes',
                'display_order' => 3,
                'products' => [
                    ['name' => 'Oignon Local',          'unit' => 'kg', 'origin_type' => 'local',    'order' => 1],
                    ['name' => 'Oignon Importé',        'unit' => 'kg', 'origin_type' => 'imported', 'order' => 2],
                    ['name' => 'Pomme de Terre Locale', 'unit' => 'kg', 'origin_type' => 'local',    'order' => 3],
                    ['name' => 'Pomme de Terre Importée','unit' => 'kg', 'origin_type' => 'imported','order' => 4],
                    ['name' => 'Nahanoe',               'unit' => 'kg', 'origin_type' => 'local',    'order' => 5],
                    ['name' => 'Patate Douce',          'unit' => 'kg', 'origin_type' => 'local',    'order' => 6],
                    ['name' => 'Chou',                  'unit' => 'kg', 'origin_type' => 'both',     'order' => 7],
                    ['name' => 'Tomate',                'unit' => 'kg', 'origin_type' => 'local',    'order' => 8],
                    ['name' => 'Carotte',               'unit' => 'kg', 'origin_type' => 'both',     'order' => 9],
                ],
            ],
            [
                'name' => 'Fruits',
                'slug' => 'fruits',
                'display_order' => 4,
                'products' => [
                    ['name' => 'Banane',    'unit' => 'kg', 'origin_type' => 'both',  'order' => 1],
                    ['name' => 'Mangue',    'unit' => 'kg', 'origin_type' => 'local', 'order' => 2],
                    ['name' => 'Orange',    'unit' => 'kg', 'origin_type' => 'both',  'order' => 3],
                    ['name' => 'Pastèque',  'unit' => 'kg', 'origin_type' => 'local', 'order' => 4],
                ],
            ],
            [
                'name' => 'Détail et Volaille',
                'slug' => 'detail-volaille',
                'display_order' => 5,
                'products' => [
                    ['name' => 'Bovin (jeunes 1-3 ans)',  'unit' => 'tête', 'origin_type' => 'local', 'order' => 1],
                    ['name' => 'Ovin (jeunes 1-3 ans)',   'unit' => 'tête', 'origin_type' => 'local', 'order' => 2],
                    ['name' => 'Caprin (élevés à 1 an)',  'unit' => 'tête', 'origin_type' => 'local', 'order' => 3],
                    ['name' => 'Volaille',                'unit' => 'tête', 'origin_type' => 'local', 'order' => 4],
                ],
            ],
            [
                'name' => 'Autres',
                'slug' => 'autres',
                'display_order' => 6,
                'products' => [
                    ['name' => 'Sel', 'unit' => 'kg', 'origin_type' => 'both', 'order' => 1],
                ],
            ],
        ];

        $now = now();

        foreach ($catalogue as $cat) {
            $categoryId = DB::table('sim_product_categories')->insertGetId([
                'name'          => $cat['name'],
                'slug'          => $cat['slug'],
                'display_order' => $cat['display_order'],
                'is_active'     => true,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            foreach ($cat['products'] as $p) {
                DB::table('sim_products')->insertOrIgnore([
                    'sim_product_category_id' => $categoryId,
                    'name'          => $p['name'],
                    'slug'          => Str::slug($p['name']),
                    'unit'          => $p['unit'],
                    'origin_type'   => $p['origin_type'],
                    'display_order' => $p['order'],
                    'is_active'     => true,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ]);
            }
        }

        $total = DB::table('sim_products')->count();
        $this->command->info("✅ {$total} produits SIM insérés.");
    }
}

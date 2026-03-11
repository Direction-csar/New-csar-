<?php

namespace Database\Seeders;

use App\Models\SimDepartment;
use App\Models\SimRegion;
use Illuminate\Database\Seeder;

class SimGeographySeeder extends Seeder
{
    /**
     * Régions et départements du Sénégal (14 régions, 46 départements).
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Dakar', 'code' => 'DK', 'departments' => ['Dakar', 'Pikine', 'Guédiawaye', 'Rufisque']],
            ['name' => 'Ziguinchor', 'code' => 'ZG', 'departments' => ['Ziguinchor', 'Bignona', 'Oussouye']],
            ['name' => 'Diourbel', 'code' => 'DB', 'departments' => ['Diourbel', 'Bambey', 'Mbacké']],
            ['name' => 'Saint-Louis', 'code' => 'SL', 'departments' => ['Dagana', 'Podor', 'Saint-Louis']],
            ['name' => 'Tambacounda', 'code' => 'TC', 'departments' => ['Bakel', 'Goudiry', 'Koumpentoum', 'Tambacounda']],
            ['name' => 'Thiès', 'code' => 'TH', 'departments' => ['M\'bour', 'Tivaouane', 'Thiès']],
            ['name' => 'Louga', 'code' => 'LG', 'departments' => ['Kébémer', 'Linguère', 'Louga']],
            ['name' => 'Fatick', 'code' => 'FK', 'departments' => ['Foundiougne', 'Gossas', 'Fatick']],
            ['name' => 'Kaffrine', 'code' => 'KF', 'departments' => ['Birkilane', 'Kaffrine', 'Koungheul', 'Malem Hodar']],
            ['name' => 'Kolda', 'code' => 'KD', 'departments' => ['Kolda', 'Médina Yoro Foulah', 'Vélingara']],
            ['name' => 'Matam', 'code' => 'MT', 'departments' => ['Kanel', 'Matam', 'Ranérou']],
            ['name' => 'Kaolack', 'code' => 'KL', 'departments' => ['Guinguinéo', 'Kaolack', 'Nioro du Rip']],
            ['name' => 'Kédougou', 'code' => 'KG', 'departments' => ['Kédougou', 'Salémata', 'Saraya']],
            ['name' => 'Sédhiou', 'code' => 'SD', 'departments' => ['Bounkiling', 'Goudomp', 'Sédhiou']],
        ];

        foreach ($data as $order => $regionData) {
            $region = SimRegion::firstOrCreate(
                ['name' => $regionData['name']],
                [
                    'code' => $regionData['code'],
                    'display_order' => $order + 1,
                    'is_active' => true,
                ]
            );

            foreach ($regionData['departments'] as $deptName) {
                SimDepartment::firstOrCreate(
                    [
                        'sim_region_id' => $region->id,
                        'name' => $deptName,
                    ],
                    ['is_active' => true]
                );
            }
        }
    }
}

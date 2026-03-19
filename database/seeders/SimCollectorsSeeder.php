<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SimCollectorsSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des collecteurs SIM de test
        $collectors = [
            [
                'name' => 'Mamadou Diallo',
                'email' => 'mamadou.diallo@sim.sn',
                'phone' => '+221771234567',
                'password_hash' => Hash::make('password123'),
                'assigned_zones' => ['Dakar', 'Thiès'],
                'status' => 'active',
                'total_collections' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Awa Sène',
                'email' => 'awa.sene@sim.sn',
                'phone' => '+221762345678',
                'password_hash' => Hash::make('password123'),
                'assigned_zones' => ['Kaolack', 'Fatick'],
                'status' => 'active',
                'total_collections' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Ibrahim Ba',
                'email' => 'ibrahim.ba@sim.sn',
                'phone' => '+221783456789',
                'password_hash' => Hash::make('password123'),
                'assigned_zones' => ['Saint-Louis', 'Louga'],
                'status' => 'active',
                'total_collections' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('sim_collectors')->insert($collectors);

        $this->command->info('✅ 3 collecteurs SIM créés avec succès');
        $this->command->info('📱 Emails de test:');
        $this->command->info('   - mamadou.diallo@sim.sn (Zones: Dakar, Thiès)');
        $this->command->info('   - awa.sene@sim.sn (Zones: Kaolack, Fatick)');
        $this->command->info('   - ibrahim.ba@sim.sn (Zones: Saint-Louis, Louga)');
        $this->command->info('🔐 Mot de passe: password123');
    }
}

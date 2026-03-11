<?php

namespace Database\Seeders;

use App\Models\SimCollectorAssignment;
use App\Models\SimDepartment;
use App\Models\SimMarket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SimSampleDataSeeder extends Seeder
{
    /**
     * Données de démo : marchés, utilisateurs agent/responsable, assignations.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->command->warn('Aucun utilisateur admin trouvé. Créez-en un d’abord.');
            return;
        }

        // Départements existants (après SimGeographySeeder)
        $departments = SimDepartment::with('region')->where('is_active', true)->orderBy('name')->take(10)->get();
        if ($departments->isEmpty()) {
            $this->command->warn('Aucun département. Lancez d’abord : php artisan db:seed --class=SimGeographySeeder');
            return;
        }

        // Utilisateurs agent et responsable (créés si absents)
        $agent = User::firstOrCreate(
            ['email' => 'agent@csar.sn'],
            [
                'name' => 'Agent Collecteur SIM',
                'password' => Hash::make('password'),
                'role' => 'agent',
                'role_id' => 4,
                'is_active' => true,
            ]
        );
        if ($agent->role !== 'agent') {
            $agent->update(['role' => 'agent', 'role_id' => 4]);
        }

        $responsable = User::firstOrCreate(
            ['email' => 'responsable@csar.sn'],
            [
                'name' => 'Superviseur SIM',
                'password' => Hash::make('password'),
                'role' => 'responsable',
                'role_id' => 3,
                'is_active' => true,
            ]
        );
        if ($responsable->role !== 'responsable') {
            $responsable->update(['role' => 'responsable', 'role_id' => 3]);
        }

        // 3 marchés de démo dans des départements différents
        $marketsData = [
            ['department_index' => 0, 'name' => 'Marché Kermel', 'market_type' => 'urbain', 'is_permanent' => true],
            ['department_index' => 1, 'name' => 'Marché Tilène', 'market_type' => 'urbain', 'is_permanent' => true],
            ['department_index' => 2, 'name' => 'Marché Castors', 'market_type' => 'urbain', 'is_permanent' => true],
        ];

        foreach ($marketsData as $data) {
            $dept = $departments->get($data['department_index']) ?? $departments->first();
            $slug = Str::slug($data['name']);
            $exists = SimMarket::where('slug', $slug)->exists();
            if ($exists) {
                $slug = $slug . '-' . now()->format('Ymd');
            }
            $market = SimMarket::firstOrCreate(
                [
                    'sim_department_id' => $dept->id,
                    'name' => $data['name'],
                ],
                [
                    'created_by' => $admin->id,
                    'slug' => $slug,
                    'market_type' => $data['market_type'],
                    'is_permanent' => $data['is_permanent'] ?? false,
                    'is_active' => true,
                ]
            );

            // Assignation : agent + superviseur pour ce marché
            SimCollectorAssignment::firstOrCreate(
                [
                    'sim_market_id' => $market->id,
                    'collector_id' => $agent->id,
                ],
                [
                    'supervisor_id' => $responsable->id,
                    'assigned_by' => $admin->id,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Données SIM de démo créées.');
        $this->command->info('  - Agent : agent@csar.sn / password');
        $this->command->info('  - Superviseur : responsable@csar.sn / password');
    }
}

<?php

namespace Database\Seeders;

use App\Models\DistributionBeneficiary;
use App\Models\DistributionEvent;
use App\Models\DistributionPlanning;
use App\Models\DistributionTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DistributionDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Utilisateurs pour les APK
        $distributeur = User::firstOrCreate(
            ['email' => 'distributeur@csar.sn'],
            [
                'name' => 'Agent Distributeur Demo',
                'phone' => '770000001',
                'password' => Hash::make('Distrib2026!'),
                'is_active' => 1,
            ]
        );
        $distributeur->role = 'distributeur';
        $distributeur->role_id = 4;
        $distributeur->save();

        $scanner = User::firstOrCreate(
            ['email' => 'scanner@csar.sn'],
            [
                'name' => 'Agent Scanner Demo',
                'phone' => '770000002',
                'password' => Hash::make('Scanner2026!'),
                'is_active' => 1,
            ]
        );
        $scanner->role = 'scanner';
        $scanner->role_id = 4;
        $scanner->save();

        $admin = User::where('role', 'admin')->first() ?? $distributeur;

        // 2. Événement
        $event = DistributionEvent::firstOrCreate(
            ['slug' => 'tabaski-2026-demo'],
            [
                'name' => 'Distribution Tabaski 2026 (DEMO)',
                'description' => 'Données fictives pour tester les applications Distribution et Scanner.',
                'location' => 'Dakar',
                'initial_stock_kg' => 10000,
                'start_date' => now()->subDays(2),
                'end_date' => now()->addDays(10),
                'status' => 'active',
                'created_by' => $admin->id,
            ]
        );

        // 3. Plannings assignés au distributeur
        $planningsData = [
            ['name' => 'Pikine - Site A', 'location' => 'Pikine, Marché Syndicat', 'planned_quota_kg' => 3000, 'expected_beneficiaries' => 60],
            ['name' => 'Guédiawaye - Site B', 'location' => 'Guédiawaye, Mairie', 'planned_quota_kg' => 2500, 'expected_beneficiaries' => 50],
            ['name' => 'Rufisque - Site C', 'location' => 'Rufisque, Stade Ngalandou Diouf', 'planned_quota_kg' => 2000, 'expected_beneficiaries' => 40],
        ];

        $firstNames = ['Moussa', 'Fatou', 'Ibrahima', 'Aminata', 'Ousmane', 'Mariama', 'Cheikh', 'Awa', 'Modou', 'Khady', 'Abdoulaye', 'Ndèye', 'Mamadou', 'Rokhaya', 'Serigne'];
        $lastNames = ['Diop', 'Ndiaye', 'Fall', 'Sow', 'Ba', 'Gueye', 'Sarr', 'Faye', 'Mbaye', 'Diallo', 'Cissé', 'Thiam', 'Seck', 'Kane', 'Niang'];
        $categories = ['famille', 'veuve', 'personne_agee', 'handicape', 'femme_enceinte'];

        $seq = 1;

        foreach ($planningsData as $i => $pd) {
            $planning = DistributionPlanning::firstOrCreate(
                ['event_id' => $event->id, 'name' => $pd['name']],
                [
                    'description' => 'Planning de démonstration',
                    'location' => $pd['location'],
                    'planned_quota_kg' => $pd['planned_quota_kg'],
                    'executed_kg' => 0,
                    'expected_beneficiaries' => $pd['expected_beneficiaries'],
                    'status' => 'active',
                    'distribution_date' => now()->addDays($i),
                    'assigned_to' => $distributeur->id,
                ]
            );

            if ($planning->beneficiaries()->exists()) {
                continue;
            }

            // 4. Bénéficiaires : 4 pending, 3 validés, 5 avec ticket, 2 kit récupéré
            $statuses = array_merge(
                array_fill(0, 4, 'pending'),
                array_fill(0, 3, 'validated'),
                array_fill(0, 5, 'ticket_issued'),
                array_fill(0, 2, 'kit_collected'),
            );

            foreach ($statuses as $status) {
                $isElderly = rand(0, 4) === 0;
                $isPregnant = rand(0, 5) === 0;
                $isDisabled = rand(0, 6) === 0;

                $beneficiary = DistributionBeneficiary::create([
                    'planning_id' => $planning->id,
                    'full_name' => $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)],
                    'phone' => '77' . str_pad((string) (1000000 + $seq), 7, '0', STR_PAD_LEFT),
                    'cni' => '1' . str_pad((string) $seq, 12, '0', STR_PAD_LEFT),
                    'address' => $pd['location'],
                    'category' => $categories[array_rand($categories)],
                    'quantity_kg' => 50,
                    'is_vulnerable' => $isElderly || $isPregnant || $isDisabled,
                    'is_elderly' => $isElderly,
                    'is_pregnant' => $isPregnant,
                    'is_disabled' => $isDisabled,
                    'status' => $status,
                    'validated_at' => $status !== 'pending' ? now()->subHours(rand(1, 24)) : null,
                    'validated_by' => $status !== 'pending' ? $distributeur->id : null,
                ]);
                $seq++;

                if (in_array($status, ['ticket_issued', 'kit_collected'])) {
                    $ticketCode = 'CSAR-DEMO' . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
                    $collected = $status === 'kit_collected';

                    DistributionTicket::create([
                        'beneficiary_id' => $beneficiary->id,
                        'planning_id' => $planning->id,
                        'ticket_code' => $ticketCode,
                        'qr_token' => Str::uuid()->toString(),
                        'status' => $collected ? 'collected' : 'issued',
                        'issued_at' => now()->subHours(rand(2, 20)),
                        'scanned_at' => $collected ? now()->subHours(1) : null,
                        'collected_at' => $collected ? now()->subHours(1) : null,
                        'scanned_by' => $collected ? $scanner->id : null,
                        'scan_location' => $collected ? $pd['location'] : null,
                    ]);

                    if ($collected) {
                        $planning->increment('executed_kg', 50);
                    }
                }
            }
        }

        $this->command->info('');
        $this->command->info('=== Données de démo distribution créées ===');
        $this->command->info('APK Distribution : distributeur@csar.sn / Distrib2026!');
        $this->command->info('APK Scanner      : scanner@csar.sn / Scanner2026!');
        $this->command->info('Événement        : ' . $event->name);
        $this->command->info('Plannings        : ' . $event->plannings()->count());
        $this->command->info('Bénéficiaires    : ' . $event->total_beneficiaries);
        $this->command->info('Tickets émis     : ' . $event->total_tickets_issued);
        $this->command->info('');
        $this->command->info('Tickets à scanner (statut issued) :');
        DistributionTicket::where('ticket_code', 'like', 'CSAR-DEMO%')
            ->where('status', 'issued')
            ->with('beneficiary')
            ->get()
            ->each(fn ($t) => $this->command->line("  {$t->ticket_code}  ->  {$t->beneficiary->full_name}"));
    }
}

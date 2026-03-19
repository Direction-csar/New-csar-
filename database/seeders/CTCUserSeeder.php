<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CTCUserSeeder extends Seeder
{
    /**
     * Créer un utilisateur CTC (Conseil Technique de la Communication)
     */
    public function run(): void
    {
        $ctcRole = \App\Models\Role::where('name', 'ctc')->first();
        if (!$ctcRole) {
            $this->command->warn('Rôle CTC non trouvé. Exécutez d\'abord: php artisan db:seed --class=RoleSeeder');
            return;
        }

        $email = 'ctc@csar.sn';
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->update([
                'role' => 'ctc',
                'role_id' => $ctcRole->id,
                'is_active' => true,
                'password' => Hash::make('password'),
            ]);
            $this->command->info('Utilisateur CTC mis à jour: ' . $email . ' / password');
            return;
        }

        User::create([
            'name' => 'Conseil Technique Communication',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'ctc',
            'role_id' => $ctcRole->id,
            'is_active' => true,
        ]);

        $this->command->info('Utilisateur CTC créé: ' . $email . ' / password');
    }
}

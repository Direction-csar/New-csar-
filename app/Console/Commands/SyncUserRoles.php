<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SyncUserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchroniser les champs role et role_id de tous les utilisateurs';

    /**
     * Mapping role_id vers nom du rôle
     */
    private $roleMap = [
        1 => 'admin',
        2 => 'dg',
        3 => 'responsable',
        4 => 'agent',
        5 => 'drh'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Synchronisation des rôles des utilisateurs...');
        $this->newLine();

        $users = User::all();
        $updated = 0;
        $skipped = 0;

        foreach ($users as $user) {
            // Déterminer le rôle attendu basé sur role_id
            $expectedRole = $this->roleMap[$user->role_id] ?? 'agent';
            
            // Récupérer le rôle actuel de la base de données
            $currentRole = DB::table('users')->where('id', $user->id)->value('role');
            
            // Si le role actuel ne correspond pas au role_id
            if ($currentRole !== $expectedRole) {
                $oldRole = $currentRole ?? 'null';
                
                // Mettre à jour directement dans la base de données
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['role' => $expectedRole]);
                
                $this->line("✅ Utilisateur #{$user->id} ({$user->name}): '{$oldRole}' → '{$expectedRole}'");
                $updated++;
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("✅ Synchronisation terminée !");
        $this->info("   - {$updated} utilisateur(s) mis à jour");
        $this->info("   - {$skipped} utilisateur(s) déjà synchronisé(s)");
        $this->newLine();

        return Command::SUCCESS;
    }
}

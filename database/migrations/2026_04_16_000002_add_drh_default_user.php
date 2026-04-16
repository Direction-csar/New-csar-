<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le rôle drh s'il n'existe pas
        $roleExists = DB::table('roles')->where('name', 'drh')->exists();
        if (!$roleExists) {
            DB::table('roles')->insert([
                'name'         => 'drh',
                'display_name' => 'Direction des Ressources Humaines',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Créer l'utilisateur DRH par défaut s'il n'existe pas
        $userExists = DB::table('users')->where('email', 'drh@csar.sn')->exists();
        if (!$userExists) {
            $role = DB::table('roles')->where('name', 'drh')->first();
            DB::table('users')->insert([
                'name'       => 'Direction RH',
                'email'      => 'drh@csar.sn',
                'password'   => Hash::make('Drh@csar2026'),
                'role'       => 'drh',
                'role_id'    => $role ? $role->id : null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'drh@csar.sn')->delete();
        DB::table('roles')->where('name', 'drh')->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter le rôle superviseur s'il n'existe pas
        $exists = DB::table('roles')->where('name', 'superviseur')->exists();
        if (!$exists) {
            DB::table('roles')->insert([
                'name'         => 'superviseur',
                'display_name' => 'Superviseur SIM',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Créer un utilisateur superviseur par défaut s'il n'existe pas
        $userExists = DB::table('users')->where('email', 'superviseur@csar.sn')->exists();
        if (!$userExists) {
            $role = DB::table('roles')->where('name', 'superviseur')->first();
            DB::table('users')->insert([
                'name'       => 'Superviseur SIM',
                'email'      => 'superviseur@csar.sn',
                'password'   => Hash::make('Superviseur2026'),
                'role'       => 'superviseur',
                'role_id'    => $role ? $role->id : null,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')->where('email', 'superviseur@csar.sn')->delete();
        DB::table('roles')->where('name', 'superviseur')->delete();
    }
};

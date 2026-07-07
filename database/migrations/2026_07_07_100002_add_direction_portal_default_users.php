<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $directions = [
            'cpm' => [
                'display_name' => 'Cellule Passation des Marchés',
                'department' => 'CPM',
                'email' => 'cpm@csar.sn',
                'password' => 'Cpm@csar2026',
                'name' => 'Direction CPM',
            ],
            'dpse' => [
                'display_name' => 'Direction Planification & Suivi Évaluation',
                'department' => 'DPSE',
                'email' => 'dpse@csar.sn',
                'password' => 'Dpse@csar2026',
                'name' => 'Direction DPSE',
            ],
            'dtl' => [
                'display_name' => 'Direction Technique et Logistique',
                'department' => 'DTL',
                'email' => 'dtl@csar.sn',
                'password' => 'Dtl@csar2026',
                'name' => 'Direction DTL',
            ],
        ];

        foreach ($directions as $slug => $data) {
            $roleExists = DB::table('roles')->where('name', $slug)->exists();
            if (!$roleExists) {
                DB::table('roles')->insert([
                    'name' => $slug,
                    'display_name' => $data['display_name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $userExists = DB::table('users')->where('email', $data['email'])->exists();
            if (!$userExists) {
                $role = DB::table('roles')->where('name', $slug)->first();
                DB::table('users')->insert([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => $slug,
                    'role_id' => $role ? $role->id : null,
                    'is_active' => true,
                    'department' => $data['department'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $emails = ['cpm@csar.sn', 'dpse@csar.sn', 'dtl@csar.sn'];
        DB::table('users')->whereIn('email', $emails)->delete();
        DB::table('roles')->whereIn('name', ['cpm', 'dpse', 'dtl'])->delete();
    }
};

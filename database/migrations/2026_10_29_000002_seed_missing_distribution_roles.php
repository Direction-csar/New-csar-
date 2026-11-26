<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rattrapage : les rôles 'distributeur' et 'scanner' sont proposés dans
 * le formulaire admin et utilisés par l'API distribution, mais absents
 * de la table roles (RoleSeeder ne les créait pas).
 * Résultat : role_id NULL pour ces comptes.
 * Idempotent : insertOrIgnore + backfill uniquement des role_id NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        DB::table('roles')->insertOrIgnore([
            ['name' => 'distributeur', 'display_name' => 'Distributeur d\'aide',
             'description' => 'APK Distribution : validation des bénéficiaires et émission des tickets QR',
             'created_at' => $now, 'updated_at' => $now],
            ['name' => 'scanner', 'display_name' => 'Scanner de tickets',
             'description' => 'APK Scanner : scan et enregistrement de la remise des kits',
             'created_at' => $now, 'updated_at' => $now],
        ]);

        foreach (['distributeur', 'scanner'] as $roleName) {
            $roleId = DB::table('roles')->where('name', $roleName)->value('id');
            if ($roleId) {
                DB::table('users')
                    ->where('role', $roleName)
                    ->whereNull('role_id')
                    ->update(['role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        // Ne pas supprimer les rôles ni réécrire role_id : la correction
        // est une donnée de référence, non destructive.
    }
};

<?php
/**
 * Fix role_id mappings for DRH + Entrepôt and ensure roles table contains drh.
 *
 * Run: php fix_role_ids_now.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════════════\n";
echo "   FIX ROLE_ID / ROLES POUR DRH + ENTREPOT\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// Ensure DRH role exists with id=5 (matches middleware expectations)
$drhRole = DB::table('roles')->where('name', 'drh')->first();
if (!$drhRole) {
    DB::table('roles')->insert([
        'id' => 5,
        'name' => 'drh',
        'display_name' => 'DRH',
        'description' => 'Directeur RH',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "✅ Role 'drh' créé (id=5)\n";
} else {
    echo "✅ Role 'drh' existe déjà (id={$drhRole->id})\n";
}

// Canonical mappings used by middleware:
// admin=1, dg=2, responsable=3, agent=4, drh=5
$updates = [
    ['email' => 'drh@csar.sn', 'role' => 'drh', 'role_id' => 5],
    ['email' => 'entrepot@csar.sn', 'role' => 'responsable', 'role_id' => 3],
    ['email' => 'responsable@csar.sn', 'role' => 'responsable', 'role_id' => 3],
];

foreach ($updates as $u) {
    $row = DB::table('users')->where('email', $u['email'])->first();
    if (!$row) {
        echo "⚠️  User manquant: {$u['email']} (skip)\n";
        continue;
    }

    DB::table('users')->where('id', $row->id)->update([
        'role' => $u['role'],
        'role_id' => $u['role_id'],
        'is_active' => 1,
        'status' => 'active',
        'updated_at' => now(),
    ]);

    echo "✅ {$u['email']} => role={$u['role']} role_id={$u['role_id']}\n";
}

echo "\n--- Vérification ---\n";
$check = DB::table('users')
    ->whereIn('email', ['drh@csar.sn', 'entrepot@csar.sn', 'responsable@csar.sn'])
    ->get(['email', 'role', 'role_id']);
foreach ($check as $c) {
    echo "• {$c->email} : role={$c->role} role_id={$c->role_id}\n";
}

echo "\n✅ Terminé.\n";






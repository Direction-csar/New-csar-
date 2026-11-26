<?php
// Diagnostic lecture seule — module Distribution
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== ROLES ===\n";
foreach (DB::table('roles')->get() as $r) echo "  {$r->id}={$r->name}\n";

echo "=== USERS (roles mobiles) ===\n";
foreach (DB::table('users')->whereIn('role', ['distributeur','scanner','agent','magasinier'])
    ->get(['id','name','email','role','role_id','is_active']) as $u)
    echo "  {$u->id}|{$u->name}|{$u->email}|role={$u->role}|rid={$u->role_id}|actif={$u->is_active}\n";

echo "=== EVENTS ===\n";
foreach (DB::table('distribution_events')->get(['id','name','status']) as $e)
    echo "  {$e->id}|{$e->name}|{$e->status}\n";

echo "=== PLANNINGS ===\n";
foreach (DB::table('distribution_plannings')->get(['id','event_id','name','status','assigned_to']) as $p)
    echo "  {$p->id}|ev={$p->event_id}|{$p->name}|status={$p->status}|assigned=" . ($p->assigned_to ?? 'NULL') . "\n";

echo "=== BENEFICIAIRES ===\n";
echo "  total=" . DB::table('distribution_beneficiaries')->count() . "\n";
foreach (DB::table('distribution_beneficiaries')->select('status', DB::raw('count(*) c'))->groupBy('status')->get() as $s)
    echo "  {$s->status}={$s->c}\n";
echo "  validated_by NULL: " . DB::table('distribution_beneficiaries')->where('status','!=','pending')->whereNull('validated_by')->count() . "\n";

echo "=== TICKETS ===\n";
echo "  total=" . DB::table('distribution_tickets')->count() . "\n";
foreach (DB::table('distribution_tickets')->select('status', DB::raw('count(*) c'))->groupBy('status')->get() as $s)
    echo "  {$s->status}={$s->c}\n";

echo "=== COLONNES distribution_beneficiaries ===\n";
foreach (DB::getSchemaBuilder()->getColumnListing('distribution_beneficiaries') as $c) echo "  $c\n";

echo "=== TOUS USERS ===\n";
echo "  total=" . DB::table('users')->count() . "\n";
foreach (DB::table('users')->select('id','name','email','role','role_id','is_active','two_factor_enabled')->orderBy('id')->limit(40)->get() as $u)
    echo "  {$u->id}|{$u->name}|{$u->email}|role=" . ($u->role ?? 'NULL') . "|rid=" . ($u->role_id ?? 'NULL') . "|actif={$u->is_active}|2fa={$u->two_factor_enabled}\n";

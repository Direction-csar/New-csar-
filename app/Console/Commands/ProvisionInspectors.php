<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\InspectorWarehouseAssignment;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Magasin\InspectorCoverageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Création/affectation des inspecteurs à partir d'un fichier fourni par la direction
 * (données réelles uniquement). Simulation par défaut ; rien n'est supprimé.
 *
 * Format CSV (séparateur « ; », avec en-tête) : nom;email;telephone;regions
 * « regions » : liste séparée par « | » (ex. Dakar|Thiès). Tous les magasins actifs
 * de ces régions sont affectés à l'inspecteur.
 */
class ProvisionInspectors extends Command
{
    protected $signature = 'inspectors:provision {file : CSV nom;email;telephone;regions} {--apply : Enregistre réellement (sinon simulation)}';

    protected $description = 'Crée les comptes inspecteurs réels et les affecte aux magasins de leurs régions (simulation par défaut)';

    public function handle(): int
    {
        $path = $this->argument('file');
        if (!is_file($path)) {
            $this->error("Fichier introuvable : {$path}");

            return self::FAILURE;
        }
        $apply = (bool) $this->option('apply');

        $rows = array_map(fn ($l) => str_getcsv($l, ';'), array_filter(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
        array_shift($rows);

        $errors = [];
        $plan = [];
        foreach ($rows as $i => $r) {
            [$name, $email, $phone, $regionsRaw] = array_pad(array_map('trim', $r), 4, '');
            $line = $i + 2;
            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Ligne {$line} : nom ou email invalide.";
                continue;
            }
            $regions = [];
            foreach (array_filter(explode('|', $regionsRaw)) as $label) {
                $canon = InspectorCoverageService::canonicalRegion($label);
                $canon ? $regions[] = $canon : $errors[] = "Ligne {$line} : région inconnue « {$label} ».";
            }
            $plan[] = compact('name', 'email', 'phone', 'regions', 'line');
        }

        $warehouses = Warehouse::where('status', 'active')->where('is_active', true)->get();
        $created = [];

        foreach ($plan as $p) {
            $wids = $warehouses->filter(fn ($w) => in_array(
                InspectorCoverageService::canonicalRegion($w->region) ?? InspectorCoverageService::canonicalRegion($w->city),
                $p['regions'], true
            ))->pluck('id')->all();
            $existing = User::where('email', $p['email'])->first();

            if ($existing && $existing->role !== 'inspecteur') {
                $errors[] = "Ligne {$p['line']} : {$p['email']} existe avec le rôle « {$existing->role} » — ignoré.";
                continue;
            }
            $noWh = array_diff($p['regions'], $warehouses->map(fn ($w) => InspectorCoverageService::canonicalRegion($w->region) ?? InspectorCoverageService::canonicalRegion($w->city))->all());
            $this->line(sprintf('%s <%s> : %s | %d magasin(s) | %s%s', $p['name'], $p['email'],
                $existing ? 'existant' : 'à créer', count($wids), implode(', ', $p['regions']),
                $noWh ? ' | régions SANS magasin : ' . implode(', ', $noWh) : ''));

            if (!$apply) {
                continue;
            }

            DB::transaction(function () use ($p, $existing, $wids, &$created) {
                $user = $existing;
                if (!$user) {
                    $password = Str::password(14);
                    $user = new User(['name' => $p['name'], 'email' => $p['email'], 'phone' => $p['phone'] ?: null,
                        'password' => Hash::make($password), 'is_active' => true]);
                    $user->role = 'inspecteur';
                    $user->save();
                    $created[] = [$p['name'], $p['email'], $password];
                    AuditLog::create(['user_id' => null, 'action' => 'inspector.provision', 'level' => 'info', 'status' => 'success',
                        'model_type' => User::class, 'model_id' => $user->id,
                        'description' => "Compte inspecteur créé (import) : {$p['email']}"]);
                }
                foreach ($wids as $wid) {
                    $a = InspectorWarehouseAssignment::firstOrNew(['inspector_id' => $user->id, 'warehouse_id' => $wid]);
                    if ($a->exists && $a->is_active) {
                        continue;
                    }
                    $a->fill(['is_active' => true, 'revoked_at' => null, 'revoked_by' => null, 'note' => 'Import régions : ' . implode(', ', $p['regions'])])->save();
                    AuditLog::create(['user_id' => null, 'action' => 'inspector_assignment.assign', 'level' => 'info', 'status' => 'success',
                        'model_type' => InspectorWarehouseAssignment::class, 'model_id' => $a->id,
                        'description' => "Affectation import : {$user->email} ↔ magasin #{$wid}"]);
                }
            });
        }

        foreach ($errors as $e) {
            $this->warn($e);
        }

        if ($apply && $created) {
            $out = storage_path('app/private/inspecteurs-identifiants-' . date('Ymd-His') . '.csv');
            @mkdir(dirname($out), 0700, true);
            file_put_contents($out, "nom;email;mot_de_passe_initial\n" . implode("\n", array_map(fn ($c) => implode(';', $c), $created)) . "\n");
            @chmod($out, 0600);
            $this->info("Mots de passe initiaux écrits dans : {$out} (à transmettre puis supprimer ; à changer à la première connexion)");
        }

        $this->newLine();
        $this->comment($apply ? 'Appliqué.' : 'Simulation (--apply pour enregistrer). Rien n\'a été modifié.');

        return $errors && $apply ? self::FAILURE : self::SUCCESS;
    }
}

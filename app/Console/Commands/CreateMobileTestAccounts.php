<?php

namespace App\Console\Commands;

use App\Models\SimCollector;
use App\Models\SimMarket;
use App\Models\SimProduct;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Comptes de test des APK (Collecte SIM + Comptable des matières).
 * - Refusé en production.
 * - Mot de passe aléatoire fort, régénéré à chaque exécution.
 * - Identifiants écrits HORS du dépôt (profil utilisateur), jamais dans les logs.
 */
class CreateMobileTestAccounts extends Command
{
    protected $signature = 'mobile:test-accounts
        {--sim-email=collecteur.test@csar.local : E-mail du collecteur SIM de test}
        {--comptable-email=comptable.test@csar.local : E-mail du comptable de test}
        {--output= : Fichier de sortie des identifiants (défaut : ~/.csar/mobile-test-accounts.json)}';

    protected $description = 'Crée ou réinitialise les comptes de test locaux des APK SIM et Comptable (hors production)';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Commande interdite en production.');
            return self::FAILURE;
        }

        foreach (['sim_collectors', 'sim_markets', 'sim_products', 'users', 'personal_access_tokens'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->error("Table manquante : {$table}. Lancez d'abord « php artisan migrate ».");
                return self::FAILURE;
            }
        }

        [$assigned, $unassigned] = $this->ensureTestMarkets();
        $this->ensureTestProduct();

        $simPassword = Str::password(20);
        $collector = SimCollector::updateOrCreate(
            ['email' => $this->option('sim-email')],
            [
                'name' => 'Collecteur Test (local)',
                'phone' => '+221700000000',
                'password_hash' => Hash::make($simPassword),
                'status' => 'active',
                'assigned_zones' => [$assigned->id],
            ]
        );
        $collector->tokens()->delete();

        $comptablePassword = Str::password(20);
        $comptable = User::where('email', $this->option('comptable-email'))->first() ?? new User();
        $comptable->forceFill(array_filter([
            'name' => 'Comptable Test (local)',
            'email' => $this->option('comptable-email'),
            'password' => Hash::make($comptablePassword),
            'role' => 'comptable',
            'role_id' => Schema::hasTable('roles') ? DB::table('roles')->where('name', 'comptable')->value('id') : null,
            'is_active' => true,
        ], fn ($v) => $v !== null))->save();
        $comptable->tokens()->delete();

        $path = $this->option('output') ?: $this->defaultOutputPath();
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0700, true);
        }
        file_put_contents($path, json_encode([
            'generated_at' => now()->toIso8601String(),
            'environment' => app()->environment(),
            'warning' => 'Comptes de TEST locaux uniquement. Ne jamais utiliser en production ni intégrer dans un APK.',
            'sim' => [
                'login_url' => '/mobile/v1/login',
                'email' => $collector->email,
                'password' => $simPassword,
                'assigned_market' => ['id' => $assigned->id, 'name' => $assigned->name],
                'unassigned_market_for_negative_test' => ['id' => $unassigned->id, 'name' => $unassigned->name],
            ],
            'comptable' => [
                'login_url' => '/api/comptable/v1/login',
                'email' => $comptable->email,
                'password' => $comptablePassword,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        @chmod($path, 0600);

        $this->info('Comptes de test prêts (mots de passe régénérés, anciens jetons révoqués).');
        $this->table(['Application', 'E-mail', 'Détail'], [
            ['Collecte SIM', $collector->email, "Marché assigné : {$assigned->name} (#{$assigned->id})"],
            ['Comptable des matières', $comptable->email, 'Rôle comptable, compte actif'],
        ]);
        $this->line("Identifiants écrits hors du dépôt : {$path}");

        return self::SUCCESS;
    }

    /** Deux marchés actifs : un assigné, un non assigné (test de cloisonnement). */
    private function ensureTestMarkets(): array
    {
        $markets = SimMarket::where('is_active', true)->orderBy('id')->take(2)->get();

        while ($markets->count() < 2) {
            $n = $markets->count() + 1;
            $markets->push($this->createTestMarket("Marché Test Local {$n}"));
        }

        return [$markets[0], $markets[1]];
    }

    private function createTestMarket(string $name): SimMarket
    {
        $regionId = DB::table('sim_regions')->where('name', 'Région Test Local')->value('id')
            ?? DB::table('sim_regions')->insertGetId([
                'name' => 'Région Test Local', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        $departmentId = DB::table('sim_departments')->where('sim_region_id', $regionId)->value('id')
            ?? DB::table('sim_departments')->insertGetId([
                'sim_region_id' => $regionId, 'name' => 'Département Test Local', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

        return SimMarket::create([
            'sim_department_id' => $departmentId,
            'name' => $name,
            'slug' => Str::slug($name),
            'market_type' => 'urbain',
            'is_active' => true,
        ]);
    }

    private function ensureTestProduct(): void
    {
        if (SimProduct::where('is_active', true)->exists()) {
            return;
        }

        $categoryId = DB::table('sim_product_categories')->where('slug', 'test-local')->value('id')
            ?? DB::table('sim_product_categories')->insertGetId([
                'name' => 'Catégorie Test Local', 'slug' => 'test-local', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);

        SimProduct::create([
            'sim_product_category_id' => $categoryId,
            'name' => 'Produit Test Local',
            'slug' => 'produit-test-local',
            'unit' => 'kg',
            'is_active' => true,
        ]);
    }

    private function defaultOutputPath(): string
    {
        $home = getenv('USERPROFILE') ?: getenv('HOME') ?: sys_get_temp_dir();

        return $home . DIRECTORY_SEPARATOR . '.csar' . DIRECTORY_SEPARATOR . 'mobile-test-accounts.json';
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixLegacyMigrations extends Command
{
    protected $signature = 'archives:fix-migrations';

    protected $description = 'Marque comme executees les migrations warehouses/stock_movements si leurs tables existent deja (schema legacy desynchronise)';

    public function handle(): int
    {
        $pairs = [
            '2025_06_30_100000_create_warehouses_table' => 'warehouses',
            '2025_06_30_100001_create_stock_movements_table' => 'stock_movements',
        ];

        foreach ($pairs as $migration => $table) {
            $tableExists = Schema::hasTable($table);
            $alreadyRecorded = DB::table('migrations')->where('migration', $migration)->exists();

            if ($tableExists && !$alreadyRecorded) {
                $batch = (int) DB::table('migrations')->max('batch') + 1;
                DB::table('migrations')->insert([
                    'migration' => $migration,
                    'batch' => $batch,
                ]);
                $this->info("OK: {$migration} marquee comme executee (batch {$batch}).");
            } elseif ($alreadyRecorded) {
                $this->line("SKIP: {$migration} deja enregistree.");
            } else {
                $this->warn("SKIP: table '{$table}' n'existe pas, migration laissee en pending normal.");
            }
        }

        return self::SUCCESS;
    }
}

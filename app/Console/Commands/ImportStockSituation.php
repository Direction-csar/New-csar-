<?php

namespace App\Console\Commands;

use App\Services\Magasin\StockSituationImporter;
use Illuminate\Console\Command;

class ImportStockSituation extends Command
{
    protected $signature = 'stock:import-situation {--dry-run : Affiche les régularisations sans rien enregistrer} {--force : Recalcule même si la situation a déjà été importée}';

    protected $description = 'Aligne les stocks des magasins sur la situation réelle du 23/11/2026 (mouvements de régularisation, historique conservé)';

    public function handle(StockSituationImporter $importer): int
    {
        $apply = !$this->option('dry-run');
        $report = $importer->run($apply, (bool) $this->option('force'));

        foreach ($report as $r) {
            $this->line(sprintf('<info>%s</info> → %s : %s', $r['region'], $r['warehouse'] ?? '—', $r['status']));
            foreach ($r['lines'] as $line) {
                $this->line('    ' . $line);
            }
        }
        $this->newLine();
        $this->info($apply ? 'Situation appliquée.' : 'Simulation uniquement (--dry-run) : rien n\'a été enregistré.');

        return self::SUCCESS;
    }
}

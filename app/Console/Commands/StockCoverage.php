<?php

namespace App\Console\Commands;

use App\Services\Magasin\InspectorCoverageService;
use Illuminate\Console\Command;

class StockCoverage extends Command
{
    protected $signature = 'stock:coverage {--json : Sortie JSON}';

    protected $description = 'Couverture des 14 régions : magasins, magasiniers, inspecteurs (lecture seule)';

    public function handle(InspectorCoverageService $svc): int
    {
        $report = $svc->report();

        if ($this->option('json')) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::SUCCESS;
        }

        foreach ($report as $r) {
            $this->line("<info>{$r['region']}</info> — {$r['status']}");
            foreach ($r['warehouses'] as $w) {
                $m = implode(', ', array_column($w['magasiniers'], 'name')) ?: '—';
                $i = implode(', ', array_column($w['inspecteurs'], 'name')) ?: '—';
                $this->line("    {$w['name']} | magasiniers: {$m} | inspecteurs: {$i}");
            }
        }
        $this->newLine();
        $this->comment('Lecture seule : aucune donnée modifiée.');

        return self::SUCCESS;
    }
}

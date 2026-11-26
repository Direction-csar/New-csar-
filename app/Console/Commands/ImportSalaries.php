<?php

namespace App\Console\Commands;

use App\Services\Drh\SalaryImportService;
use Illuminate\Console\Command;

class ImportSalaries extends Command
{
    protected $signature = 'rh:salaires-import
        {fichier : Chemin du fichier TXT des rémunérations}
        {--apply : Enregistrer les modifications après la prévisualisation}
        {--force : Ne pas demander de confirmation (avec --apply)}
        {--user-id= : Utilisateur DRH auteur de l\'actualisation}';

    protected $description = 'Prévisualise (par défaut) ou applique l\'actualisation des rémunérations depuis le fichier TXT';

    public function handle(SalaryImportService $service): int
    {
        $path = $this->argument('fichier');
        if (!is_file($path) || !is_readable($path)) {
            $this->error("Fichier introuvable ou illisible : {$path}");

            return self::FAILURE;
        }

        $userId = $this->option('user-id') ? (int) $this->option('user-id') : null;
        try {
            $import = $service->preview(file_get_contents($path), basename($path), $userId);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $s = $import->summary;
        $money = fn ($v) => $v === null ? '—' : number_format((float) $v, 0, ',', ' ');
        $this->info("Prévisualisation #{$import->id} — feuille « {$s['sheet']} » — {$s['total']} agent(s)");
        $this->table(['Statut', 'Nombre'], collect($s['by_status'])->map(fn ($n, $k) => [$k, $n])->values());

        $rows = collect($import->rows);
        $this->table(
            ['Matricule', 'Agent', 'Statut', 'Brut actuel', 'Nouveau brut', 'Changements', 'Alertes'],
            $rows->filter(fn ($r) => $r['status'] !== 'inchange')->map(fn ($r) => [
                $r['matricule'] ?: '—',
                mb_strimwidth($r['nom_base'] ?? $r['nom_fichier'], 0, 30, '…'),
                $r['status'],
                $money($r['old']['salaire_brut'] ?? null),
                $money($r['new']['salaire_brut'] ?? null),
                implode(',', $r['changes']),
                implode(',', $r['warnings']),
            ])->values()
        );
        $this->line("Masse brute : {$money($s['masse_brute_avant'])} → {$money($s['masse_brute_apres'])} F");
        $this->line("À mettre à jour : {$s['applicable']} · bloqués : {$s['blocked']} · fiches absentes du fichier : {$s['absent_du_fichier']}");

        if (!$this->option('apply')) {
            $this->warn('Aucune modification enregistrée (prévisualisation). Relancez avec --apply pour enregistrer.');

            return self::SUCCESS;
        }

        if ($s['applicable'] === 0) {
            $this->info('Rien à enregistrer.');

            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm("Enregistrer les nouvelles rémunérations pour {$s['applicable']} agent(s) ?")) {
            $this->warn('Annulé : aucune fiche modifiée.');

            return self::SUCCESS;
        }

        $result = $service->apply($import, $userId);
        $this->info("{$result['updated']} fiche(s) mise(s) à jour (import #{$import->id}, anciennes valeurs conservées).");
        foreach ($result['skipped'] as $skip) {
            $this->warn("Ignoré : {$skip}");
        }

        return self::SUCCESS;
    }
}

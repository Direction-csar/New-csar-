<?php

namespace App\Console\Commands;

use App\Models\Personnel;
use App\Services\Drh\ContractService;
use Illuminate\Console\Command;

class GenerateVersionedContracts extends Command
{
    protected $signature = 'rh:contrats-generer
        {--mode=missing : missing (manquants), all (actualiser tous) ou selection}
        {--matricule=* : Matricule(s) à traiter avec --mode=selection}
        {--user-id= : Utilisateur DRH auteur}';

    protected $description = 'Génère les contrats CDI/CDD versionnés (les contrats signés ne sont jamais écrasés)';

    public function handle(ContractService $contracts): int
    {
        $mode = $this->option('mode');
        if (!in_array($mode, ['missing', 'all', 'selection'], true)) {
            $this->error('Mode invalide : missing, all ou selection.');

            return self::FAILURE;
        }

        $agents = Personnel::query()
            ->when($mode === 'selection', fn ($q) => $q->whereIn('matricule', $this->option('matricule')))
            ->orderBy('prenoms_nom')->get();

        if ($agents->isEmpty()) {
            $this->warn('Aucun agent à traiter.');

            return self::SUCCESS;
        }

        $s = $contracts->generateMany($agents, $mode, $this->option('user-id') ? (int) $this->option('user-id') : null);
        $this->table(['Générés', 'Avenants à valider', 'Inchangés', 'Déjà existants', 'Erreurs'],
            [[$s['created'], $s['avenants'], $s['unchanged'], $s['skipped'], count($s['errors'])]]);
        foreach ($s['errors'] as $error) {
            $this->error($error);
        }

        return $s['errors'] ? self::FAILURE : self::SUCCESS;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Personnel;
use Illuminate\Support\Facades\DB;

/**
 * Actualisation 2026 depuis le « Tableau comparatif des rémunérations » :
 * - agent existant (même matricule) : seuls poste, catégorie et rémunération sont mis à jour ;
 * - agent absent (les 3 recrues 202608, 202609, 202610) : fiche créée avec les données du tableau.
 * Les autres informations des fiches existantes ne sont jamais modifiées.
 */
class Remunerations2026Seeder extends AgentsImportSeeder
{
    public string $csvPath = '';

    private const UPDATED_FIELDS = [
        'poste_actuel', 'categorie', 'salaire_base', 'sursalaire',
        'indemnite_fonction', 'indemnite_transport', 'salaire_brut', 'net_a_payer',
    ];

    public function run(): void
    {
        $path = $this->csvPath ?: database_path('seeders/remunerations_2026.csv');
        $lines = array_slice(preg_split('/\r\n|\n/', trim(file_get_contents($path))), 1);

        $existing = Personnel::query()->get()->keyBy(fn ($p) => ltrim(trim((string) $p->matricule), '0'));
        $updated = $created = $unchanged = 0;

        DB::transaction(function () use ($lines, $existing, &$updated, &$created, &$unchanged) {
            foreach ($lines as $line) {
                $data = $this->mapRow(str_getcsv($line, ';'));
                if (!$data) {
                    continue;
                }
                $data['indemnite_fonction'] ??= 0;
                $data['indemnite_transport'] ??= 0;

                $agent = $existing->get(ltrim($data['matricule'], '0'));
                if (!$agent) {
                    Personnel::create($data);
                    $created++;
                    $this->command?->info("Créé : {$data['matricule']} {$data['prenoms_nom']}");
                    continue;
                }

                $agent->fill(array_intersect_key($data, array_flip(self::UPDATED_FIELDS)));
                if ($agent->isDirty()) {
                    $agent->save();
                    $updated++;
                } else {
                    $unchanged++;
                }
            }
        });

        $this->command?->info("Rémunérations 2026 : {$updated} mis à jour, {$created} créé(s), {$unchanged} inchangé(s).");
    }
}

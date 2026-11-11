<?php

namespace Database\Seeders;

use App\Models\Personnel;
use Illuminate\Support\Facades\DB;

/**
 * Synchronisation des fiches agents depuis le « Tableau comparatif des
 * rémunérations de l'année 2026 » (fiches_2026.csv, 100 agents).
 *
 * Met à jour les données d'état civil et de formation (diplômes, sexe,
 * filiation, CIN, naissance, situation familiale, adresse, dates) ainsi que
 * la rémunération et l'organigramme. Les champs non présents dans le tableau
 * (email, téléphones, photo, diplômes saisis manuellement hors import, etc.)
 * ne sont jamais modifiés. Un agent inconnu est créé.
 */
class FichesAgents2026Seeder extends AgentsImportSeeder
{
    public string $csvPath = '';

    private const UPDATED_FIELDS = [
        'prenoms_nom', 'sexe', 'date_naissance', 'lieu_naissance', 'tranche_age',
        'numero_cni', 'situation_matrimoniale', 'nombre_enfants', 'adresse_complete',
        'date_recrutement_csar', 'date_prise_service_csar', 'filiation',
        'formations_professionnelles', 'diplome_academique', 'autres_diplomes_certifications',
        'localisation_region', 'direction_service', 'poste_actuel', 'categorie',
        'salaire_base', 'sursalaire', 'indemnite_fonction', 'indemnite_transport',
        'salaire_brut', 'net_a_payer',
    ];

    public function run(): void
    {
        $path = $this->csvPath ?: database_path('seeders/fiches_2026.csv');
        $lines = array_slice(preg_split('/\r\n|\n/', trim(file_get_contents($path))), 1);

        $existing = Personnel::query()->get()->keyBy(fn ($p) => ltrim(trim((string) $p->matricule), '0'));
        $updated = $created = $unchanged = 0;

        DB::transaction(function () use ($lines, $existing, &$updated, &$created, &$unchanged) {
            foreach ($lines as $line) {
                $cols = str_getcsv($line, ';');
                $data = $this->mapRow($cols);
                if (!$data) {
                    continue;
                }
                $data['direction_service'] = config('organigramme')[mb_strtoupper(trim($cols[1] ?? ''))] ?? $data['direction_service'];
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

        $this->command?->info("Fiches 2026 : {$updated} mis à jour, {$created} créé(s), {$unchanged} inchangé(s).");
    }
}

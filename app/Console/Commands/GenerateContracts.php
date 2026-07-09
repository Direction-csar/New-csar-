<?php

namespace App\Console\Commands;

use App\Models\Personnel;
use App\Models\RhDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateContracts extends Command
{
    protected $signature = 'contracts:generate {--user-id=1 : ID de l\'utilisateur créateur}';
    protected $description = 'Génère les contrats CDI/CDD pour tous les agents et les sauvegarde en PDF';

    public function handle(): int
    {
        $userId = $this->option('user-id');
        $agents = Personnel::orderBy('prenoms_nom')->get();

        $generated = 0;
        $skipped = 0;

        foreach ($agents as $agent) {
            if ($agent->type_contrat === 'CDD') {
                $type = 'contrat-cdd';
                $view = 'admin.drh.documents.contrat_cdd';
                $label = 'Contrat CDD';
            } else {
                $type = 'contrat-cdi';
                $view = 'admin.drh.documents.contrat_cdi';
                $label = 'Contrat CDI';
            }

            $data = $this->contractData($agent);

            // Éviter les doublons : un seul contrat par agent et par type
            if (RhDocument::where('personnel_id', $agent->id)->where('type', $type)->exists()) {
                $this->warn("Contrat existant ignoré : {$agent->prenoms_nom} ({$type})");
                $skipped++;
                continue;
            }

            $pdf = Pdf::loadView($view, $data)->setPaper('a4', 'portrait');
            $filename = $this->sanitizeFilename($agent->prenoms_nom) . '-' . $type . '-' . Carbon::now()->format('YmdHis') . '.pdf';
            $path = 'contrats/' . $filename;
            Storage::disk('public')->put($path, $pdf->output());

            RhDocument::create([
                'personnel_id' => $agent->id,
                'type' => $type,
                'label' => $label,
                'data' => $data,
                'statut' => 'Généré',
                'created_by' => $userId,
            ]);

            $this->info("Contrat généré : {$agent->prenoms_nom} ({$type}) -> {$path}");
            $generated++;
        }

        $this->info("Génération terminée : {$generated} contrat(s) généré(s), {$skipped} ignoré(s).");
        $this->info("Fichiers disponibles dans : storage/app/public/contrats/");

        return self::SUCCESS;
    }

    private function contractData(Personnel $agent): array
    {
        return [
            'personnel' => $agent,
            'date_embauche' => $agent->date_recrutement_csar?->format('Y-m-d'),
            'date_debut' => $agent->date_recrutement_csar?->format('Y-m-d'),
            'date_fin' => $agent->type_contrat === 'CDD' ? '2026-12-31' : null,
            'salaire_base' => $agent->salaire_base,
            'sursalaire' => $agent->sursalaire,
            'indemnite_transport' => $agent->indemnite_transport,
            'indemnite_fonction' => $agent->indemnite_fonction,
            'salaire_brut' => $agent->salaire_brut,
            'poste' => $agent->poste_actuel,
            'direction' => $agent->direction_service,
            'categorie' => $agent->categorie,
            'diplome' => $agent->formations_professionnelles ?? $agent->diplome_academique,
            'filiation' => $agent->filiation,
            'numero_identification' => $agent->numero_cni,
            'date_delivrance_id' => $agent->date_delivrance_id?->format('Y-m-d'),
            'domicile_actuel' => $agent->adresse_complete,
            'situation_famille' => $agent->situation_matrimoniale,
            'nombre_epouses' => $agent->nombre_epouses,
        ];
    }

    private function sanitizeFilename(string $name): string
    {
        return preg_replace('/[^a-z0-9_-]+/', '-', mb_strtolower($name));
    }
}

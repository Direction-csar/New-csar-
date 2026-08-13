<?php

namespace App\Services;

use App\Models\Beneficiaire;
use App\Models\BonMatiere;
use App\Models\Planning;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class BeneficiaireDistributionService
{
    public static function create(array $data, int $userId): Beneficiaire
    {
        $data['created_by'] = $userId;
        $data['vulnerable'] = filter_var($data['vulnerable'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['religious'] = filter_var($data['religious'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['spontaneous'] = filter_var($data['spontaneous'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['status'] = $data['status'] ?? 'active';

        $beneficiaire = null;
        $planning = null;

        DB::transaction(function () use ($data, $userId, &$beneficiaire, &$planning) {
            $planning = Planning::findOrFail($data['planning_id']);

            $beneficiaire = Beneficiaire::create($data);

            do {
                $numeroBon = 'BM-' . $planning->id . '-' . strtoupper(bin2hex(random_bytes(4)));
            } while (BonMatiere::where('numero_bon', $numeroBon)->exists());

            $bon = BonMatiere::create([
                'planning_id' => $planning->id,
                'beneficiaire_id' => $beneficiaire->id,
                'numero_bon' => $numeroBon,
                'quantite_kg' => $data['quantite_kg'],
                'categorie' => $planning->category,
                'statut' => 'attribue',
                'attributed_at' => now(),
                'attributed_by' => $userId,
            ]);

            $planning->increment('executed_quota_kg', $data['quantite_kg']);
            $planning->campaign->increment('executed_stock_kg', $data['quantite_kg']);

            do {
                $code = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
            } while (Ticket::where('code', $code)->exists());

            Ticket::create([
                'bon_matiere_id' => $bon->id,
                'code' => $code,
                'qr_data' => json_encode([
                    'bon_id' => $bon->id,
                    'numero_bon' => $numeroBon,
                    'code' => $code,
                    'beneficiaire' => $beneficiaire->name,
                ]),
            ]);
        });

        assert($beneficiaire !== null && $planning !== null);

        DoublonService::detecter($beneficiaire);
        AlerteService::verifierPlanning($planning);
        AlerteService::verifierCampaign($planning->campaign);

        return $beneficiaire;
    }
}

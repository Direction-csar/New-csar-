<?php

namespace App\Services;

use App\Models\Beneficiaire;
use App\Models\DoublonDetecte;

class DoublonService
{
    public static function detecter(Beneficiaire $beneficiaire): void
    {
        if (!$beneficiaire->phone && !$beneficiaire->cni) {
            return;
        }

        $query = Beneficiaire::where('id', '!=', $beneficiaire->id)
            ->where(function ($q) use ($beneficiaire) {
                if ($beneficiaire->phone) {
                    $q->orWhere('phone', $beneficiaire->phone);
                }
                if ($beneficiaire->cni) {
                    $q->orWhere('cni', $beneficiaire->cni);
                }
            });

        foreach ($query->get() as $dup) {
            $types = [];
            if ($beneficiaire->phone && $dup->phone === $beneficiaire->phone) {
                $types[] = 'telephone';
            }
            if ($beneficiaire->cni && $dup->cni === $beneficiaire->cni) {
                $types[] = 'cni';
            }

            DoublonDetecte::firstOrCreate(
                [
                    'entity_1_id' => min($beneficiaire->id, $dup->id),
                    'entity_2_id' => max($beneficiaire->id, $dup->id),
                ],
                [
                    'type' => implode(',', $types) ?: 'telephone',
                    'planning_1_id' => $beneficiaire->planning_id,
                    'planning_2_id' => $dup->planning_id,
                    'status' => 'a_verifier',
                ]
            );
        }
    }
}

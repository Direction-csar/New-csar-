<?php

namespace App\Services;

use App\Models\Alerte;
use App\Models\Campaign;
use App\Models\Planning;

class AlerteService
{
    public static function verifierPlanning(Planning $planning): void
    {
        if ($planning->executed_quota_kg >= $planning->planned_quota_kg) {
            $alerte = Alerte::firstOrCreate(
                [
                    'type' => 'quota',
                    'planning_id' => $planning->id,
                    'controle' => 'quota_atteint',
                ],
                [
                    'valeur' => $planning->executed_quota_kg,
                    'seuil' => $planning->planned_quota_kg,
                    'status' => 'active',
                ]
            );

            if ($alerte->wasRecentlyCreated) {
                NotificationService::notifyAdminAndDG(
                    'Alerte quota atteint',
                    "Le planning {$planning->name} a atteint son quota de {$planning->planned_quota_kg} kg.",
                    'warning',
                    ['planning_id' => $planning->id, 'alerte_id' => $alerte->id],
                    route('admin.distribution.dashboard')
                );
            }
        }

        if ($planning->executed_quota_kg >= $planning->alert_threshold_kg && $planning->executed_quota_kg < $planning->planned_quota_kg) {
            $alerte = Alerte::firstOrCreate(
                [
                    'type' => 'quota',
                    'planning_id' => $planning->id,
                    'controle' => 'seuil_alerte',
                ],
                [
                    'valeur' => $planning->executed_quota_kg,
                    'seuil' => $planning->alert_threshold_kg,
                    'status' => 'active',
                ]
            );

            if ($alerte->wasRecentlyCreated) {
                NotificationService::notifyAdminAndDG(
                    'Alerte seuil de quota',
                    "Le planning {$planning->name} a atteint le seuil d'alerte de {$planning->alert_threshold_kg} kg.",
                    'warning',
                    ['planning_id' => $planning->id, 'alerte_id' => $alerte->id],
                    route('admin.distribution.dashboard')
                );
            }
        }
    }

    public static function verifierCampaign(Campaign $campaign): void
    {
        if ($campaign->executed_stock_kg >= $campaign->initial_stock_kg) {
            $alerte = Alerte::firstOrCreate(
                [
                    'type' => 'stock',
                    'campaign_id' => $campaign->id,
                    'controle' => 'stock_epuise',
                ],
                [
                    'valeur' => $campaign->executed_stock_kg,
                    'seuil' => $campaign->initial_stock_kg,
                    'status' => 'active',
                ]
            );

            if ($alerte->wasRecentlyCreated) {
                NotificationService::notifyAdminAndDG(
                    'Alerte stock épuisé',
                    "La campagne {$campaign->name} a épuisé son stock initial de {$campaign->initial_stock_kg} kg.",
                    'error',
                    ['campaign_id' => $campaign->id, 'alerte_id' => $alerte->id],
                    route('admin.distribution.dashboard')
                );
            }
        }
    }
}

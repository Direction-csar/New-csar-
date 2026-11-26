<?php

namespace App\Services\Distribution;

use App\Models\DistributionPlanning;
use App\Models\DistributionPlanningAgent;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Source unique des droits d'acces aux plannings de distribution.
 *
 * Deux affectations independantes (table distribution_planning_agents) :
 *  - distributor : consulte les beneficiaires, valide, emet les tickets QR ;
 *  - scanner     : verifie les tickets et confirme la remise du kit.
 * Un planning sans agent n'est accessible a personne, sauf a l'administrateur.
 * Un distributeur ne peut confirmer une remise que s'il est aussi affecte
 * comme scanner sur le meme planning.
 */
class PlanningAccess
{
    public static function isAdmin(?Authenticatable $user): bool
    {
        return $user instanceof User && in_array($user->role, ['admin', 'super_admin'], true);
    }

    public static function canDistribute(?Authenticatable $user, ?DistributionPlanning $planning): bool
    {
        return self::can($user, $planning, DistributionPlanningAgent::TYPE_DISTRIBUTOR);
    }

    public static function canScan(?Authenticatable $user, ?DistributionPlanning $planning): bool
    {
        return self::can($user, $planning, DistributionPlanningAgent::TYPE_SCANNER);
    }

    private static function can(?Authenticatable $user, ?DistributionPlanning $planning, string $type): bool
    {
        if (!$user instanceof User || !$user->is_active || !$planning) {
            return false;
        }

        return self::isAdmin($user) || $planning->hasAgent($user->id, $type);
    }
}

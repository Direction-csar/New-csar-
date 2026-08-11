<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

/**
 * Politique d'accès aux entrepôts.
 *
 * - Admin : tous les droits.
 * - Responsable : consultation et gestion des stocks.
 * - Magasinier : consultation de son entrepôt assigné uniquement.
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class WarehousePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable', 'magasinier'], true);
    }

    public function view(User $user, Warehouse $warehouse): bool
    {
        if (in_array($user->role, ['admin', 'responsable'], true)) {
            return true;
        }

        return $user->warehouse_id === $warehouse->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return in_array($user->role, ['admin', 'responsable'], true);
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->isAdmin();
    }

    public function manageStock(User $user, Warehouse $warehouse): bool
    {
        if (in_array($user->role, ['admin', 'responsable'], true)) {
            return true;
        }

        return $user->warehouse_id === $warehouse->id;
    }

    public function restore(User $user, Warehouse $warehouse): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Warehouse $warehouse): bool
    {
        return $user->isAdmin();
    }
}

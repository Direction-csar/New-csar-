<?php

namespace App\Policies;

use App\Models\Stock;
use App\Models\User;

/**
 * Politique d'accès aux stocks.
 *
 * - Admin : tout.
 * - DG : lecture seule.
 * - Responsable : lecture/écriture sur son propre entrepôt UNIQUEMENT.
 * - Agent : lecture seule sur son entrepôt.
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class StockPolicy
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
        return in_array($user->role, ['admin', 'dg', 'responsable', 'agent'], true);
    }

    public function view(User $user, Stock $stock): bool
    {
        if ($user->isDG()) {
            return true;
        }
        if (in_array($user->role, ['responsable', 'agent'], true)) {
            return isset($stock->warehouse_id) && $stock->warehouse_id === $user->warehouse_id;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->isResponsable() || $user->isAdmin();
    }

    public function update(User $user, Stock $stock): bool
    {
        if ($user->isDG() || $user->isAgent()) {
            return false;
        }
        if ($user->isResponsable()) {
            return isset($stock->warehouse_id) && $stock->warehouse_id === $user->warehouse_id;
        }
        return false;
    }

    public function delete(User $user, Stock $stock): bool
    {
        // Réservé admin (couvert par before()).
        return false;
    }

    public function transfer(User $user, Stock $stock): bool
    {
        return $this->update($user, $stock);
    }

    public function export(User $user): bool
    {
        return in_array($user->role, ['admin', 'dg', 'responsable'], true);
    }
}

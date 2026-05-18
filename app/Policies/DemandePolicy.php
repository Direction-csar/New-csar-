<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;

/**
 * Politique d'accès aux demandes (aide / dons).
 *
 * - Le demandeur (utilisateur public) ne passe PAS par les guards internes.
 * - Côté interne, lecture autorisée pour admin/dg/responsable.
 * - Mise à jour (changement de statut, affectation) : admin et DG.
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class DemandePolicy
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
        return in_array($user->role, ['admin', 'dg', 'responsable'], true);
    }

    public function view(User $user, Demande $demande): bool
    {
        // Responsable ne voit que les demandes de son entrepôt.
        if ($user->isResponsable()) {
            return isset($demande->warehouse_id) && $demande->warehouse_id === $user->warehouse_id;
        }
        return in_array($user->role, ['admin', 'dg'], true);
    }

    public function update(User $user, Demande $demande): bool
    {
        // DG : lecture seule (selon spécification CSAR).
        if ($user->isDG()) {
            return false;
        }
        // Responsable : peut traiter les demandes de son entrepôt.
        if ($user->isResponsable()) {
            return isset($demande->warehouse_id) && $demande->warehouse_id === $user->warehouse_id;
        }
        return $user->isAdmin();
    }

    public function delete(User $user, Demande $demande): bool
    {
        // Suppression réservée à l'admin (déjà couvert par before()).
        return false;
    }

    public function assign(User $user, Demande $demande): bool
    {
        return $user->isAdmin();
    }
}

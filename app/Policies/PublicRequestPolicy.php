<?php

namespace App\Policies;

use App\Models\PublicRequest;
use App\Models\User;

/**
 * Politique d'accès aux demandes publiques.
 *
 * - Admin : tous les droits.
 * - DG : approbation et consultation.
 * - Responsable : consultation et traitement des demandes assignées.
 * - Agent : consultation des demandes qui lui sont assignées.
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class PublicRequestPolicy
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

    public function view(User $user, PublicRequest $request): bool
    {
        if (in_array($user->role, ['admin', 'dg', 'responsable'], true)) {
            return true;
        }

        return $request->assigned_to === $user->id
            || $request->requester_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, PublicRequest $request): bool
    {
        return in_array($user->role, ['admin', 'dg', 'responsable'], true)
            || $request->assigned_to === $user->id;
    }

    public function delete(User $user, PublicRequest $request): bool
    {
        return $user->isAdmin();
    }

    public function assign(User $user, PublicRequest $request): bool
    {
        return in_array($user->role, ['admin', 'responsable'], true);
    }

    public function approve(User $user, PublicRequest $request): bool
    {
        return in_array($user->role, ['admin', 'dg'], true);
    }

    public function restore(User $user, PublicRequest $request): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, PublicRequest $request): bool
    {
        return $user->isAdmin();
    }
}

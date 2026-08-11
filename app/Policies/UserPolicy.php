<?php

namespace App\Policies;

use App\Models\User;

/**
 * Politique d'accès aux utilisateurs.
 *
 * - Seuls les admin peuvent gérer les utilisateurs.
 * - Un utilisateur peut consulter/modifier son propre profil.
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class UserPolicy
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

    public function view(User $user, User $target): bool
    {
        return $user->id === $target->id
            || in_array($user->role, ['admin', 'dg', 'responsable'], true);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->id === $target->id || $user->isAdmin();
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }
        return $user->isAdmin();
    }

    public function restore(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }
        return $user->isAdmin();
    }
}

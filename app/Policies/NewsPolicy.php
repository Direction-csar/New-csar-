<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

/**
 * Politique d'accès aux actualités (News).
 *
 * - Tout utilisateur authentifié peut consulter ses actualités.
 * - Seuls les rôles admin, ctc, dg peuvent créer.
 * - Modification et suppression : admin OU créateur original (CTC/DG/admin).
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class NewsPolicy
{
    /**
     * Rôles autorisés à gérer les actualités.
     */
    private const MANAGER_ROLES = ['admin', 'ctc', 'dg'];

    /**
     * Avant tout : un super-admin a tous les droits.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null; // Laisser les autres méthodes décider.
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->role, self::MANAGER_ROLES, true);
    }

    public function view(User $user, News $news): bool
    {
        return in_array($user->role, self::MANAGER_ROLES, true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'ctc'], true);
    }

    public function update(User $user, News $news): bool
    {
        // Le créateur original peut modifier sa propre actualité.
        if ($news->created_by === $user->id) {
            return true;
        }
        // CTC peut modifier les publications CTC.
        return $user->role === 'ctc';
    }

    public function delete(User $user, News $news): bool
    {
        // Seul le créateur ou un admin (via before()) peut supprimer.
        return $news->created_by === $user->id;
    }

    public function publish(User $user, News $news): bool
    {
        return in_array($user->role, ['admin', 'ctc'], true);
    }

    public function restore(User $user, News $news): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, News $news): bool
    {
        return $user->isAdmin();
    }
}

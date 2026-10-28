<?php

namespace App\Policies;

use App\Models\Archive;
use App\Models\User;

class ArchivePolicy
{
    /** Directions disposant d'un espace archives. */
    private const DIRECTIONS = ['CPM', 'DFC', 'DPSE', 'DRH', 'DTL', 'INSPECTEUR', 'COMPTABLE'];

    /**
     * Appartenance à une direction : champ department, ou à défaut le rôle
     * du compte portail (ex : rôle 'dtl' ⇔ direction 'DTL').
     */
    private function inDirection(User $user, string $direction): bool
    {
        return $user->department === $direction
            || strtoupper((string) $user->role) === $direction;
    }

    private function inAnyDirection(User $user): bool
    {
        return in_array($user->department, self::DIRECTIONS, true)
            || in_array(strtoupper((string) $user->role), self::DIRECTIONS, true);
    }

    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }
        return null;
    }

    public function view(User $user, Archive $archive): bool
    {
        return $this->inDirection($user, $archive->direction);
    }

    public function viewAny(User $user): bool
    {
        return $this->inAnyDirection($user) || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $this->inAnyDirection($user);
    }

    public function update(User $user, Archive $archive): bool
    {
        return false;
    }

    public function delete(User $user, Archive $archive): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Archive $archive): bool
    {
        return $user->isAdmin();
    }

    public function download(User $user, Archive $archive): bool
    {
        return $this->view($user, $archive);
    }

    public function print(User $user, Archive $archive): bool
    {
        return $this->view($user, $archive);
    }
}

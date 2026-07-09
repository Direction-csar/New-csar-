<?php

namespace App\Policies;

use App\Models\Archive;
use App\Models\User;

class ArchivePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if (in_array($user->role, ['admin', 'super_admin'], true)) {
            return true;
        }
        return null;
    }

    public function view(User $user, Archive $archive): bool
    {
        return $user->department === $archive->direction;
    }

    public function viewAny(User $user): bool
    {
        return in_array($user->department, ['CPM', 'DFC', 'DPSE', 'DRH', 'DTL'], true) || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return in_array($user->department, ['CPM', 'DFC', 'DPSE', 'DRH', 'DTL'], true);
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

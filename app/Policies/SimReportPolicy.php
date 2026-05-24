<?php

namespace App\Policies;

use App\Models\SimReport;
use App\Models\User;

/**
 * Politique d'accès aux rapports SIM.
 *
 * Audit OWASP A01 — Broken Access Control.
 */
class SimReportPolicy
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

    public function view(User $user, SimReport $report): bool
    {
        // Public si publié + flag is_public.
        if ($report->is_public && $report->status === 'completed') {
            return true;
        }
        return in_array($user->role, ['admin', 'dg', 'responsable'], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'responsable'], true);
    }

    public function update(User $user, SimReport $report): bool
    {
        if ($report->created_by === $user->id) {
            return true;
        }
        return $user->isAdmin();
    }

    public function delete(User $user, SimReport $report): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        if (in_array($user->role, ['ctc', 'dg', 'responsable'], true)) {
            return true;
        }
        return $report->created_by === $user->id;
    }

    public function download(User $user, SimReport $report): bool
    {
        return $this->view($user, $report);
    }
}

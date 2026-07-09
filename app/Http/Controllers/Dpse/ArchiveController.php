<?php

namespace App\Http\Controllers\Dpse;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'DPSE';
    }

    protected function getLayout(): string
    {
        $user = auth()->user();
        if ($user && in_array($user->role, ['admin', 'super_admin'], true)) {
            return 'layouts.admin';
        }
        return 'layouts.direction-portal';
    }
}

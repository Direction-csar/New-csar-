<?php

namespace App\Http\Controllers\Inspecteur;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'INSPECTEUR';
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

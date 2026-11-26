<?php

namespace App\Http\Controllers\Comptable;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'COMPTABLE';
    }
}

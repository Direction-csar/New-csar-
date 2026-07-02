<?php

namespace App\Http\Controllers\Dpse;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'DPSE';
    }
}

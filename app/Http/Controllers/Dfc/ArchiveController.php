<?php

namespace App\Http\Controllers\Dfc;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'DFC';
    }
}

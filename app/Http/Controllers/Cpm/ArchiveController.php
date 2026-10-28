<?php

namespace App\Http\Controllers\Cpm;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'CPM';
    }
}

<?php

namespace App\Http\Controllers\Inspecteur;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'INSPECTEUR';
    }
}

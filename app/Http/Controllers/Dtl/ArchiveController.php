<?php

namespace App\Http\Controllers\Dtl;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'DTL';
    }

    protected function getLayout(): string
    {
        return 'layouts.direction-portal';
    }
}

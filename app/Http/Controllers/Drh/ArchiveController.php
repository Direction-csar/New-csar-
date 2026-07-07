<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\DirectionArchiveController;

class ArchiveController extends DirectionArchiveController
{
    protected function getDirection(): string
    {
        return 'DRH';
    }

    protected function getLayout(): string
    {
        return 'layouts.drh-portal';
    }
}

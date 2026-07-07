<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\DirectionLoginController;
use App\Models\Archive;
use App\Models\ArchiveFolder;
use Illuminate\Support\Facades\Auth;

/**
 * Tableau de bord générique pour les portails dédiés par direction (CPM, DPSE, DTL, ...).
 */
class DirectionDashboardController extends Controller
{
    public function index(string $direction)
    {
        abort_unless(array_key_exists($direction, DirectionLoginController::DIRECTIONS), 404);

        $directionCode = strtoupper($direction);

        $stats = [
            'archives_count' => Archive::where('direction', $directionCode)->count(),
            'folders_count' => ArchiveFolder::where('direction', $directionCode)->count(),
            'archives_annee' => Archive::where('direction', $directionCode)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return view('admin.direction.dashboard', [
            'direction' => $direction,
            'directionLabel' => DirectionLoginController::DIRECTIONS[$direction],
            'stats' => $stats,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Distribution;

use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Beneficiaire;
use App\Models\BonMatiere;
use App\Models\Campaign;
use App\Models\Planning;
use App\Models\Ticket;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'campaigns' => Campaign::count(),
            'plannings' => Planning::count(),
            'beneficiaires' => Beneficiaire::count(),
            'bons' => BonMatiere::count(),
            'tickets_used' => Ticket::where('used', true)->count(),
            'tickets_pending' => Ticket::where('used', false)->count(),
            'kg_attributed' => BonMatiere::sum('quantite_kg'),
            'kg_delivered' => BonMatiere::where('statut', 'livre')->sum('quantite_kg'),
        ];

        $alertes = Alerte::with('campaign', 'planning')
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('admin.distribution.dashboard', compact('stats', 'alertes'));
    }
}

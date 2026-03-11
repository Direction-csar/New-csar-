<?php

namespace App\Http\Controllers\DG;

use App\Http\Controllers\Controller;
use App\Services\SimAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimController extends Controller
{
    public function __construct(
        protected SimAnalyticsService $simAnalytics
    ) {}

    /**
     * Vue SIM pour la Direction Générale : prix nationaux, évolution, alertes, comparaison régions.
     */
    public function index(Request $request): View
    {
        $year = (int) ($request->get('year') ?: now()->year);

        $overview = $this->simAnalytics->adminOverview();
        $prices = $this->simAnalytics->publicPrices($year);
        $alerts = $this->simAnalytics->getPriceAlerts(15);
        $regional = $this->simAnalytics->getRegionalComparison($year, 8);

        return view('dg.sim.index', [
            'overview' => $overview,
            'prices' => $prices,
            'alerts' => $alerts,
            'regional' => $regional,
            'year' => $year,
        ]);
    }
}

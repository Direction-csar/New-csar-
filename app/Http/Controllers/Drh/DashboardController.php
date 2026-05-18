<?php

namespace App\Http\Controllers\Drh;

use App\Http\Controllers\Controller;
use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $total      = Personnel::count();
        $hommes     = Personnel::where('sexe', 'Masculin')->count();
        $femmes     = Personnel::where('sexe', 'Féminin')->count();
        $demissions = Personnel::whereIn('statut', ['Demission', 'Démission', 'Démissionnaire'])->count();

        // Âge moyen
        $ageMoyen = (int) round(
            Personnel::whereNotNull('date_naissance')
                ->get()
                ->avg(fn($p) => Carbon::parse($p->date_naissance)->age) ?? 0
        );

        // Statistiques
        $stats = [
            'effectif'    => $total,
            'demissions'  => $demissions,
            'hommes'      => $hommes,
            'femmes'      => $femmes,
            'pct_hommes'  => $total > 0 ? round($hommes / $total * 100) : 0,
            'pct_femmes'  => $total > 0 ? round($femmes / $total * 100) : 0,
            'age_moyen'   => $ageMoyen,
        ];

        // Tranche d'âge
        $tranchesAge = Personnel::selectRaw('tranche_age, COUNT(*) as c')
            ->whereNotNull('tranche_age')
            ->groupBy('tranche_age')
            ->pluck('c', 'tranche_age')
            ->toArray();

        // Ancienneté (calcul par groupes)
        $anciennete = ['0-2 ans' => 0, '3-5 ans' => 0, '6-10 ans' => 0, '11-15 ans' => 0, '15+ ans' => 0];
        Personnel::whereNotNull('date_recrutement_csar')->chunk(200, function ($rows) use (&$anciennete) {
            foreach ($rows as $p) {
                $years = Carbon::parse($p->date_recrutement_csar)->diffInYears(now());
                if ($years <= 2) $anciennete['0-2 ans']++;
                elseif ($years <= 5) $anciennete['3-5 ans']++;
                elseif ($years <= 10) $anciennete['6-10 ans']++;
                elseif ($years <= 15) $anciennete['11-15 ans']++;
                else $anciennete['15+ ans']++;
            }
        });

        // Effectif par direction (top 10)
        $parDirection = Personnel::selectRaw('direction_service, COUNT(*) as c')
            ->whereNotNull('direction_service')
            ->groupBy('direction_service')
            ->orderByDesc('c')
            ->limit(10)
            ->pluck('c', 'direction_service')
            ->toArray();

        // Effectif par poste (top 10)
        $parPoste = Personnel::selectRaw('poste_actuel, COUNT(*) as c')
            ->whereNotNull('poste_actuel')
            ->groupBy('poste_actuel')
            ->orderByDesc('c')
            ->limit(10)
            ->pluck('c', 'poste_actuel')
            ->toArray();

        // Effectif par région
        $parRegion = Personnel::selectRaw('localisation_region, COUNT(*) as c')
            ->whereNotNull('localisation_region')
            ->groupBy('localisation_region')
            ->orderByDesc('c')
            ->pluck('c', 'localisation_region')
            ->toArray();

        // Effectif par statut
        $parStatut = Personnel::selectRaw('statut, COUNT(*) as c')
            ->whereNotNull('statut')
            ->groupBy('statut')
            ->pluck('c', 'statut')
            ->toArray();

        // Évolution recrutements (par année, 6 dernières années)
        $currentYear = (int) date('Y');
        $evolutionRecrutements = [];
        for ($y = $currentYear - 5; $y <= $currentYear; $y++) {
            $evolutionRecrutements[$y] = Personnel::whereYear('date_recrutement_csar', $y)->count();
        }

        // Liste agents les plus anciens (top 5)
        $plusAnciens = Personnel::whereNotNull('date_recrutement_csar')
            ->orderBy('date_recrutement_csar', 'asc')
            ->limit(5)
            ->get();

        // Liste agents les plus récents (top 5)
        $plusRecents = Personnel::whereNotNull('date_recrutement_csar')
            ->orderBy('date_recrutement_csar', 'desc')
            ->limit(5)
            ->get();

        return view('admin.drh.dashboard.index', compact(
            'stats', 'tranchesAge', 'anciennete', 'parDirection',
            'parPoste', 'parRegion', 'parStatut',
            'evolutionRecrutements', 'plusAnciens', 'plusRecents'
        ));
    }
}
